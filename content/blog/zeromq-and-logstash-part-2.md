+++
title = "Zeromq and Logstash Part 2"
date = "2012-02-08"
slug = "2012/02/08/zeromq-and-logstash-part-2"
Categories = []
+++

A few days ago I wrote up some notes on how we're making Logstash better by adding ZeroMQ as an option for inputs and outputs. That night we decided to take it a bit further and add support for ZeroMQ as a filter plugin as well.
<!-- more -->

I've had a lot of people ask me what's so hot about ZeroMQ. It's hard to explain but I really would suggest you read the excellent [zguide](http://zguide.zeromq.org). The best way I can describe it is that it's sockets on steroids. Sockets that behave the way you would expect sockets to behave as opposed to the way they do now. [Just open a socket!](http://www.quora.com/What-is-the-background-of-the-just-open-a-socket-meme).

# Inputs and Outputs
I'm only going to touch briefly on inputs and outputs. They were discussed briefly previously and I have a full fledged post in the wings about it.

They essentially work like the other implementations (AMQP and Redis) with the exception that you don't have a broker in the middle. Let me show you:

	[Collector 1] ------ load balanced events ----> [Indexer 1, Indexer 2, Indexer 3, Indexer 4]
	[Collector 2] ------ load balanced events ----> [Indexer 1, Indexer 2, Indexer 3, Indexer 4]
	[Collector 3] ------ load balanced events ----> [Indexer 1, Indexer 2, Indexer 3, Indexer 4]
	[Collector 4] ------ load balanced events ----> [Indexer 1, Indexer 2, Indexer 3, Indexer 4]

As you can see we're doing a pattern very similar to before. We want to send events of our nodes over to a cluster of indexers that do filtering. The difference here is that we don't have a broker. Not big deal, right? One less thing to worry about! You don't have to learn some new tool just to get some simple load balancing of workers. This works great.....until you need to scale workers.

Even using awesome configuration management, you've now got to cycle all your collectors to add the new endpoints. This means lost events. This makes me unhappy. It makes you unhappy. The world is sad. Why are you doing this to us?

Luckily I've been authorized by the Franklin Mint to release the source code to an enterprise class ZeroMQ broker that you can use. Not only is it enterprise class but it has built-in clustering. You can [grab the code here from github](https://github.com/lusis/enterprise-zeromq-broker).

Here are the configs for the logstash agents (output.conf is collector config, input.conf is indexer config):

output.conf:

```
input { stdin { type => "stdin" } }
output {
  zeromq {
    topology => "pushpull"
    address => ["tcp://localhost:5555", "tcp://localhost:5557"]
  }
}
```

input.conf:

```
input { 
  zeromq {
    type => "pull-input"
    topology => "pushpull"
    address => ["tcp://localhost:5556", "tcp://localhost:5558"]
    mode => "client"
  }
}
output { stdout { debug => true }}
```

## Action shot
Here's a shot of our fancy clustered broker in action (click to zoom):

[![zeromq-broker-ss.png](/images/posts/zeromq-part2/zeromq-broker-ss.png)](/images/posts/zeromq-part2/zeromq-broker-ss.png)

As you can see the two events we sent were automatically load balanced across our _"brokers"_ which then load balanced across our indexers.

## What have we bought ourselves?
Obviously this is all something of a joke. All we have done is point our collectors at other nodes instead of directly at our indexers. But realize that you can create 2 fixed points on your network with 8 lines of core code and use those as the static information in your indexers and collectors. You can then scale either side without ever having to update a configuration file.

I dare say you can even run those on t1.micro instances on Amazon.

Oh and if you don't like Ruby, write it in something else. That's the beauty of ZeroMQ.

# Filters
The thing that has me most excited is the addition of ZeroMQ as a filter to logstash. As you've already seen, ZeroMQ makes it REALLY easy to wire network topologies up with complex patterns. In the inputs and outputs we've exposed a few topologies that make sense. However there's another topology that we had not yet exposed because it didn't make sense - `reqrep`.

## REQ/REP
`reqrep` is short for request and reply. The reason we didn't expose it previously is that it didn't really make sense with the nature of inputs and outputs. However after talking with Jordan, we decided it actually DID make sense to use it for filters. After all, filters get a request -> do something -> return a response.

If it's not immediately clear yet how this makes sense, I've got another example for you. Let's take the case of needing to look something up externally to mutate a field. You COULD write a Logstash filter to do this ONE thing for you. Maybe you can make it generic enough to even submit a pull request.

Or you could use a ZeroMQ filter:

```
input { stdin { type => "stdin-type" } }
filter { zeromq { } }
output { stdout { debug => true } }
```

Here's the code for the filter:

```ruby
require 'rubygems'
require 'ffi-rzmq'
require "json"

context = ZMQ::Context.new
socket = context.socket(ZMQ::REP)
socket.bind("tcp://*:2121")
msg = ''
puts "starting up"
while true do
  socket.recv_string(msg)
  modified_message = JSON.parse(msg)
  puts "Message received: #{msg}"
  # Simulate using an external data source to 
  # to something that you need
  case modified_message["@source"]
  when "stdin://jvstratusmbp.lusis.org/"
    puts "Doing db lookup"
    sleep 10
    modified_message["@source"] = "john's laptop"
  end
  puts "Message responded: #{modified_message.to_json}"
  socket.send_string(modified_message.to_json)
end
```

By default, the filter will send the entire event over a ZeroMQ `REQ` socket to `tcp://localhost:2121`. It will then take the reply and send it up the chain to the Logstash output with the following results:

[![zeromq-filter-event.png](/images/posts/zeromq-part2/zeromq-filter-event.png)](/images/posts/zeromq-part2/zeromq-filter-event.png)

Alternately, you can send a single field to the filter and have it to work with:

```
input { stdin { type => "stdin-test" } }
filter { zeromq { field => "@message" } }
output { stdout { debug => true }}
```

and the code:

```ruby
require 'rubygems'
require 'ffi-rzmq'
require "json"

context = ZMQ::Context.new
socket = context.socket(ZMQ::REP)
socket.bind("tcp://*:2121")
msg = ''
puts "starting up"
while true do
  socket.recv_string(msg)
  puts "Recieved message: #{msg}"
  modified_message = "this field was changed externally"
  puts "Modified message: #{modified_message}"
  socket.send_string(modified_message)
end
```
and the result:

[![zeromq-filter-field.png](/images/posts/zeromq-part2/zeromq-filter-field.png)](/images/posts/zeromq-part2/zeromq-filter-field.png)

Many people have been asking for an `exec` filter for some time now. Dealing with that overhead is insane when coming from the JVM. By doing this type of work over ZeroMQ, there's much less overhead AND a reliable conduit for making it happen.

Here's just a few of the use cases I could think of:

* Artifically throttling your flow. Just use a sleep and return the original event.
* Doing external lookups for replacing parts of the event
* Adding arbitrary tags to a message using external criteria based on the event.
* Moving underperforming filters out of logstash and into an external process that is more performant
* Reducing the need to modify configs in logstash for greater uptime.

# Wrap up
All the ZeroMQ support is currently tagged experimental (hence the warnings you saw in my screenshots). It also exists in the form described only in master. If this interests you at all, please build from master and run some tests of your own. We would love the feedback and any bugs or tips you can provide are always valuable.
