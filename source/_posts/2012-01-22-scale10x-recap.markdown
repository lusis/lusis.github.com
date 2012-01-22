---
layout: post
title: "Scale10x Recap"
date: 2012-01-22 07:02
comments: true
categories: ["devops", "scale10x", "community"]
---

This past week I had the awesome pleasure of participating in my first [SoCal Linux Expo](http://www.socallinuxexpo.org/). As I later discovered, this was the 10th installment of this awesome event (hence the 10x).

# The email and enStratus
I got an email from [Ilan Rabinovitch](https://twitter.com/irabinovitch) just as things were going down with me headed to [enStratus](http://enstratus.com). Since the event was going to be right around the time I started, I pretty much put it out of my mind.
Then I realized that [my boss](https://twitter.com/botchagalupe) was going to be attending. I figured it wouldn't hurt to ask and it was decided that I would shadow John on his trip to enStratus HQ for my manditory cultural immersion (translation: does the fat redneck own a winter coat?) and on to SCaLE. While I didn't make it to Minnesota (diverted to San Jose on business), I did still make it to the conference.

I had an awesome time in San Francisco and San Jose. I got to meet a great bunch of folks and geek out hardcore.

# Monitoring sucks
Ilan asked me if I would be willing to be on a panel about the whole [#monitoringsucks](https://github.com/monitoringsucks) thing. We were able to score a great panel of folks:

- Simon Jakesch from Zenoss
- James Litton from PagerDuty
- Jody Mulkey from Shopzilla

The event was awesome. I did some minor introductions, got the ball rolling with some questions and then we let the audience take it from there.
The participation was AWESOME. We had great questions from the audience and the feedback I got AFTER the fact was mindblowing. One particular post-panel question is worth a blog post in its own right.

One thing that really stood out was this: People just don't know where to start. The landscape is pretty "cluttered". [Chris Webber](https://twitter.com/cwebber) brought up a very salient point that I sometimes forget; When we talk about "monitoring", we're really talking about multiple things - collection, alerting, visualization, trending and multitudes of other aspects.

I got asked several times in the hallway - "What should I use?" or "What do you think about <foo>?". My first response was always "What are you using now?".

I like to think I'm pretty pragmatic. I love the new shiny. I love pretty graphs. I'm a technologist. However, I know when to be realistic. My thought process goes something like this:

## Do you have something in place now?

### Yes
Why are you looking to switch? Is it unreliable? Is it painful to configure? Basically, if it's getting the job done and has relatively minor overhead there's no reason to switch.
The pain points for me with monitoring solutions usually come much later. It doesn't scale or scaling it is difficult. It doesn't provide the visibility I need. It's unreliable (usually do to scaling problems).
Until then, use what you've got and be guard for early signs of problems like check latency going up or missed notifications.

If you have a configuration management solution in place, it probably has native support for configuring Nagios. When you add a new host to your environment, you only need to tell your CM tool to run on your monitoring server. If you've done any sort of logical grouping, you'll have the right things monitored quickly.

### No
If you don't have ANYTHING in place, you need to cover two bases pretty quick:

- Outside-In Checks: is my site up and responding timely?
- Stupid stuff: Are my disks filling up? Is my database slave behind?

For outside in checks, use something quick and easy like Pingdom. For the inside checks, don't underestimate the power of a cron job. If you want something a bit more packaged, look at [monit](http://mmonit.com). It's dead simple and can get you to a safe place.

## A note on visibility
Monitoring tools are great but many times they fall down when you need to diagnose a problem ex post facto. If you went the simple route, you probably don't have any real trending data. This is where many complaints start to come from folks. You end up monitoring the same thing twice - once for alerting systems like Nagios and another time for your visualization, trending and other engines. When you reach this point, start looking at things like Sensu or all-in-one solutions that, while cumbersome and imprecise use the same collected data - Zenoss, Zabbix, Icinga (originally a fork of Nagios).

The event was recorded (both audio and video) but I have no timeframe on when it's going to be available but I'll let you know as soon as it's up.

# The rest of the conference
The rest of the conference was epic as well. Being that this was my first time, I didn't know what to expect. The thing that most stood out was the number of children. This was probably the most family friendly conference I've ever been to. Encouraging stuff. Plenty of events and in fact an entire track dedicated to children.

I didn't get to attend as many talks as I wanted to. While the facility was really nice, the building is like a faraday cage. My phone spent what little battery life it had just trying to get a signal. I spent quite a bit of time running back to my room to charge up. [Chris Webber](https://twitter.com/cwebber) totally got me hooked on portable chargers.

# Juju talk
_disclaimer: I'm fully aware that Juju is undergoing heavy active development and is a very young project_

One of the talks I attended was on [Juju](http://juju.ubuntu.com). I was probably a bit harsh on Juju when it was first announced. The original name was much better and the whole witch doctor thing just doesn't sit well with me.

I also hate the tag line - "DevOps distilled". It's marketing pure and simple. I have very little tolerance for things that bill themselves as a "devops tool" or "for devops".

But more than the name, something about Juju didn't feel right. After the talk, something still doesn't feel right. While I don't like pooping all over someone else's hard work so writing this part is tough.

## Where does it fit?
Right now, I don't think Juju even knows where it fits. It's got some great ideas and on any other day, I'd be all over it. The problem is that Juju tries to do too much in some areas and not enough in others.

Parts of Juju are EXACTLY what I see as my primary use case for Noah. The service orchestration is great. The ideas are pretty solid. Juju even uses ZooKeeper under the hood.

## Services not servers
Everyone knows that I preach the mantra of "services matter. hosts don't"

The problem is that in an attempt to be the Nagios (unlimited flexibility) of configuration management, it can't actually do enough in that area. Because it only concerns itself with services (and the configuration of them), it doesn't do enough to manage the host. Just because the end state is "I'm serving a web page" doesn't mean you should ignore the host its running on. Since Juju isn't designed to deal with that (and actually LACKS any primitives to do it), you're left with needing to manage a system in multiple places - once with your CM tool and then again with the charms.

Someone said it best when he described Juju as "apt for services". It's quite evident that the same broken mentality that apt takes to managing packages is applied to Juju as well. Charms have upgrade and downgrade steps. They're just as complicated too. Not only is there no standard (since charms can be written in any language) it's actually detrimental. The reason for a common DSL or language like the ones exposed by CM tools is not some academic mental masturbation. It's repeatability and efficiency. I can go into a puppet shop and look at a module and know what it does. I can look at most chef recipes (outside of ones that might use a custom LWRP) and know what's going on.

In the Juju world, a single charm could be written in one spot in Python and another spot in Bash. It pushes too much responsibility to the end user NOT to mess something up. I dare say that idemopotence doesn't even exist in Juju.

## A fair shake
Again, I'm going to do some more playing around with Juju. I think it can meet a critical need for folks but I think they need to revisit what problem they're trying to accomplish. I appreciate the work they've done and I'm totally excited that orchestration is getting the proper attention. The presenters were fantastic.

# Other stuff
I attended a really good talk about the history of Openstack and where it's going. It was great. As someone who is working with openstack professionally now (and had just dealt with some of its warts not 3 days before hand), I found it very valuable. Also congrats to the speaker, [Jesse Andrews](https://twitter.com/anotherjesse) on the birth of his first child!

I managed to make it to Brendan Gregg's talk as well. If you ever have the opportunity to hear him speak, you should take it. While I'm not a SmartOS user, the talk was really not about that. I walked out with some amazing insight on how smart people troubleshoot performance problems. Very well done.

# The hallway track
Of course the real value in any conference is the hallway track. The chance to interact with your peers. I met so many smart people (some twice because I suck at remembering faces at first - sorry!). Chatting with folks like C. Flores, Jason Cook, Sean O'Meara, Chris Webber, Dave Rawks, Matt Ray, Matt Silvey and so many others that I can't keep straight in my head. Everyone was awesome and I hope that you were able to get as much out of me as I got out of you.

Thanks again to Ilan for the invitation and for running such an amazing conference.

Also, little known made-up fact: Lusis is Tagalog for "He who eats with both hands".....
