---
layout: post
title: On Noah - Part 3
date: 2011-05-18 22:14:00
comments: true
categories: [noah, blogger posts]
---

*This is the third part in a series on Noah. [Part 1](http://goo.gl/l3Mgt) and [Part 2](http://goo.gl/Nj2TN) are available as well*

In Part 1 and 2 of this series I covered background on Zookeeper and
discussed the similarities and differences between it and Noah. This
post is discussing the technology stack under Noah and the reasoning for
it.

# A little back story

I've told a few people this but my original intention was to use Noah as
a way to learn Erlang. However this did not work out. I needed to get a
proof of concept out much quicker than the ramp up time it would take to
[learn me some Erlang](http://learnyousomeerlang.com/). I had this
grandiose idea to slap mnesia, riak\_core and webmachine into a tasty
ball of Zookeeper clonage.
<!--more-->
I am not a developer by trade. I don't have any formal education in
computer science (or anything for that matter). The reason I mention
this is to say that programming is hard work for me. This has two side
effects:

-   It takes me considerably longer than a working developer to code
    what's in my head
-   I can only really learn a new language when I have an itch to
    scratch. A real world problem to model.

So in the interest of time, I fell back to a language I'm most
comfortable with right now, Ruby.

# Sinatra and Ruby

Noah isn't so much a web application as it is this 'api thing'. There's
no proper front end and honestly, you guys don't want to see what my
design deficient mind would create. I like to joke that in the world of
MVC, I stick to the M and C. Sure, APIs have views but not in the "click
the pretty button sense".

I had been doing quite a bit of glue code at the office using
[Sinatra](http://www.sinatrarb.com) (and EventMachine) so I went with
that. Sinatra is, if you use sheer number of clones in other languages
as an example, a success for writing API-only applications. I also
figured that if I wanted to slap something proper on the front, I could
easily integrate it with [Padrino](http://www.padrinorb.com).

But now I had to address the data storage issue.

# Redis

Previously, as a way to learn Python at another company, I wrote an
application called [Vogeler](https://github.com/lusis/vogeler). That
application had a lot of moving parts - CouchDB for storage and RabbitMQ
for messaging.

I knew from dealing with CouchDB on CentOS5 that I wasn't going to use
THAT again. Much of it would have been overkill for Noah anyway. I
realized I really needed nothing more than a key/value store. That
really left me with either Riak or Redis. I love Riak but it wasn't the
right fit in this case. I needed something with a smaller dependency
footprint. Mind you Riak is VERY easy to install but managing Erlang
applications is still a bit edgy for some folks. I needed something
simpler.

I also realized early on that I needed some sort of basic queuing
functionality. That really sealed Redis for me. Not only did it have
zero external dependencies, but it also met the needs for queuing. I
could use `lists` as dedicated direct queues and I could use the
built-in `pubsub` as a broadcast mechanism. Redis also has a fast atomic
counter that could be used to approximate the ZK sequence primitive
should I want to do that.

Additionally, Redis has master/slave (not my first choice) support for
limited scaling as well as redundancy. One of my original design goals
was that Noah behave like a traditional web application. This is a model
ops folks understand very well at this point.

# EventMachine

When you think asynchronous in the Ruby world, there's really only one
tool that comes to mind, EventMachine. Noah is designed for asynchronous
networks and is itself asynchronous in its design. The callback agent
itself uses EventMachine to process watches. As I said previously, this
is simply using an EM friendly Redis driver that can do `PSUBSCRIBE`
(using em-hiredis) and send watch messages (using em-http-request since
we only support HTTP by default).

# Ohm

Finally I slapped [Ohm](http://ohm.keyvalue.org) on top as the
abstraction layer for Redis access. Ohm, if you haven't used it, is
simply one of if not the best Ruby library for working with Redis. It's
easily extensible, very transparent and frankly, it just gets the hell
out of your way. A good example of this is converting some result to a
hash. By default, Ohm only returns the id of the record. Nothing more.
It also makes it VERY easy to drop past the abstraction and operate on
Redis directly. It even provides helpers to get the keys it uses to
query Redis. A good example of this is in the Linking and Tagging code.
The following is a method in the Tag model:

``` ruby

	def members=(member)
	  self.key[:members].sadd(member.key)
	  member.tag! self.name unless member.tags.member?(self)
	end

```

Because Links and Tags are a one-to-many across multiple models, I drop
down to Redis and use `sadd` to add the object to a Redis set of objects
sharing the same tag.

It also has a very handy feature which is how the core of Watches are
done. You can define hooks at any phase of Redis interaction - before
and after saves, creates, updates and deletes. the entire Watch system
is nothing more than calling these post hooks to format the state of the
object as JSON, add metadata and send the message using `PUBLISH`
messages to Redis with the Noah namespace as the channel.

# Distribution vectors

I've used this phrase with a few people. Essentially, I want as many
people as possible to be able to use the Noah server component. I've
kept the Ruby dependencies to a minimum and I've made sure that every
single one works on MRI 1.8.7 up to 1.9.2 as well as JRuby. I already
distribute the most current release as a war that can be deployed to a
container or run standalone. I want the lowest barrier to entry to get
the broadest install base possible. When a new PaaS offering comes out,
I pester the hell out of anyone I can find associated with it so I can
get deploy instructions written for Noah. So far you can run it on
Heroku (using the various hosted Redis providers), CloudFoundry and
dotcloud.

I'm a bit more lax on the callback daemon. Because it can be written in
any language that can talk to the Redis pubsub system and because it has
"stricter" performance needs, I'm willing to make the requirements for
the "official" daemon more stringent. It currently ONLY works on MRI
(mainly due to the em-hiredis requirement).

Doing things differently
------------------------

Some people have asked me why I didn't use technology A or technology B.
I think I addressed that mostly above but I'll tackle a couple of key
ones.

ZeroMQ

The main reason for not using 0mq was that I wasn't really aware of it.
Were I to start over and still be using Ruby, I'd probably give it a
good strong look. The would still be the question of the storage
component though. There's still a possible place for it that I'll
address in part four.

NATS

This was something I simply had no idea about until I started poking
around the CloudFoundry code base. I can almost guarantee that NATS will
be a part of Noah in the future. Expect much more information about that
in part four.

MongoDB

You have got to be kidding me, right? I don't trust my data (or anyone
else's for that matter) to a product that doesn't understand what
durability means when we're talking about databases.

Insert favorite data store here

As I said, Redis was the best way to get multiple required functionality
into a single product. Why does a data storage engine have a pubsub
messaging subsystem built in? I don't know off the top of my head but
I'll take it.

Wrap up - Part 3
----------------

So again, because I evidently like recaps, here's the take away:

-   The key components in Noah are Redis and Sinatra
-   Noah is written in Ruby because of time constraints in learning a
    new language
-   Noah strives for the server component to have the broadest set of
    distribution vectors as possible
-   Ruby dependencies are kept to a minimum to ensure the previous point
-   The lightest possible abstractions (Ohm) are used.
-   Stricter requirements exist for non-server components because of
    flexibility in alternates
-   I really should learn me some erlang
-   I'm not a fan of MongoDB

If you haven't guessed, I'm doing one part a night in this series.
Tomorrow is part four which will cover the future plans for Noah. I'm
also planning on a bonus part five to cover things that didn't really
fit into the first four.
