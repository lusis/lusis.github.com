---
layout: post
title: "What production means"
date: 2012-07-09 21:31
comments: true
categories: 
---

This post is something that's been brewing for a while. While it may sound targeted in tone, it's more general than that. Let's just call it an open letter to family, friends and coworkers around the world.
<!-- more -->

One thing that I have the hardest time communicating to friends and family who aren't in the IT industry is the concept of "production" and what it means to be on-call. Even coworkers have a hard time understanding what it means.

The topic recently came up again and the confusion bothered me so much that I resolved to write this blog post as soon as humanly possible.

# A few clarifications
I want to clarify a few very important things:

- I'm not whining about what I do
- I love what I do
- I'm not burned out
- I'm not being self-important
- I've always had a hard time 'ranking' problems. EVERYTHING is important to me.
- I'm not really interested in critiques of what SHOULD have been done. Riak wasn't around, for instance, when I was managing the financial stuff for instance.
- Yes, rotations are important but not always viable. Luckily we have a solid rotation at enStratus.

# What production means to me (and why)
Production environments take many forms. It's even harder to define. For me, production has always meant "any system, service or component that the business requires to do business".
I've worked in several different companies over the last 17 years. In some cases, production was an ERP system or a file server. In other cases, production was a web presence. What's interesting each of these is that in some cases, production had a time associated with it.

Let's take a few of these and compare them:

## The retail financial company
Long ago I worked for a company that did payday and title loans. We operated over 600+ retail locations from east to west coast. The primary application used by these outlets was a web-based loan management application (websphere + db2). Stores were located across the country and store hours were from 9AM to 6PM (IIRC). From this you might think "production was the web application and it needed to be online from 9AM EST to 10PM EST". You would be correct at the highest level.

However employees started the day at 8AM (lining up customers to call and what not) and left at 7PM (closing out books for the day). After the last store went "offline", we began various nightly batch jobs. Being that this was financial in nature, batch jobs were the norm. We also had backups that had to run as we rebuilt our QA database nightly from sanitized production database backups. If the batch jobs didn't finish in time, we actually had to delay the start of the day for the stores. Our datawarehouse was also loaded from a secondary copy of the database restored from backup. If I recall the main reason for this was that our nightly window was SO crunched for time that we couldn't even load the warehouse from the main database because we had to start various batch jobs as soon as backup was done.

But that's just the main system. We had ancillary services as well. None of the retail outlets had access to the internet except through a squid proxy. There were print servers that did server-side check printing. In the backoffice, we had collections and other things that depending on the reports that came out of the data warehouse. We had nightly backups of the LAN stuff. DNS servers that the stores had to use. VPN concentrators. ALL of this had the same SLA as production.

All told, I recall the final number for any business hours outage as costing us something like $100k for 15 minutes of downtime.

## The Learning Management System
This system was used by a charter school system in the state of Ohio. It was an online classroom system that provided education for at-risk students. This WAS the school for these kids. Obviously it had normal school hours but since the students had no physical textbooks of any kind, the system HAD to be online for something as simple as homework. As with the previous setup, there were all sorts of ancillary services that we had to have available. All of the static content was shared across all of the tomcat servers via a SAN (OCFS2 - I have scars). It was backed by MySQL. We still had to do backups. We had to maintain connectivity. Everything we had was 'production'. Since we had developers in other countries on different hours, we burned what money we had when the development environment or our SVN repo was offline. That was production too.

## Web applications in general
To those in the industry I'm not telling you anything new. But to those not in the know, the internet doesn't have office hours. Yes, you can gauge where your largest userbase is but take a system like enStratus.

It manages cloud resources for people all across the world. For many of these people, the only access they have to their cloud account is VIA enStratus. Take AWS for instance. enStratus is responsible for detecting outages and replacing components in the infrastructure for these companies or autoscaling to meet some demand. If enStratus is offline, these actions are NOT being taken on behalf of the user. The biggest fear for me is that enStratus is offline when AWS is having an outage. Some customers are paying us for this use case alone. Mind you in the last few outages, not even enStratus could fix the problem because of control plane issues. One thing enStratus can do is scale across multiple clouds so even AWS control plane issues are no excuse.

enStratus production itself is a pretty complex beast. The stack in general is designed to be fairly "SOA". We use RabbitMQ pretty heavily for workers. However we've had some issues in the recent past where our workers were getting OutOfMemory exceptions. We run multiple workers (obviously) but in this case an OOM on one worker would eventually translate into OOMs across all the workers. When all the workers OOMd, they would stop processing messages from the queue. When that happened, RabbitMQ could eventually tank from units of work waiting to be picked up. We never had this happen, mind you but that was the end game.

This meant we had to be diligent on these OOMs. All the time. 24x7x365.

Luckily this problem was fairly short-lived but until the bug was identified and fixed (it happened to be related to an edge case with S3 bucket sizes), we had to be on guard for these OOM exceptions.

# What does it mean?
The thing that I want to get across is that "production" is DIRECTLY related to the bottom line of the business. If "production" is offline, customers can't use the system. Customers are unhappy. If customers are unhappy they eventually go elsewhere. If customers go elsewhere the company loses money. If the company loses money eventually the company lays people off. This isn't rocket science. We talk about complex systems and cascading failures. This is a cascading failure that means someone doesn't have a job at Christmas.

Yes, I take it that seriously. When I talk about production, that's what I mean.

# On-call
Now that you know what production means and what impact it has, I shouldn't need to say much about being "on-call". Yes, I'm on-call now and then. Yes, just like a doctor. Sometimes, depending on the company, I've been the only person on-call. Ever. No rotation. No help. Just me. The one person keeping shit running all the time so that customers (internal or external) aren't impacted by the slightest glitch in the system. Yes we should build more resilient systems and we strive to do that. However, tech debt is a thing. It's not always immediately an option.

So when I'm on-call it means I'm the person responsible for production as defined above and all the baggage that comes with it.


Someone once said to me "Most of us work nights and weekends, although we try to balance it when we can."

While I appreciate the sentiment, I don't just work nights and weekends, I work ALL the time. I have to be able to respond at a moments notice to a production issue when I'm on-call. Think about that for a moment. I have to essentially be 15 minutes from a working internet connection and may need to sit in front of a computer for an unspecified amount of time until the issue is resolved. I can't just go to bed or decide that I've worked enough for the day.

# The joys of working remote
I've been pretty fortunate in the past several years to be in a situation where I can support production from pretty much anywhere. I need at most a 3G connection and my laptop. I can VPN into production and fix most any problem. Yes, I always bring my laptop on "vacation". If I don't have a decent signal, then there's a chance I'll have to drive to the McDonald's 15 minutes away to use the wifi. I try not to be on-call when I'm on vacation but sometimes it's not always an option.

I just got back from visiting my in-laws Up North in Michigan. The only time I was able to get a single bar of 3G was late at night when enough subscribers stopped using the cell towers. Up until a year or so ago, there was no option for any sort of broadband internet in the area.

I've finally gotten my in-laws to understand much of what I've written here. They'll be wiring the cottage for cable internet so that I can take the family up for longer periods of time. Yes, I'll be "working" on vacation but I'd rather work a bit on vacation and stay longer than have to cut the trip short just so I can get back.

# Poor poor me
As I said at the start, please don't misinterpret what I'm saying as whining or some sort of god complex. This is the field I've chosen. I love what I do and I take that responsibility very seriously. As we start to build more resilient systems and accept that failure WILL happen and design for it, things will only get better. I still remember the first time I was able to replace a problematic system from scratch in 20 minutes via configuration management and call it a night. 

As enStratus migrates its backend to Riak DS and builds out secondary and tertiary datacenters, I look forward to losing a data node not being the end of the world. DevOps über alles and all that.

So dear friends and family, the next time someone tells you they're on-call for production....cut 'em some slack.