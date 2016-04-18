---
layout: post
title: "Monitoring sucking just a little bit less"
date: 2012-06-05 12:22
comments: true
categories: 
---

So [it's come to my attention](http://blog.zenoss.com/2012/06/turning-monitoringsucks-into-monitoringsucksless) that today is the "anniversary" of when I wrote my [first "#monitoringsucks" blog post](http://blog.lusis.org/blog/2011/06/05/why-monitoring-sucks/).

<!--more-->

So the question I found myself asking today is "Does monitoring suck less than it did a year ago?". I'd have to be an idiot to say anything other than "yes".

I didn't set out to start a "movement". I'm not someone who handles compliments very well. Mainly because I suffer from a bad case of [imposter syndrome](http://kartar.net/2012/05/imposter-syndrome/). The other side of this is that I know that I've done next to nothing to make it any better. Meanwhile the real heros are people releasing code every day. People rethinking how we think about monitoring, trending, alerting and everything else. Companies like Etsy, Netflix, Yammer and countless more are sharing the deep squishy bits of how they do things and are releasing code to back it up.

# What's gotten better?
I think the biggest thing that's gotten better is that people are really starting to leverage advances we've had in ancillary tooling in recent times. Not that any of these ideas are new (message busses existed long before RabbitMQ) but the barrier to entry is much lower.

I still feel like what drove this was a byproduct of configuration management uptake. As it became easier to stamp out servers, the rate of change in our infrastructure surpassed what tools were original designed to deal with. As we started having more time to think about what we wanted to monitor (because we weren't spending all our time building hand-crafted artisan machines), we started to feel the pain more. We wanted our monitoring systems to work as smoothly as the rest of the kit but it didn't. 

Combine that with:

- We now have data storage engines of varying complexity for storing time-series data. And with greater resolution and the ability to change that resolution.
- We have tooling that can read that timeseries data and represent it in dynamic ways.
- We revisted the idea of push vs. pull and leave/join of components in our infra.

The world is a brighter place because of this and to everyone who had something to do with it - whether discussing on a mailing list, talking on IRC, tweeting, writing code or whatever - thank you.

# What's coming down the pipe
As a side effect of this monitoring thing, people ask my opinion a lot. Like I said, I'm still weirded out by this. Stepping back, though, and just geeking out on things, I see some really cool stuff in the future. Here's just a subset of 'stuff' I've been thinking about:

## Presence-based Discovery
I couldn't think of a better way to describe it but the idea is simply (or not so simply) that by virtue of coming online, a system is saying "I wish to be monitored in this way". This is pretty dependent on configuration management for this to go smoothly, imho.

I mentioned this in a post on the devops toolchain but it goes something like this:

- new node comes online
- new node registers its presence in some way (I'm kind of keen on the XMPP idea) with a notification of services it offers
- centralized system is monitoring (har har) this presence system and starts monitoring the system based on predefined criteria at a "well-known" endpoint
- optionally the system can dictate what it wants monitored

Obviously this would all be very painful without some sort of configuration management system. However, it's very easy for me in my base group or role for a system to say "Install `W`, register via `X`, listen on `Y` for active checks and publish everything to `Z` endpoint". What `W`,`X`,`Y` and `Z` are is irrelevant. We can cookie cutter this stuff. FWIW this is nothing new. We're just seeing "consumer-grade" options that are usable by everyone.

## Push vs Poll
I've said many times that poll-based monitoring is dead. That's a bit of hyperbole. What's dead is the idea that we can only check `X` every `N` times over `K` period. This is a hold-over from ineffecient polling mechanisms that would crumble under too-frequent polling as well as systems that weren't able to handle being polled that often. I see polling moving from "check host `X` every 5 minutes for memory usage" to "watch this bus for memory usage stats and if there's not anything in 2 minutes, make sure the world is okay". We'll always need the outside-in checks of things but that's much less intensive than polling ALL the things.

We're so close with tools like Graphite now which can accept arbitrary metrics from anywhere with no need to preconfigure it to accept them. There are some concerns here around bad data being injected from unauthorized sources. As we automate more and make decisions based on this data, we need to be aware of it. Another discussion for another day though something akin to the way mcollective does trust is probably in order.

## Self-service and Culture
This is a big one too and I think will cut down on many complaints that people have around even something like Nagios.

We have to be able to say "You know what? We don't need to monitor that. Let's disable that check". If something is unactionable, then why the hell are you alerting on it? This is where decoupling the trending/visualization from alerting can be so powerful. I'm currently rebuilding our monitoring setup to do most checks based on data in Graphite. Why? Because if I flip the relationship around, I've now got to deal with the alert question before I can even get the information. Instead of alerting on data and then storing it as an afterthought (perfdata anyone?) let's start collecting the data, storing it and then alerting based on it.

This also provides for options around self-service. Not everyone needs to know about the disk space on nodeX. Only the people who can fix it do. Maybe your database folks want to get alerts on queries taking longer than N. As an operations person, you can't do anything about that and certainly not at that moment (in most cases). You're just going to push it down the line ANYWAY. And do you really want to have to deal with changing thresholds on behalf of someone?

I'm also a big fan of the idea that components in your infrastructure - applications, os, whatever - self-host a pubsub type of endpoint where users can get realtime information about the system themselves. I do this with every logstash install I setup where possible. Every remote logstash agent I've setup in enStratus also provides a pubsub 0mq socket that you can use to live tail log information from that host broken down in topic keys around metadata.

## Application health by default
I've fawned over Coda Hale's "Metrics" talk several times. I'm far from a fanboy but "Metrics" gets it right. This ties into self-service quite a bit. Developers need to be free to instrument the application without creating "yet another place to look". Metrics does this so well with the idea of pushing instrumentation out of the application (oh look - push again!) and into Graphite or whereever is appropriate. And if you aren't ready for push yet, you can still poll via JMX. 

The idea has been ported to multiple other languages at this point. There no excuse not to deeply instrument your applications. If you have an application that CAN'T be instrumented properly, maybe you should consider a different application?

## Applying science and common sense
The last thing that I see as being a step forward is we start applying science to our process. No more `-w 60,60,60 -c 75,75,75` canned thresholds. We start thinking about our thresholds. Maybe we do away with them entirely as static constructs. Instead we apply event processing and historical data to build thresholds that are intelligent.

We start looking at the shape of our infra. Is it REALLY important that you get woken up at 3AM because a Riak node is down? Not if it's just one but maybe if it's two depending on your cluster size. Maybe both of those nodes were in the same rack. Okay that's bad.

We start to consider context and start applying science! We step out of the shamanistic ages of monitoring (who the hell still sets swap space to double physical memory and is 75% of a 1TB volume in use really something that can't wait?) where "We've always done it this way". Start thinking like the Apple 1984 commercial, whip out your hammer and smash your preexisting notions around what constitutes an alert.

# What I'm building
Right now I'm spending most of my time being pragmatic. I'm still adding new checks to Nagios but the information is coming from different sources now. I'm dumping data into graphite via logstash and doing checks on that. Collectd is now pushing directly to graphite as well. Nagios is becoming less and less of a factor. When I finally strip it down to its bare essentials, I'll have a better idea what gap needs to be filled. It's starting to look like riemann at that point.

I still want to tackle this presence based idea in some form. Even if presence is just a signal to run chef-client. At my previous company, we used Noah for this. I've not yet had time to decide if Noah is the right fight here.

# What others are building
What's more important is what others are building. There are too many to list but I'm going to give it a shot off the top of my head. Please don't take offense if your project isn't here.

- Sensu
- Graphite-tattle
- Cepmon
- Logstash
- Librato
- PagerDuty
- Umpire
- alerting-controller
- Graphite
- Statsd
- Logster
- Metrics
- Incinga (yes, they're starting to diverge from Nagios)
- ZeroMQ
- Chef
- Puppet
- Zookeeper
- Riemann
- OpenTSDB
- Ganglia
- TempoDB
- CollectD
- Datadog
- Folsom
- JMXTrans
- Pencil
- Rocksteady
- Boundary
- Circonus
- GDash

Probably the best bet is to head over to [the monitoringsucks tool repo on github](https://github.com/monitoringsucks/tool-repos). I can't do the awesomeness of what people are doing justice here.

and so many more.