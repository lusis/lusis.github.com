---
layout: post
title: "On Noah - Part 2"
date: 2011-05-17 22:38:00
comments: true
categories: [noah, blogger posts]
---

*This is the second part in a series on Noah. Part 1 is available* [here](http://goo.gl/l3Mgt)

In part one of this series, I went over a little background about
ZooKeeper and how the basic Zookeeper concepts are implemented in Noah.
In this post, I want to go over a little bit about a few things that
Noah does differently.
<!--more-->

## Noah Primitives

As mentioned in the previous post, Noah has 5 essential data types, four
of which are what I've interchangeably refered to as either Primitives
and Opinionated models. The four primitives are Host, Service,
Application and Configuration. The idea was to map some common use cases
for Zookeeper and Noah onto a set of objects that users would find
familiar.

You might detect a bit of Nagios inspiration in the first two.

* **Host:**
    Analogous to a traditional host or server. The machine or instance running the operating system. Unique by name.
* **Service:**
    Typically mapped to something like HTTP or HTTPS. Think of this as the listening port on a Host. Services must be bound to Hosts. Unique by service name and host name.
* **Application:**
    Apache, your application (rails, php, java, whatever). There's a subtle difference here from Service. Unique by name.
* **Configuration:**
    A distinct configuration element. Has a one-to-many relationship with Applications. Supports limited mime typing.

Hosts and Services have a unique attribute known as `status`. This is a
required attribute and is one of `up`,`down` or `pending`. These
primitives would work very well integrated into the OS init process.
Since Noah is curl-friendly, you could add something globally to init
scripts that updated Noah when your host is starting up or when some
critical init script starts. If you were to imagine Noah primitives as
part of the OSI model, these are analagous to Layers 2 and 3.

Applications and Configurations are intended to feel more like Layer 7
(again, using our OSI model analogy). The differentiation is that your
application might be a Sinatra or Java application that has a set of
Configurations associated with it. Interestingly enough, you might
choose to have something like Tomcat act as both a Service AND an
Application. The aspect of Tomcat as a Service is different than the
Java applications running in the container or even Tomcat's own
configurations (such as logging).

One thing I'm trying to pull off with Configurations is limited
mime-type support. When creating a Configuration in Noah, you can assign
a `format` attribute. Currently 3 formats or types are understood:

-   string
-   json
-   yaml

The idea is that, if you provide a type, we will serve that content back
to you in that format when you request it (assuming you request it that
way via HTTP headers). This should allow you to skip parsing the JSON
representation of the whole object and instead use it directly. Right
now this list is hardcoded. I have a task to convert this.

Hosts and Services make a great "canned" structure for building a
monitoring system on top of Noah. Applications and Configurations are a
lightweight configuration management system. Obviously there are more
uses than that but it's a good way to look at it.

## Ephemerals

Ephemerals, as mentioned previously, are closer to what Zookeeper
provides. The way I like to describe Ephemerals to people is a '512 byte
key/value store with triggers' (via Watch callbacks). If none of the
Primitives fit your use case, the Ephemerals make a good place to start.
Simply send some data in the body of your post to the url and the data
is stored there. No attempt is made to understand or interpret the data.
The hierarchy of objects in the Ephemeral namespace is completely
arbitrary. Data living at `/ephemerals/foo` has no relationship with
data living at `/ephemerals/foo/bar`.

Ephemerals are also not browseable except via a Linking and Tagging.

## Links and Tags

Links and Tags are, as far as I can tell, unique to Noah compared to
Zookeeper. Because we namespace against Primitives and Ephemerals, there
existed the need to visualize objects under a custom hierarchy.
Currently Links and Tags are the only way to visualize Ephemerals in a
JSON format.

Tags are pretty standard across the internet by now. You might choose to
tag a bunch of items as `production` or perhaps group a set of Hosts and
Services as `out-of-service`. Tagging an item is a simple process in the
API. Simply `PUT` the name of the tag(s) to the url of a distinct named
item appended by `tag`. For instance, the following JSON posted to
`/applications/my_kick_ass_app/tag` with tag the Application
`my_kick_ass_app` with the tags `sinatra`, `production` and `foobar`:

```javascript

	{"tags":["sinatra", "production", "foobar"]}

```

Links work similar to Tags (including the act of linking) except that
the top level namespace is now replaced with the name of the Link. The
top level namespace in Noah for the purposes of Watches is `//noah`. By
linking a group of objects together, you will be able to (not yet
implemented), perform operations such as Watches in bulk. For instance,
if you wanted to be informed of all changes to your objects in Noah, you
would create a Watch against `//noah/*`. This works fine for most people
but imagine you wanted a more multi-tenant friendly system. By using
links, you can group ONLY the objects you care about and create the
watch against that link. So `//noah/*` becomes `//my_organization/*` and
only those changes to items in that namespace will fire for that Watch.

The idea is also that other operations outside of setting Watches can be
applied to the underlying object in the link as well. The name Link was
inspired by the idea of symlinking.

## Watches and Callbacks

In the first post, I mentioned that by nature of Noah being
"disconnected", Watches were persistent as opposed to one-shot.
Additionally, because of the pluggable nature of Noah Watches and
because Noah has no opinion regarding the destination of a fired Watch,
it becomes very easy to use Noah as a broadcast mechanism. You don't
need to have watches for each interested party. Instead, you can create
a callback plugin that could dump the messages on an ActiveMQ Fanout
queue or AMQP broadcast exchange. You could even use multicast to notify
multiple interested parties at once.

Again, the act of creating a watch and the destination for notifications
is entirely disconnected from the final client that might use the
information in that watch event.

Additionally, because of how changes are broadcast internally to Noah,
you don't even have to use the "official" Watch method. All actions in
Noah are published post-commit to a pubsub queue in Redis. Any language
that supports Redis pubsub can attach directly to the queue and
PSUBSCRIBE to the entire namespace or a subset. You can write your own
engine for listening, filtering and notifying clients.

This is exactly how the Watcher daemon works. It attaches to the Redis
pubsub queue, makes a few API calls for the current registered set of
watches and then uses the watches to filter messages. When a new watch
is created, that message is like any other change in Noah. The watcher
daemon sees that and immediately adds it to its internal filter. This
means that you can create a new watch, immediately change the watched
object and the callback will be made.

## Wrap up - Part Two

So to wrap up:

-   Noah has 5 basic "objects" in the system. Four of those are
    opinionated and come with specific contracts. The other is a "dumb"
    key/value store of sorts.
-   Noah provides Links and Tags as a way to perform logical grouping of
    these objects. Links replace the top-level hierarchy.
-   Watches are persistent. The act of creating a watch and notifying on
    watched objects is disconnected from the final recipient of the
    message. System A can register a watch on behalf of System B.
-   Watches are nothing more than a set of filters applied to a Redis
    pubsub queue listener. Any language that supports Redis and its
    pubsub queue can be a processor for watches.
-   You don't even have to register any Watches in Noah if you choose to
    attach and filter yourself.

Part three in this series will discuss the technology stack under Noah
and the reasoning behind it. A bit of that was touched on in this post.
Part four is the discussion about long-term goals and roadmaps.

