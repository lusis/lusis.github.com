---
layout: post
title: "There's no konami code for operations"
date: 2014-06-13 23:45
comments: true
categories: 
---

up up down down left right left right b a select start
<!-- more -->

I went on a bit of a rip today about all sorts of technology. I figured I should at least clarify some of it in long form.

## Vmotion/live migration technologies
Vmotion is a scam. I have frequently said that only trivial workloads are safe for vmotion. Here's the reasoning:

- live migration realistically requires the workload on the vm being migrated to be quiesced.
- any workload that can be quiesced with no impact is most likely a trivial workload
- trivial workloads don't NEED the benefit of live migration
- to that end, you're basically paying a lot of money to allow a system to copy its files from one node to another

Let's also not forget that live migration claims to have accomplished a lot of things such as time travel. You may also know live motion technology by its other names like:

- Dude where's my clock?
- I paused your workload for you but you didn't notic...hey why did I just get a network partition in my cluster?

But hey it demos really well when you can keep watching that streaming video while your vm is moved from host to another.

## Autoscaling
Autoscaling is a myth. My reasoning behind this has similarities with vmotion/live migration.
Again we have a set of things we need to clarify:

- horizontal vs vertical autoscaling
- triviality of the workload being autoscaled
- workload support for autoscaling
- scale up vs scale down

I am not concerns with trivial workloads. Trivial workloads are...well...trivial. The largely cached static marketing website takes no effort whatsoever to scale. Oh look I just brought up a new server with the same static content! Instant capacity!

Let's take a standard architecture here:

- caching (memcache)
- frontend 
- load balancer
- database (this applies to traditional RDBMS as well as NoSQL)

When I "autoscale" my caching layer, I now have to concern myself with the following things:

- hashing algos
- cache miss increase

So sure, feel free to autoscale that group of memcache servers but your performance just went to through the floor. Now you've had a downstream affect on your database as you're having to go to origin due to cache misses. Oh and by the way not all servers talking to the caching tier saw the same topology so now you've got possibly incorrect data you're serving from the cache

When I autoscale my frontend, I've now add `n` number of connections to the database. How's that network looking? Oh wait did you just autoscale to the point of starving the database of resources? Have you possible shot yourself in the foot because now the stuff that was working before is getting rejected because of connection counts?

Autoscaling load balancers is also a problem as you now have the issue of topology mismatch of your backends as well as dealing with session injection that was PREVIOUSLY handled by only one LB.

Finally let's get to autoscaling our database. Vertical or horizontal, relational or "NoSQL" it doesn't matter. If you vertically scale your DB, do you have to restart the process with larger memory allocations? What about rebalancing of data when you scale horizontally?

And we've not even gotten to if your application is actually ABLE to be autoscaled (are you entirely stateless? 12 factor friendly?).

Combining these things along with unmentioned downstream impacts and transitive dependencies, means that in most cases when you need to autoscale you won't be able to respond to the workload for some time. It's possible that AFTER that time has passed, the workload may be gone.

And let's not even talk about trying to unwind all that madness via scale down.

# private PaaS
This is almost the most egregious of them all. It ranks right up there with "private cloud" in the bullshit-o-meter department. Resources are not infinite.
I have a bit of a guidepost I use when thinking about "new" functionality in applications.

- what functionality am I adding?
- is it my core competency
- what does the landscape of existing ecosystem look like for that functionality
- how successful is that landscape for that functionality

In most cases, the landscape is either saturated with business whose core focus and expertise is on that concept/functionality. In worst cases the majority of the businesses in that space are failing. Think long and hard about if you have the expertise to do this thing.

PaaS and IaaS are in the same boat. I'm a bit more harsh on the IaaS front as I truly believe that if an operations team had been able to deliver on the promise of virtualization but couldn't (for any reason) then sticking ANOTHER layer on top isn't going to magically make it work. The platform still has real hardware under the covers that has the same limitations it had before - bandwidth capcity, io, patching of hypervisors. This stuff doesn't just disappear. In many cases you can actually hit a wall VERY early on for capacity issues. 

[This slide deck about Cloudstack Hypervisor choices](http://www.slideshare.net/TimMackey/hypervisor-31754727) is an amazing read for understanding limitations. Some of these are actually imposed by the hypervisor:

- Max VLANs
- Max Storage
- Max vms per hypervisor
- Max hypervisors per pool


There's no cheat code for this shit, folks. Very few PaaS and IaaS products tackle operational issues at all. Time to first success is important but not if it comes at the expense of cost to operate over time. Yes, I can sudocurlbash your product on to my system but that's trivial. How do I deal with:

- Backups
- Affinity/Anti-affinity issues
- Upgrades
- Supportability

IaaS actualy isn't as bad as a PaaS in some of those but they both have issues. PaaS is mainly worse because you have adopt into an ecosystem and philosophy (not that 12 factor isn't entire good but it's a start) if you want to have any real success.

You will *NOT* forklift a workload into one of these models and be successful in the long term. In the short term you will simply have given someone else a lot of your money.

I drove a forklift for two years. I know forklifts.

# End rant
I'm not saying that people can't be successful at these things. Clearly they are to some degree. But that's only the public face. How much shit did they have to wade through to make this work? Where are the bits of baling wire, duct tape and a healthy belief in a higher power that keep it from just failling over the edge of the abyss?

Anyway that's just a short list of things from a very tired and worn out person with less hair than he started the day with. I know I shouldn't get mad about this stuff but it's hard when the people trying to smokescreen you ARE you in a sense (professionally speaking). Frankly I'm just tired of people thinking that the operational aspects of this stuff are irrelevant because somebody promised them "autoscaling selfhealing magical rainbow-colored unicorn piss in a bottle" where they didn't have to interact with operations folks ever again. 
