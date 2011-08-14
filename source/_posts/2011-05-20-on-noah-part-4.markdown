---
layout: post
title: On Noah - Part 4
date: 2011-05-20 02:01:00
categories: [noah, blogger posts]
---

*This is the fourth part in a series on Noah. [Part 1](http://goo.gl/l3Mgt), [Part 2](http://goo.gl/Nj2TN) and [Part 3](http://goo.gl/RsZtZ) are available as well*

In Part 1 and 2 of this series I covered background on Zookeeper and
discussed the similarities and differences between it and Noah. Part 3
was about the components underneath Noah that make it tick.

This post is about the "future" of Noah. Since I'm a fan of Fourcast
podcast, I thought it would be nice to do an immediate, medium and long
term set of goals.
<!--more-->
# Immediate Future - the road to 1.0

In the most immediate future there are a few things that need to happen.
These are in no specific order.

-   General
    -   Better test coverage ESPECIALLY around the watch subsystem
    -   Full code comment coverage
    -   Chef cookbooks/Puppet manifests for doing a full install
    -   "fatty" installers for a standalone server
    -   Documentation around operational best practices
    -   Documentation around clustering, redundancy and hadr
    -   Documentation around integration best practices
    -   Performance testing

-   Noah Server
    -   Expiry flags and reaping for Ephemerals
    -   Convert mime-type in Configurations to make sense
    -   Untag and Unlink support
    -   Refactor how you specify Redis connection information
    -   Integrated metrics for monitoring (failed callbacks, expired
        ephemeral count, that kind of stuff)

-   Watcher callback daemon
    -   Make the HTTP callback plugin more flexible
    -   Finish binscript for the watcher daemon

-   Other
    -   Finish [Boat](http://goo.gl/B65aL)
    -   Finish NoahLite LWRP for Chef (using Boat)
    -   A few more HTTP-based callback plugins (Rundeck, Jenkins)

Now that doesn't look like a very cool list but it's a lot of work for
one person. I don't blame anyone for not getting excited about it. The
goal now is to get a functional and stable application out the door that
people can start using. Mind you I think it's usable now (and I'm
already using it in "production").

Obviously if anyone has something else they'd like to see on the list,
let me know.

# Medium Rare

So beyond that 1.0 release, what's on tap? Most of the work will
probably occur around the watcher subsystem and the callback daemon.
However there are a few key server changes I need to implement.

-   Server
    -   Full ACL support on every object at every level
    -   Token-based and SSH key based credentialing
    -   Optional versioning on every object at every level
    -   Accountability/Audit trail
    -   Implement a long-polling interface for inband watchers

-   Watcher callback daemon
    -   Decouple the callback daemon from the Ruby API of the server.
        Instead the daemon itself needs to be a full REST client of the
        Noah server
    -   Break out the "official" callback daemon into a distinct package

-   Clients
    -   Sinatra Helper

Also during this period, I want to spend time building up the ecosystem
as a whole. You can see a general mindmap of that
[here](https://github.com/lusis/Noah/wiki/Ecosystem).

Going into a bit more detail...

## Tokens and keys

It's plainly clear that something which has the ability to make runtime
environment changes needs to be secure. The first thing to roll off the
line post-1.0 will be that functionality. Full ACL support for all
entries will be enabled and in can be set at any level in the namespace
just the same as Watches.

## Versioning and Auditing

Again for all entires and levels in the namespace, versioning and
auditing will be allowed. The intention is that the number of revisions
and audit entries are configurable as well - not just an enable/disable
bit.

## In-band watches

While I've lamented the fact that watches were in-band only in
Zookeeper, there's a real world need for that model. The idea of
long-polling functionality is something I'd actually like to have by 1.0
but likely won't happen. The intent is simply that when you call say
`/some/path/watch`, you can pass an optional flag in the message stating
that you want to watch that endpoint for a fixed amount of time for any
changes. Optionally a way to subscribe to all changes over long-polling
for a fixed amount of time is cool too.

## Agent changes

These two are pretty high on my list. As I said, there's a workable
solution with minimal tech debt going into the 1.0 release but long
term, this needs to be a distinct package. A few other ideas I'm kicking
around are allowing configurable filtering on WHICH callback types an
agent will handle. The idea is that you can specify that this invocation
only handle http callbacks while this other one handles AMQP.

## Sinatra Helper

One idea I'd REALLY like to come to fruition is the Sinatra Helper. I
envision it working something like this:

``` ruby

	require 'sinatra/base'

	class MyApp < Sinatra::Base
	  register Noah::Sinatra
	
	  noah_server "http://localhost:5678"
	  noah_node_name "myself"
	  noah_app_name "MyApp"
	  noah_token "somerandomlongstring"
	  dynamic_get :database_server
	  dynamic_set :some_other_variable, "foobar"
	  watch :this_other_node
	end

```

The idea is that the helper allows you to register your application very
easily with Noah for other components in your environment to be know. As
a byproduct, you get the ability to get/set certain configuration
parameters entirely in Noah. The watch setting is kind of cool as well.
What will happen is if you decide to `watch` something this way, the
helper will create a random (and yes, secure) route in your application
that watch events can notify. In this way, your Sinatra application can
be notified of any changes and will automatically "reconfigure" itself.

Obviously I'd love to see other implementations of this idea for other
languages and frameworks.

# Long term changes

There aren't so much specific list items here as general themes and
ideas. While I list these as long term, I've already gotten an offer to
help with some of them so they might actually get out sooner.

## Making Noah itself distributed

This is something I'm VERY keen on getting accomplished and would really
consider it the fruition of what Noah itself does. The idea is simply
that multiple Noah servers themselves are clients of other Noah servers.
I've got several ideas about how to accomplish this but I got an
interesting follow up from someone on Github the other day. He asked
what my plans were in this area and we had several lengthy emails back
and forth including an offer to work on this particular issue.

Obviously there are a whole host of issues to consider. Race conditions
in ordered delivery of Watch callbacks (getting a status "down" after a
status "up" when it's supposed to be the other way around..) and
eventual consistency spring to mind first.

The general architecture idea that was offered up is to use
[NATS](https://github.com/derekcollison/nats) as the mechanism for
accomplishing this. In the same way that there would be AMQP callback
support, there would be NATS support. Additional Noah servers would only
need to know one other member to bootstrap and everything else happens
using the natural flows within Noah.

The other part of that is how to handle the Redis part. The natural
inclination is to use the upcoming Redis clustering but that's not
something I want to do. I want each Noah server to actually include its
OWN Redis instance "embedded" and not need to rely on any external
mechanism for replication of the data. Again, the biggest validation of
what Noah is designed to do is using only Noah itself to do it.

## Move off Redis/Swappable persistence

If NATS says anything to me, it says "Why do you even need Redis?". If
you recall, I went with Redis because it solved multiple problems. If I
can find a persistence mechanism that I can use without any external
service running, I'd love to use it.

## ZeroMQ

If I were to end up moving off Redis, I'd need a cross platform and
cross language way to handle the pubsub component. NATS would be the
first idea but NATS is Ruby only (unless I've missed something). ZeroMQ
appears to have broad language and platform support so writing custom
agents in the same vein as the Redis PUBSUB method should be feasible.

## Nanite-style agents

This is more of a command-and-control topic but a set of
high-performance specialized agents on systems that can watch the PUBSUB
backend or listen for callbacks would be awesome. This would allow you
really integrate Noah into your infrastructure beyond the application
level. Use it to trigger a puppet or chef run, reboot instances or do
whatever. This is really about bringing what I wanted to accomplish with
Vogeler into Noah.

## The PAXOS question

A lot of people have asked me about this. I'll state right now that I
can only make it through about 20-30% of any reading about Paxos before
my brain starts to melt. However in the interest of proving myself the
fool, I think it would be possible to implement some Paxos like
functionality on top of Noah. Remember that Noah is fundamentally about
fully disconnected nodes. What better example of a network of unreliable
processors than ones that never actually talk to each other. The problem
is that the use case for doing it in Noah is fairly limited so as not to
be worth it.

The grand scheme is that Noah helps enable the construction of systems
where you can say "This component is free to go off and operate in this
way secure in the knowledge that if something it needs to know changes,
someone will tell it". I did say "grand" didn't I? At some point, I may
hit the limit of what I can do using only Ruby. Who knows.

# Wrap up - Part 4

Again with the recap

-   Get to 1.0 with a stable and fixed set of functionality
-   Nurture the Noah ecosystem
-   Make it easy for people to integrate Noah into thier applications
-   Get all meta and make Noah itself distributed using Noah
-   Minimize the dependencies even more
-   Build skynet

*I'm not kidding on that last one. Ask me about Parrot AR drones and
Noah sometime*

If you made it this far, I want to say thank you to anyone who read any
or all of the parts. Please don't hesitate to contact me with any
questions about the project.
