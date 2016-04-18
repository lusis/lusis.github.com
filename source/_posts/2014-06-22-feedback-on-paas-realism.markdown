---
layout: post
title: "Feedback on 'PaaS Realism' Post"
date: 2014-06-22 22:22
comments: true
categories: 
---
I've gotten quite a bit of constructive feedback on the PaaS realism post and I wanted to aggregate it here.
<!-- more -->

# OpenShift
I was humbled to see a very awesome post from [Luke Meyer who works on the OpenShift team](http://sosiouxme.wordpress.com/2014/06/19/response-to-paas-for-realists-re-openshift/).  I think if you are going to deploy OpenShift, his post is very important to read.

I wanted to respond to a few points specifically he made (and again say thank you for taking the time to respond/educate me)

## State and Sticky Sessions
Luke is absoltely correct that statefulness and stateful requirements are not a limitation of a PaaS. I'll address this a bit at the bottom a bit more clearly but I would say the issue is **AMPLIFIED** by a PaaS.

## Databases on OpenShift
Luke points out that database cartridges on OpenShift are not redundant. I was informed that there [*IS* an HA cartridge of PGSQL available](http://crunchydatasolutions.com/crunchy-latest/crunchy-announces-high-availability-postgresql-for-openshift-enterprise/) though it appears to be a commercial offering. The marketing fluff doesn't go into any useful information to clarify how that works (and I've not yet dug in to see if there's freely available tech information).

And frankly, similarly to the state issue, database usage is pretty app specific. One thing I take issue with is that if you do end up using an external DB, have you really bought yourself that much? Yes your application has a common 'environment' to it now but there's still this non-PaaS component that someone has to use. Unless you're setting up an external development database you now have a tangible difference between production and development that you have to account for. Again, this is not **SPECIFIC** to a PaaS but it is a variable that I think gets glossed over.

## HA Storage
I think this came across incorrectly in my original post. If your application requires persistant storage, you've already hit the first strike in my "Is your app ready" list. If you are using a platform as a service and you require some form of persistent storage, you should go back to the drawing board.

## DynDNS
I can't go into details but I can tell you this is definitely an issue. A predictable enterprise IT security bullshit issue but an issue none the less. As in a complete non-starter issue.

## MongoDB
I'm going to hold off on this until the end because this ties into an overarching concern/commentary I have.

I think everyone should read the rest of Luke's post that I'm not responding to specifically. It's a very well thought out post and he makes very excellent points. In fact at the end he does ask a very legitimate question:

{% blockquote %}
So if you think PaaS is right for you, also ask yourself: do you want to be in the building-a-PaaS business or the using-a-PaaS business?
{% endblockquote %}

# CloudFoundry
I didn't get much feedback on CF as I'd liked but in fairness I didn't give CF the same attention as I did OpenShift. I did, however, get quite a bit of good information from Twitter from both Dave McCrory and James Watters as well as someone I'm not comfortable naming considering it was not public communication.

Before I address the CF specifics, however, I think the most critical deciding factor in why *NOT* to choose CF right now is that it's undergoing a rewrite. I don't know what the upgrade path for that rewrite is. My guess is it's a parallel deploy and then migrate considering the scope of the change but someone PLEASE correct me if I'm wrong on that.

Based on what I've seen, the rewrite **ACTUALLY** sounds like it will make CF a solid choice. The problem here is that standing up a system like CF (or OpenShift or even OpenStack) are not things that should be taken lightly. You are going to be putting your PRODUCTION workloads on these systems. If you stand up CF now and then come back in three to six months telling everyone you have to transition to a new setup (regardless of the migration process), you risk burning a lot of personal capital.

Now on to the feedback

## CF doesn't use containers
I incorrectly stated that CF used LXC and according to both Dave and James, that was incorrect. I can't find the link that made me think that (poor form on my part). This is currently controlled by Warden (as I grok it). 

## CF and application session state
James pointed me to an interesting blog post about how [CF handles session state](http://blog.gopivotal.com/cloud-foundry-pivotal/products/scaling-real-time-apps-on-cloud-foundry-using-node-js-and-redis). One thing that's interesting about this post is that it honestly isn't even CF specific. However it still doesn't address my concern about databases on a PaaS.

So the demo application is using redis as a session store. Great! We're going to ignore the work that Kyle Kingsbury did on [Redis with Jepsen](http://aphyr.com/posts/283-call-me-maybe-redis) for a minute.

What this article **DOESN'T** cover is how might I deploy a redis "cluster" to CF:

{% blockquote %}
MemoryStore is simply a Javascript object – it will be in memory as long as the server is up. If the server goes down, all the session information of all users will be lost!

We need a place to store this outside of our server, but it should also be very fast to retrieve. That’s where Redis as a session store come in.
{% endblockquote %}

The post goes into terribly awesome detail about how to make your app handle the reconnection while conviently leaving out how one might deal with the loss of the redis instance itself. I mean let's be fair. This post basically just told you how to write reconnect logic. It gives you all the **APP** bits but nothing about the **INFRASTRUCTURE** bits. I'll address this at the end but it's just more of the kind of thing that is driving me crazy about the magical PaaS landscape.

## Go rewrite is about features not bugs
I'm going to disagree with James here. [This video](https://www.youtube.com/watch?v=1OkmVTFhfLY) was recently posted from the CloudFoundry conference. While Onsi (who is now my new favorite speaker btw), doesn't call these things "bugs", I would consider the issues he brings up and the end results to be bugs. Again this is subjective but it does get to my primary area of concern addressed below.

## CF is good for enterprises
James pointed me to [this blog post](http://scn.sap.com/community/cloud/blog/2014/06/22/cloud-foundry-hybris-and-the-hana-cloud-platform-microservices-and-beyond) about SAP building their nexgen PaaS on CF. While this is interesting and exciting (especially coming from a legacy like SAP) it means very little to me.

# My perspective
I said this **SEVERAL** times and reiterated it on Twitter but I want to clarify it one more time:

My questions and concerns, while very much focused on the developer experience, are **MORE** focused on the **OPERATIONAL** experience of running any of these products. Yes, there may be "hundreds of thousands of production apps" running on CloudFoundry (to quote James) but I want to know what the **OPERATIONAL** burden is to running those.

These are the things I care about (outside of the developer experience):

- What are the failure points?
- What is the impact of each failure point?
- What are the SINGLE points of failure?
- What is my recovery pattern?
- What is my upgrade experience?
- What is the operational overhead in the applications running ON the product?
- What is my DR strategy?
- What is my HA strategy?

and this is just the SHORT list.

When Luke tells me the operations team running the hosted version of OpenShift says

{% blockquote %}
“We had a couple of outages early on that were traced back to mongo bugs but generally we don’t even think about it.  Mongo just keeps ticking and that’s fantastic.
{% endblockquote %}

That's great but doesn't mean **ANYTHING** to me. What matters to me is MY operational experience with MongoDB. Here I have one data point as a 'positive' and in my own experience and the documented experience of others AS well as the actual math done by [Kyle Kingsbury with Jepsen](http://aphyr.com/posts/284-call-me-maybe-mongodb), MongoDB is not suitable for any production workload I wish to manage.

Luke says "Was the use of MongoDB your only criticism here?" to which I would say "It's not the only concern but it is the single LARGEST concern I have with OpenShift and one that immediately rules it out for me".

Operations is hard to back with science in the same way that doing a Jepsen series is. In the end what we have to go in is best worded by my coworker Tim Freeman:

{% blockquote %}
I've run so many things like X that I can roughly tell what running X is going to be like...
{% endblockquote %}

If the message here isn't clear, the experience of running a platform by the authors of the platform means VERY little to me when it comes to deciding if *I* or my company should run it. There are plenty of tools I use daily that are SaaS that run on MongoDB (that I'm aware of and more that I'm not). There's a valid argument about "should I trust someone who would wilfully choose MongoDB" but that's not something I control. If the service does what I need and I haven't been directly affected by MongoDB's issues then I really don't have any room to complain. If *YOUR* ops team wants to run product X, that's great. My duty and responsibility is to my ops team, my developers and my business units.

Mark Imbriaco brought up a point that I do agree with (and explicitly state in my previous post) in response to me stating this concern:

{% blockquote %}
Still disagree, even with that focus. Supporting PaaS is much less complex than dozens of inconsistent app envs.
{% endblockquote %}

but there is, again, a very subtle distinction here. An unspoken word and a bit of a mismatch in what I'm talking about. Let me restate Mark's tweet with that word and clarify what I think he meant and what I definitely meant (Mark please please correct me if I'm wrong in your intention here):

{% blockquote %}
Still disagree, even with that focus. Supporting PaaS applications is much less complex than dozens of inconsistent app envs.
{% endblockquote %}

Mark is 1000% right. Supporting a PaaS-hosted application is infinitely less complex than multiple inconsistent ones.

Supporting a *PaaS* however, is not infinitely less complex. It is *MORE* complex. Mark did follow on with this:

{% blockquote %}
For smaller envs, your advice to rationalize deployment and runtime envs over PaaS is good advice.
{% endblockquote %}

Now in fairness (and I love Mark to death and respect him immensely) that's a bit of arrogant way to word it. EVERY environment, regardless of size, should deal with foundational issues such as deployment and runtime before adding another layer of complexity. Another point to keep in mind is that Mark has his background with Heroku. That's not a positive or a negative it's just a very important data point. Mark knows how to run a PaaS. He's run arguably the largest public PaaS out there and dealt with all of its failure points.

Also no one has yet to address my concerns about the centralized management of the PaaS itself or the data *IN* that PaaS. This is something that Heroku is actually a very POOR model for as Heroku does not concern itself with those things (nor does it need to). While Heroku does [offer and document how an individual might handle an HA or DR scenario](https://devcenter.heroku.com/articles/heroku-postgres-ha) <del>note that Heroku doesn't centrally backup every single database hosted on its platform.</del> *(It was pointed out to me that I misread the Heroku docs. All PGSQL dbs are backed up but there ARE limits related to the db that vary by tier)*. Also worth noting that Heroku's own offering is PGSQL and that the addon providers are responsible that comparible service. When you are running your own PaaS you are not only Heroku but also that addon provider.

So to go back to the quote from Luke:

{% blockquote %}
So if you think PaaS is right for you, also ask yourself: do you want to be in the building-a-PaaS business, or the using-a-PaaS business?
{% endblockquote %}

As I said, this is a good question but it's indicative of the problems with all the solutions to date from the Docker based ones to CloudFoundry to OpenShift. It even extends to the IaaS offerings like OpenStack and CloudStack. Every single time the discussion is framed as "you should use this thing. It makes [deploying apps|managing apps|creating infrastructure|autoscaling|blah blah blah] so much easier and better.

And guess what? They're all right. These tools *DO* make these things easier and in many cases better but they ALL (each and every one of them) ignore (and I would argue INTENTIONALLY) the question staring them in the face.

Who the fuck is going to *RUN* this shit?
