+++
title = "Zeromq and Logstash Part 1"
date = "2012-02-06"
slug = "2012/02/06/zeromq-and-logstash-part-1"
Categories = []
+++
Every once in a while, a software project comes along that makes you rethink how you've done things up until that point. I've often said that ElasticSearch was the first of those projects for me. The other is ZeroMQ.
<!-- more -->

# Edit and update
Evidently my testing missed a pretty critical usecase - pubsub. It doesn't work right now. Due to the way we're doing sockopts works for setting topics. However we don't have a commensurate setting on the PUB side. I've created [LOGSTASH-399](https://logstash.jira.com/browse/LOGSTASH-399) and [LOGSTASH-400](https://logstash.jira.com/browse/LOGSTASH-400) to deal with these issues. I am so sorry about that however it doesn't change the overall tone and content of this message as `pair` and `pushpull` still work.

# A little history
In January of this year, [Jordan](https://twitter.com/jordansissel) merged the first iteration of ZeroMQ support for Logstash. Several people had been asking for it and I had it on my plate to do as well. Funny side note, the pull request for the ZeroMQ plugin was my inspiration for adding [plugin_status](http://logstash.net/docs/1.1.0/plugin-status) to Logstash.

The reason for wanting to mark it experimental is that there was concern over the best approach to using ZeroMQ with Logstash. Did we create a single context per agent? Did we do a context per thread? How well would the multiple layers of indirection work (jvm + ruby + ffi)?

[Brice's](https://twitter.com/_masterzen_) original pull request only hadnled one part of the total ZeroMQ package (PUBSUB) but it was an awesome start. We actually had two other pull requests around the same time but his was first.

A week or so ago, I started a series of posts around doing load balanced filter pipelines with Logstash. The first was [AMQP](http://goo.gl/vWyCH) and then [Redis](http://goo.gl/6W8Lv). The next logical step was ZeroMQ (and something of a "Oh..and one more thing.." post). Sadly, the current version of the plugin was not amenable to doing the same flow. Since it only supported PUBSUB, I needed to do some work on the plugin to get the other socket types supported. I made this my weekend project.

# Something different
One thing that ZeroMQ does amazingly well is make something complex very easy. It exposes common communication patterns over socket types and makes it easy to use them. It really is just plug and play communication.

However it also makes some really powerful flows available to you if you dig deep enough. Look at this example from the [zguide](http://zguide.zeromq.org)

![complex-flow](https://github.com/imatix/zguide/raw/master/images/fig14.png)

Mind you the code for that is pretty simple ([ruby example](http://zguide.zeromq.org/rb:taskwork2)) but we need to enable that level of flexibility and power behind the Logstash config language. We also wanted to avoid the confusion that we faced with the AMQP plugin around exchange vs. queue.

Jordan came up with the idea of removing the socket type confusion and just exposing the patterns. And that's what we've done.

# Configuration
In the configuration language, Logstash exposes the ZeroMQ socket type pairs in the using the same syntax on both inputs and outputs. We call these a "topology". In fact, out of the box, Logstash ZeroMQ support will work out of the box with two agents on the same machine:

## Output

```
input {
  stdin { type => "stdin-input" }
}
output {
  zeromq { topology => "pushpull" }
}
```

## Input

```
input {
  zeromq { topology => "pushpull" type => "zeromq-input" }
}
output {
  stdout { debug => true }
}
```

## Opinionated
Because any side of a socket type in ZeroMQ can be the connecting or binding side (the underlying message flow is disconnected from how the connection is established), Logstash follows the recommendation of the zguide. The more "stable" parts of your infrastructure should be the side that binds/listens while they ephemeral side should be the one that initiates connections.

Following this, we have some sane defaults around the plugins:

* Logstash inputs will, by default, be the `bind` side and bind to all interfaces on port 2120
* Logstash outputs will, by default, be the `connect` side
* Logstash inputs will be the consumer side of a flow
* Logstash outputs will be the producing side of a flow

The last two are obviously pretty "duh" but worth mentioning. Right now Logstash exposes three socket types that make sense for Logstash:

* PUSHPULL (Output is PUSH. Input is PULL)
* PUBSUB (Output is PUB. Input is SUB)
* PAIR

It's worth reading up on ALL [the socket types in ZeroMQ](http://api.zeromq.org/2-1:zmq-socket).

By default, because of how ZeroMQ will most commonly be slotted into your pipeline, it sets the default message format to the Logstash native _json\_event_.

You can still get to the low-level tuning of the sockets via the `sockopts` configuration setting. This is a Logstash config hash. For example, if you wanted to tune the high water mark of a socket (`ZMQ_HWM`), you would do so with this option:

`zeromq { topology => "pushpull" sockopts => ["ZMQ::HWM", 20] }`

These options are passed directly to the `ffi-rzmq` library we use (hence the syntax on the option name). If a new option is added in a later release, it's already available that way.

# Usage of each topology
While I have a few more blog posts in the hopper around ZeroMQ (and various patterns with Logstash), I'll briefly cover where each type might fit.

## PUBSUB
This is exactly what it sounds like. Each output (PUB) broadcasts to all connected inputs (SUB).

## PUSHPULL
This most closely mimics the examples in my previous posts on AMQP and Redis. Each output (PUSH) load-balances across all connected inputs (PULL).

## PAIR
This is essentially a one-to-one streaming socket. While messages CAN flow both directions, Logstash does not support (nor need) that. Outputs stream events to the input.

ZeroMQ has other topologies (like REQREP - request response and ROUTER/DEALER) but they don't really make sense for Logstash right now. For the type of messaging that Logstash does between peers, PAIR is a much better fit. We have plans to expose these in a future release.

# Future
As I said, I've got quite a few ideas for posts around this plugin. It opens up so many avenues for users and makes doing complex pipelines much easier. Here's a sample of some things you'll be able to do:

- Writing your own "broker" to sit between edges and indexers in whatever language works best (8 lines of Ruby)
- Log directly from your application (e.g. log4j ZMQ appender) to logstash with minimal fuss
- Tune ZeroMQ sockopts for durability

Current ZeroMQ support only exists in master right now. However building from source is very easy. Simply clone the repo and type `make`. You don't even need to have Ruby installed. This will leave your very own jar file in the `build` directory.
