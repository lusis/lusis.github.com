---
layout: post
title: "Fun With Celluloid"
date: 2011-08-13 00:30
comments: true
categories: [ruby, actors, celluloid, noah]
---

_warning! This is a really long post_

In the course of rewriting the [Noah](https://github.com/lusis/Noah) callback daemon, I started to get really frustrated with EventMachine. This is nothing against EventMachine by any stretch of the imagination. I really like it.

What I was having issues with is making the plugin framework as dirt simple as possible. By using EM, I had no choice but to require folks to understand how EM works. This primarily meant not blocking the reactor. Additionally, through no fault of EM, I was starting to get mired in callback spaghetti.

# Actors
I've mentioned several times before that I love the actor model. It makes sense to me. The idea of mailboxes and message passing is really simple to understand. For a while, there was project that implemented actors on top of EM called Revactor but it stalled. I started following the author (Tony Arcieri) on GitHub to see if he would ever update it. He did not but I caught wind of his new project and it was pretty much exactly what I was looking for.

Actors have a proven track record in Erlang and the Akka framework for Scala and Java uses them as well.

# Celluloid
<!--more-->
[Celluloid](https://github.com/tarcieri/celluloid) is an implementation of Actors on Ruby. At this point, it lacks some of the more advanced features of the Akka and Erlang implementations. However Tony is very bullish about Celluloid and is pretty awesome in general.

I'm not going to go over Celluloid basics in too much detail. Tony does an awesome job in the [README](http://celluloid.github.com/) for the project. What I want to talk more about is how I want to use it for Noah and what capabilities it has/is missing for that use case.

# Noah callbacks
I won't bore you with a rehash of Noah. I've written a ton of blog posts (and plan to write more). However for this discussion, it's important to understand what Noah callbacks need to do.

## Quick recap
Any object in Noah can be "watched". This is directly inspired by ZooKeeper. Because Noah is stateless, however, watches need to work a little differently. The primary difference is that Noah's watches are asynch. As a side-effect of that, we get some really cool additional functionality. So what does a Noah watch consist of?

- An absolute or partial path to and endpoint in the system
- A URI-style location for notification of changes to that path

Let's say you had a small sinatra application running on all your servers. Its only job was to be a listener for messages from Noah. This daemon will be responsible for rewriting your `hosts` file with any hosts that are created, modified or deleted on your network.

In this case, you might register your watch with a path of `/hosts/` and an endpoint of `http://machinename:port/update_hosts`. Any time a host object is created, updated or deleted Noah will send the JSON representation of that object state along with the operation performed to that endpoint. Let's say you also want to know about some configuration setting that has changed which lives at `/configurations/my_config_file.ini`. Let's put a kink in that. You want that watch to drop its message onto a RabbitMQ exchange.

So now we have the following information that we need to act on:

- `{:endpoint => 'http://machine:port/update_hosts', :pattern => '//noah/hosts'}`
- `{:endpoint => 'amqp://host:port/exchange?durable=true', :pattern => '//noah/configurations/my_config_file.ini'}`

Not so hard right? But we also have some additional moving parts. Something needs to monitor Redis for these various CRUD messages. We need to compare them against a list of endpoints that want notification about those messages. We also need to intercept any messages from Redis that are new endpoints being registered. Oh and we also need to know about failed endpoints so we can track and eventually evict them. Obviously we don't want to stop http messages from going out because AMQP is slow. Imagine if we implemented FTP endpoint support! Essentially we need high concurrency not only on each 'class' of endpoint (http, amqp, ftp whatever) but also within each class of endpoint. If any individual endpoint attempt crashes for any reason, we need to take some action (eviction for instance) and not impact anyone else.

# Doing it with Celluloid
So thinking about how we would do this with actors, I came up with the following basic actors:

- RedisActor _watches the Redis pubsub backend_
- HTTPActor _handles HTTP endpoints - a 'worker'_
- AMQPActor _handles AMQP endpoints - a 'worker'_
- BrokerActor _responsible for intercepting endpoint CRUD operations and also determining which actors to send messages to for processing_

As I said previously, we also need to ensure that if any worker crashes, that it gets replaced. Otherwise we would eventually lose all of our workers.

With this information, we can start to build a tree that looks something like this:

	- Master process
		|_Redis
		|_Broker
		|_HTTPPool
		|    |_Worker
		|    |_Worker
		|_AMQPPool
		    |_Worker
		    |_Worker

The master process is responsible for handling the Redis, Broker and Pool actors. Each pool actor is responsible for its workers. Not really visible in the ASCII above is how messages flow:

- Master process spawns Redis, Broker, HTTPPool and AMQPPool as supervised processes.
- Each pool type spins up a set of supervised workers.
- Master process makes an HTTP request to the Noah server for all existing watches (synchronous)
- It sends a message with those watches to the Broker so it can build its initial list.(synchronous)
- Redis actor watches pubsub.
- Watch messages are sent to a mailbox on the Broker. (synchronous)
- The rest to a different mailbox on the broker.
- The broker performs some filtering to determine if any registered watches care about the message. If so, those are sent to the appropriate pool. (async)
- Each Pool selects a worker and tells him the endpoint and the message
- The worker delivers the message

Where this became a slight problem with Celluloid is that it lacks two bits of functionality currently:

- Supervision trees
- Pool primitives

Right now in Celluloid, there is no way to build "pools" of supervised processes. The supervised part is important. If a process is supervised, crashes will be trapped and the process will be restarted.

So how did we "fake" this with the existing functionality?

The generic tree was fairly easy. The main Ruby process creates supervised processes for each actor:

``` ruby
	class RedisActor
	  include Celluloid::Actor
	  def initialize(name)
	    @name = name
	    log.info "starting redis actor"
	  end

	  def start
	   # start watching redis
	  end
	end
	class BrokerActor
	  include Celluloid::Actor
	  # constructor
	  def process_watch(msg)
	    #...
	  end
	  def do_work(msg)
	    #...
	  end
	end

	class HTTPPool
	  # you get the idea
	end

	@http_pool = HTTPPool.supervise_as :http_pool, "http_pool"
	@broker_actor = BrokerActor.supervise_as :broker_actor, "broker"
	@redis_actor = RedisActor.supervise_as :redis_actor, "redis"
```

The workers were a bit more complicated. What I ended up doing was something like this:


``` ruby
	class HTTPWorker
	  include Celluloid::Actor

	  attr_reader :name

	  def initialize(name)
	    @name = name
	  end
	  def do_work(ep, msg)
	    # Work to send the message
	  end
	end

	class HTTPPool
	  include Celluloid::Actor
	  WORKERS = 10

	  attr_reader :workers

	  def initialize(name)
	    @name = name
	    @workers = []
	    WORKERS.times do |id|
	      @workers[id] = HTTPWorker.supervise_as "http_worker_#{id}".to_sym, "http_worker_#{id}"
	    end
	  end
	  def do_work
	    @workers.sample.actor.do_work "msg"
	  end
	end
```

The problem as it stands is that we can't really have "anonymous" supervised processes. Each actor that's created goes into Celluloid's registry. We need a programatic way to look those up so we use `supervise\_as` to give them a name.

This gives us our worker pool. Now Redis can shovel messages to the broker who filters them. He sends a unit of work to a Pool which then selects a random worker to do the REAL work. Should any actor crash, he will be restarted. Because each actor is isolated, A crash in talking to redis, doesn't cause our existing workers to stop sending messages.

Obviously this a fairly naive implementation. We're missing some really important functionality here.

- detecting busy workers
- detecting dead workers (yes we still need to do this)
- alternate worker selection mechanisms (cyclical for instance)
- crash handling
- backlog handling

You might wonder why we care if a worker is dead or not? Currently Celluloid buffers messages in each actor until the can be dealt with. In the case of our Pool, it will select a worker and buffer any messages if the worker is blocked. If our worker crashes on its current unit of work, it returns control to the pool. The pool then attempts to send the worker the next message but the worker is dead and hasn't respawned yet.

# Some code to play with
Yes, we've finally made it to the end.

I've created a fun little sinatra application to make it easier for me to test my pools. It consists of a generic Pool class that can be subclassed and take a the name of a worker class as an argument. When a worker gets a message of "die", it will raise an exception thus simulating a crash. Additionally, the "message processing" logic includes sleep to simulate long running work. 

The reason Sinatra is in the mix is to provide an easy way for me to fire off simulated requests to the pool so I can experiment with different approaches. Eventually, Celluloid will have a proper Pool construct. I plan on using this as the basis for a pull request. You can see it here. Please feel free to fork and experiment with me. It's really fun.

{% gist 1143369 %}

