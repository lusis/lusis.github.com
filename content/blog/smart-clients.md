+++
title = "Smart Clients"
date = "2013-05-13"
slug = "2013/05/13/smart-clients"
Categories = []
+++

Currently [RICON|EAST](http://ricon.io/east.html) is going on in NYC. [Tom Santero](https://twitter.com/tsantero) and the whole Basho crew is doing an awesome job if the content available via the live stream and twitters is to be believed.
<!--more-->

_(please note this is my first blog entry post-D. As such and because I've yet to talk to any legal folks, I should state that this does not represent any opinion or policy of Dell)_

One thing that caught my eye/ear when I could listen/watch was an excellent presentation by [Sean Cribbs](https://twitter.com/seancribbs). Sean holds a special place in my hero worship pantheon for a few reasons:

- I first heard about Riak on one of the first episodes of the ChangeLog show (along with [Andy Gross](https://twitter.com/argv0)). It and the whole NoSQL thing made sense then
- Sean is a pretty fucking down to earth person. He graciously drove down to one of our local meetups. Uber friendly and an awesome advocate for Basho and Riak.
- He's a really awesome presenter and if you've never had the priviledge of seeing him (live stream or in person), he rocks the mic.

So in Sean's presentation he's talking about some changes to the Ruby client library for Riak. Many of the changes make the Ruby library a proper smart client. Read [this wiki](https://github.com/basho/riak-ruby-client/wiki/Connecting-to-Riak) wiki entry under **Connecting to Clusters** for some of the features. It's awesome (especially the transport-related failure handling).

# Client libraries in general
I want to say a bit about client libraries. Regardless of what they talk to (though I'll be talking specifically about database client libraries), this is something many companies get wrong.

Everyone knows I'm not the biggest MongoDB/10Gen fan in the world. I won't go into detail about the technical reasons behind that. Many others have done a much more eloquent dive into that topic.
As much as it's easy to make fun of MongoDB as being an marketing-driven database, they did get one thing right. They owned their client driver availability. Not only did they own and maintain all the drivers but they largely had the same API across the various languages.

Then again, they had to. Other databases/applications offer a REST-ish interface over HTTP (or plain-text interface like Redis) so they can punt a bit. Got a libcurl port for your language? You're set. MongoDB has its own protocol and that god-forsaken BSON shitshow.

One of the benefits, however, of a plain-text or HTTP-based protocol is that it's a pattern we can grok as operators and developers. We load balance webservers. We speak to third-party APIs. It's not the most EFFICIENT but it's a known quantity. It's also, as I said, REALLY fucking easy to add support to your language of choice. No need to FFI some c library or make a binary extension. Any language worth its salt has http client support in stdlib (even if it's as big a pile of dog squeeze as net/http). Again, most languages also have libcurl support for something better.

# Back to smart clients
I largely dislike applications that require smart clients to get the full benefit. As an operations person, I'm USUALLY using a dynamic language like python, ruby or perl to access the system as opposed to directly from the application. This was my biggest gripe with ZooKeeper (as I've said many times in the past). It's also been one of my points of contention with Datomic. If you aren't on a JVM language, you're shit out of luck for now. Yes, JRuby makes this billions of times easier for those of us using Ruby but Jython is still not where it needs to be for modern Python.

I also had this problem with Voldemort. Disclaimer this is 2-3 year old data from running Voldemort in production. AFAIK, it's still the case. For the sake of this discussion, we're going to ignore data opacity. At the time, the only way to fully access the data in and maintain a Voldemort cluster was from the JVM. I ended up writing quite a bit of JRuby wrapper around StoreClient just to see the data we had in Voldemort.

Riak (and as another example, ElasticSearch) is nice in this regard. It's HTTP. I can curl it from a shell script. I can use the Ruby library Basho is maintaining. If I'm using a language without 'official' support, I can write my own. All the metadata is largely attached to http headers and even monitoring is done via the `/ping` and `/stats` urls. Something I didn't realize until today (thanks to [Benjamin Black](https://twitter.com/b6n)) is that the stats interface actually exposes stuff I had previously glossed over including cluster topology. This is where the meat of the discussion on twitter today happened.

# Operational Happiness 
My original statements on this discussion related to using haproxy in front of your Riak cluster. There are several reasons I prefer this but a quick sidebar

## Seed Nodes
I've had some minor operational experience with Cassandra (nothing to write home about) but one of the things that always bothered me was the idea of 'seed nodes'. Let me be clear that Cassandra and Riak are pretty much the only two datastores I'd feel comfortable using these days (with the nod going to Riak) in any sort of scalable environment. Postgres has earned its way back to my into my graces but MySQL can _insert Louis C.K. euphemism here_.

My problem with seed nodes is the idea that I have special nodes. These nodes have to be hard-coded in a config file somewhere or discovered by some other method. I could store them as DNS lookups but now I've got to deal with TTLs on DNS. And I've got to deal with the fact that DNS doesn't actually care if the host I've been given is actually alive or not.

I could store this information in ZooKeeper but what if I don't actually have native ZK support in that database? I've got to write something that populates ZK when a new node is available and it's not actually a live check. I still have to test that host first. Yes you should do that anyway but it's a valid point.

So if I'm storing seed nodes as DNS names in a config file, I can never change those names without either rolling out new code or configs. That might require a restart somewhere. If I'm clever, I could probably make that an administrative hook in my application (think JMX) where I can fiddle the seed list. I can poll a config file for changes. I can do a lot of thing but none of them are "optimal" to me.

## Back to haproxy
My prefered method of using Riak is to stick everything behind haproxy. There are several reasons for this but here are a few reasons (note we're going to assume use of CM at this point):

- Operationally, haproxy nodes are easier to manage than application configs (depending on the application).
- Many times, at different companies, we've had to roll our own layer on top of a client library (or even write our own due to licensing issues). Load-balancing is not neccessarily a core application developer competency (and it's bitten me in the ass before).
- From an operational perspective, I can bring nodes in and out of service in haproxy for maintenance without needing to inform the client or have it waste cycles with stale node detection. It simply will never talk to an out of service backend.

Basically the haproxy crew has already done all the work in load balancing HTTP connections intelligently and they're pretty damn good at it. I love my developers and I know the folks writing the various libraries I use are smart people but again it goes back to core competency. Note that Basho gets a nod here, however, in that I'm pretty sure that, what with writing webmachine, they grok http pretty well.

I've even gone the approach of using haproxy on every system that needs to connect to some backend locally. This totally eliminates the idea of fixed points in the network for connections (at the expense of having to deal with a bit of drift between CM runs). If I wanted to, I could even make multiple riak clusters transparent behind haproxy (though I can only think of a few REALLY specific use cases for that).

## Trade offs
Yes there are trade-offs to this approach. As Ben pointed out, the Riak stats interface is really powerful from a topology perspective. A REALLY smart riak client can discovery data layout and make deeply intelligent decisions on that.

With other applications like ElasticSearch I can actually become a full fledged cluster member as a non-data node and actually offload some of the work of the cluster for scatter/gather type operations using the Java library.

With haproxy, I don't get those types of benefits.

## What I do get
Outside of the maintainability of haproxy (which is, again, subjective) I get one benefit I **CAN'T** get with smart clients that is, unfortunately, neccessary with many customers - a more narrow network allowance.

Enterprises are interesting beasts and still think in terms of traditional tiered application stacks. Nothing wrong with that and it does have SOME security benefits but many deployments I've been involved with have official policies that 'data tiers' (whatever the hell those are) must be protected in a dedicated network behind an additional firewall. So here's Riak, a 'data tier', that we have to have with as small an ingress as possible. That rules out smart clients of the toplogy-aware variety. So we stick haproxy in the mix. We tell them to use a load balancer. Some folks use an internal F5 HA pair with some sort of VRRP. We'll set up haproxy + keepalived or some other combination depending.

# TMTOWTDI
One of the things that Riak allows is this level of flexibility. I can use HTTP and haproxy. I can use HTTP and smart client. I can use protobufs and haproxy or a smart client. It's really that flexible. I happen to prefer the haproxy approach for reasons I've already mentioned but I totally grok that some folks want a more intelligent client approach. Some folks would argue that there's a right way and a wrong way but I don't see it like that. What I see is a datastore that, just like it letting me control the consistency levels I want, let's me control HOW I access that data.
