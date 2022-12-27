+++
title = "Future of Noah"
date = "2013-01-20"
slug = "2013/01/20/future-of-noah"
Categories = []
+++

This is probably the most difficult blog post I've had to write. What's worse is I've been sitting on it for months.
<!-- more -->

When I started Noah a few years ago, I had a head full of steam. I had some grand ideas but was trying to keep things realistic. I simply wanted a simple REST-ish interface for stashing nuggets of information between systems and a flexible way to notify interested parties when that information changed.

It started as a [mindmap](https://raw.github.com/lusis/Noah/8a2e193c043ab30cce17d7ada25ef33b72baa73e/doc/noah-mindmap-original.png) laying in bed one night. It was my first serious project and I had no idea what I was getting in to. If you're curious, you can read quite a bit of my initial braindumps on the [wiki under 'General Thoughts'](https://github.com/lusis/Noah/wiki). I watched every day as more and more people started following the project.

It was a game changer for me in many ways. Working on Noah was fun and it was rewarding in more ways than one. But real life gets in the way sometimes.

# On stewardship
One of the things I've learned over the past few years is that for opensource to REALLY thrive, it can't be a one-person show. I've been involved with opensource for most of my 17+ year career. You think I would have learned that lesson before now.

Stewardship is a hard thing. Our arrogance and pride makes us want to keep things close to our chest.

- "I just want to get to a 1.0 release"
- "Things are too in flux right now. It wouldn't be fair to bring others in"
- "I don't quite trust anyone else with it yet"
- "Let me just get this ONE part of the API in place first.."

These are all things I said to myself.

What really changed my mind was a few things. Being involved in the Padrino project. Seeing the Fog community grow after Wesley started allowing committers. Seeing Jordan trust me enough to make me a logstash committer before his daughter was born. The biggest trigger was actually one of my own projects - the chef logstash cookbook.

Bryan Berry (FSM bless him) pestered the hell out of me about getting some changes merged in. He was making neccessary changes and fixes. He was evolving it to make it more flexible beyond my own use case. I don't recall if he asked to be a committer but I gave it to him. The pull request queue drained and he added more than I ever had time for. Not long after, I added Chris Lundquist. Those two have been running it since then really.

I think back to when I got added to the committers for Padrino. It was a rush. It was amazing and scary. Above all it was the encouragement I needed. How dare I deny someone else that same opportunity.

Making that first pull request is hard. To have it accepted is a feeling I'll keep with me for a long time. I can only hope that some project I create some day will give someone that same confidence and feeling.

# So what about Noah
Noah is in the same place Logstash was. I'm not using it and that's really hurting it more than anything. It's time to let someone who IS using it take control. I care too much about it to watch it die on the vine. I still believe in what it was designed to do and every single day I get emails asking me if it's still alive because it's a perfect fit for what someone needs. The same stuff is STILL coming up on various mailing lists and Noah is a perfect fit. There are companies actively using it even it the current unloved state. Those folks have a vested interest in it. 

When I added Chris and Bryan to the cookbook, I sent them an email with what my vision was for the cookbook. I can't find that email now but I recall only had two real requirements:

- Out of the box, it would work on a single system with no additional configuration (i.e. add the cookbook to a run_list and logstash would work automatically)
- A user never had to modify the cookbook to change anything related to roles (i.e. allow the attributes to drive search for discovering your indexer - hence all the role stuff in the attrs now)

I need to do the same thing for Noah and see where it leads.

# Dat list
This list isn't comprehensive but I think it hits the key points.

## Simple
Noah should be simple to interact with. It was born out of frustration with trying to interact with ZooKeeper. Nothing is more simple than being able to use `curl` IMHO. I can use Noah in shell scripts and I can use it in Java (we had a Spring Configurator at VA that talked to Noah. It was awesome). You should always be able to use `curl` to interact with Noah. I wish I could find it now but someone once brought up Noah on the ZK mailing list. This led to various rants about how it didn't do consensus and a bunch of other stuff that ZK did. One of the Yahoo guys (I wish I could remember who) said something in favor of Noah that stuck with me:

*Interfaces matter*

I know I'm on the right track here because Rackspace just built a product that provides an HTTP interface to ZK. Oh and it does callbacks.

## Friendly to both sysadmins and developers
Simplicity plays into this but I wanted Noah to be the tool that solved some friction between the people who write the code and the people who run the code. Configuration is all over the place in any modern stack. Configuration management has come into its own. People are using it but you still see disconnects. Where should this config be maintained? What's the best way to have puppet track changes to application configuration? I can't get my developers to update the ERB templates in the Chef cookbook. All of these things are where Noah is helpful.

I still stand by the statement that [not all configuration is equal](http://lusislog.blogspot.com/2011/03/ad-hoc-configuration-coordination-and.html). Volatility is a thing and it doesn't have to mean the end of all the effort in moving to a CM tool. I wanted to remove that friction point.

I was also immensely inspired by Kelsey Hightower here. I've told the story several times of how Kelsey got so frustrated that the developers wouldn't cooperate with us on Puppet and config files for our applications that he learned enough Java to write a library for looking up information in Cobbler. Cobbler has an XMLRPC api and that was simple enough that he could port his python skills to java and write the fucking library himself. I wanted Noah to be friendly enough that a sysadmin could do what Kelsey did.

## Watches and Callbacks
I've said this before but one of the most awesome things that ZK has is watches. They have pitfalls (reregister your watches after they fire for instance) but they're awesome. Noah's callback system is the thing that needs the most love (it works but the plugin API was never finalized). It's also one of the most powerful parts that meets the needs of folks that I see posting on various mailing lists.

The idea is simple. When something changes in Noah, you should be able to fire off a message however the end-user wants to get it. I think this is one of the reasons I love working on Logstash so much. Writing plugins is so simple and it's the gateway drug to anyone who wants to contribute to logstash.

# Things I don't care about
What don't I care about?

## Language
I don't care about the language it's written in. If someone wants to take it and convert it to Python or Erlang or Clojure, be my guest. I just want the ideas to live on somehwere. In fact, I've rewritten various parts of Noah over the last year privately. Not just experimenting with moving from EM to Celluloid but as a Cherry.py app, in Clojure and I even started an Erlang attempt (except that I know almost NO Erlang so it didn't get very far).

## Name
Honestly I don't even care about the name. Yeah it's witty and fits with the idea of ZooKeeper but I have no qualms about adding a link to your project from the Noah readme and recommending people use it instead.

## Paxos/ZAB
This was never a requirement for Noah. Noah was specifically designed for certain types of information. If you need that, use the right tool.

## Persistence
Let's be honest. From a simplicity standpoint, it doesn't get much simpler than Redis. It's one of the reasons we changed the default logstash tutorial to use Redis instead of RabbitMQ. I know Redis reinvents a lot of wheels that have already been solved but it, along with ElasticSearch, are one of the lowest friction bits of software I've dealt with in a long time. Not having external dependencies is a godsend for getting started.

However I've also got small experiments privately where I used ZMQ internally and sqlite. I've written a git-based persistence for it too.

Riak is also a great fit for Noah and takes care of the availability issue on the persistence side. More on Riak in a sec.

# So that's it
That's really all that matters. If you want to take ownership of the project, contact me. Let me know and we'll talk. Who knows. Maybe I'm overestimating the level of interest. Maybe ZK isn't as unapproachable to people anymore. The language bindings have certainly gotten much better. I just want the project to be useful to folks and I'm getting in the way of that.

# What are the other options?
I don't know of many other options out there. Doozer is picking up steam again as I understand it and it has a much smaller footprint than ZK does. There was a python project that did a subset of Noah but I can't find it now.

One thing that is worth considering is a project that I found earlier today - [zpax](https://github.com/cocagne/zpax). While this is just a framework experiment of sorts, it could inspire you to add your own frontend to it. The same author is also working on DTLS on top of ZMQ.

I've thought about ways I could actually do this with Logstash plugins. It's doable but not really feasible without making Logstash do something it isn't shaped for.

Another idea that I'm actually toying around with is simply using Riak plus a ZeroMQ post-commit hook so that plugins could be written in a simpler way. [Sean Cribbs already took the idea and made a POC 2 years ago](https://github.com/seancribbs/riak_zmq) based on a gist from Cody Soyland. You wouldn't have the same API up front as Noah but you could stub that out in some framework and also have it be the recipient of the ZMQ publishes.

Finally you could just use ZooKeeper. Yes it has MUCH greater overhead but you DO get a lot more bang for the buck. There really isn't anything in the opensource world right now that compares. It also provides additional features that I never really cared about or needed in Noah.

# Wrap up
I'm not done in this space. I don't know where I'm going next with it. Maybe I'll start from scratch with a much simpler API. Maybe I'll just run with the Riak idea.

I just want to give a shoutout to the countless people who helped me evangelize Noah over the last few years. It was recommended on mailing lists, twitter and many other places. It meant a lot to me and I only hope that someone will take up the mantle and make it something you would recommend again.

For those of you still using Noah, I hope we can find a home for it so that it can continue to provide value to you.

Thanks.

