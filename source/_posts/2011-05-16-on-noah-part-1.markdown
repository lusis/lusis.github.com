---
layout: post
title: "On Noah - Part 1"
comments: true
date: 2011-05-16 23:16
categories: [noah, blogger posts]
---

*This is the first part in a series of posts going over Noah*

As you may have heard (from my own mouth no less), I've got a smallish
side project I've been working on called
[Noah](https://github.com/lusis/Noah).

<!--more-->
It's a project I've been wanting to work on for a long time now and
earlier this year I got off my ass and started hacking. The response has
been nothing short of overwhelming. I've heard from so many people how
they're excited for it and nothing could drive me harder to work on it
than that feedback. To everyone who doesn't run away when I talk your
ear off about it, thank you so much.

Since I never really wrote an "official" post about it, I thought this
would be a good opportunity to talk about what it is, what my ideas are
and where I'd like to see it go in the future.

# So why Noah?

*fair warning. much of the following may be duplicates of information in
the Noah wiki*

The inspiration for Noah came from a few places but the biggest
inspiration is [Apache Zookeeper](http://goo.gl/WGCxY). Zookeeper is one
of those things that by virtue of its design is a BUNCH of different
things. It's all about perspective. I'm going to (yet again) paste the
description of Zookeeper straight from the project site:

    ZooKeeper is a centralized service for maintaining configuration information, naming, providing distributed synchronization, and providing group services.

Now that might be a bit confusing at first. Which is it? Is it a
configuration management system? A naming system? It's all of them and,
again, it's all about perspective.

Zookeeper, however, has a few problems for my standard use case.

-   Limited client library support
-   Requires persistent connections to the server for full benefit

By the first, I mean that the only official language bindings are C and
Java. There's contributed Python support and Twitter maintains a Ruby
library. However all of these bindings are "native" and must be
compiled. There is also a command-line client that you can use for
interacting as well - one in Java and two C flavors.

The second is more of a showstopper. Zookeeper uses the client
connection to the server as in-band signaling. This is how watches
(discussed in a moment) are communicated to clients. Persistent
connections are simply not always an option. I can't deploy something to
Heroku or AppEngine that requires that persistent connection. Even if I
could, it would be cost-prohibitive and honestly wouldn't make sense.

Looking at the list of features I loved about ZK, I thought "How would I
make that work in the disconnected world?". By that I mean what would it
take to implement any or all of the Zookeeper functionality as a service
that other applications could use?

From that thought process, I came up with Noah. The name is only a play
on the concept of a zookeeper and holds no other real significance other
than irritation at least two people named Noah when I talk about the
project.

So working through the feature list, I came up with a few things I
**REALLY** wanted. I wanted Znodes, Watches and I wanted to do it all
over HTTP so that I could have the broadest set of client support. JSON
is really the defacto standard for web "messaging" at this point so
that's what I went with. Basically the goal was "If your language can
make HTTP requests and parse JSON, you can write a Noah client"

# Znodes and Noah primitives

Zookeeper has a shared hierarchical namespace similar to a UNIX
filesystem. Points in the hierarchy are called `znodes`. Essentially
these are arbitrary paths where you can store bits of data - up to 1MB
in size. These znodes are unique absolute paths. For instance:


	//systems/foo/bar/networks/kansas/router-1/router-2

Each fully qualified path is a unique znode. Znodes can be ephemeral or
persistent. Zookeeper also has some primitives that can be applied to
znodes such as 'sequence`.

When I originally started working on Noah, so that I could work with a
model, I created some base primitives that would help me demonstrate an
example of some of the use cases:

-   Host
-   Service
-   Application
-   Configuration

These primitives were actual models in the Noah code base with a strict
contract on them. As an example, Hosts must have a status and can have
any number of services associated with them. Services MUST be tied
explicity to a host. Applications can have Configurations (or not) and
Configurations can belong to any number of Applications or not.
Additionally, I had another "data type" that I was simply calling
Ephemerals. This is similar to the Zookeeper znode model. Originally I
intended for Ephemerals to be just that - ephemeral. But I've backed off
that plan. In Noah, Ephemerals can be either persistent or truely
ephemeral (not yet implemented).

So now I had a data model to work with. A place to store information and
flexibility to allow people to use the predefined primitives or the
ephemerals for storing arbitrary bits of information.

# Living the disconnected life

As I said, the model for my implementation was "disconnected". When
thinking about how to implement Watches in a disconnected model, the
only thing that made sense to me was a callback system. Clients would
register an interest on an object in the system and when that object
changed, they would get notified by the method of their choosing.

One thing about Watches in Zookeeper that annoys me is that they're
one-shot deals. If you register a watch on a znode, once that watch is
triggered, you have to REREGISTER the watch. First off this creates, as
documented by the ZK project, a window of opportunity where you could
miss another change to that watch. Let's assume you aren't using a
language where interacting with Zookeeper is a synchronous process:

-   Connect to ZK
-   Register watch on znode
-   Wait
-   Change happens
-   Watch fires
-   Process watch event
-   Reregister watch on znode

In between those last two steps, you risk missing activity on the znode.
In the Noah world, watches are persistent. This makes sense for two
reasons. The first is that the latency between a watch callback being
fired and proccessed could be much higher than the persistent connection
in ZK. The window of missed messages is simply much greater. We could
easily be talking 100's of milliseconds of latency just to get the
message and more so to reregister the watch.

Secondly, the registration of Watches in Noah is, by nature of Noah's
design and as a byproduct, disconnected from the consumer of those
watches. This offers much greater flexibility in what watches can do.
Let's look at a few examples.

First off, it's important to understand how Noah handles callbacks. The
message format of a callback in Noah is simply a JSON representation of
the changed state of an object and some metadata about the action taken
(i.e. delete, create, update). Watches can be registered on distinct
objects, a given path (and thus all the children under that path) and
further refined down to a given action. Out of the box, Noah ships with
one callback handler - http. This means that when you register a watch
on a path or object, you provide an http endpoint where Noah can post
the aforementioned JSON message. What you do with it from there is up to
you.

By virtue of the above, the callback system is also designed to be
'pluggable' for lack of a better word. While the out of the box
experience is an http post, you could easily write a callback handler
that posted the message to an AMQP exchange or wrote the information to
disk as a flat file. The only requirement is that you represent the
callback location as a single string. The string will be parsed as a url
and broken down into tokens that determine which plugin to call.

So this system allows for you to distribute watches to multiple systems
with a single callback. Interestingly enough, this same watch callback
system forms the basis of how Noah servers will share changes with each
other in the future.

# Wrap up - Part 1

So wrapping up what I've discussed, here are the key take aways:

-   Noah is a 'port' of specific Zookeeper functionality to a
    disconnected and asynchronous world
-   Noah uses HTTP and JSON as the interface to the server
-   Noah has both traditional ZK-style Ephemerals as well as opinionated
    Primitives
-   Noah uses a pluggable callback system to approximate the Watch
    functionality in Zookeeper
-   Clients can be written in any language that can speak HTTP and
    understand JSON (yes, even a shell script)

# Part 2 and beyond

In part two of this series we'll discuss some of the additions to Noah
that aren't a part of Zookeeper such as Tags and Links. Part 3 will
cover the underlying technology which I am intentionally not discussing
at this point. Part 4 will be a roadmap of my future plans for Noah.
