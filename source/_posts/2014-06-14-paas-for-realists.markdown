---
layout: post
title: "PaaS for Realists"
date: 2014-06-14 23:12
comments: true
categories: 
---

I realize I was pretty down on PaaS the past couple of days. Lest I send the wrong message, I figure a clarification is in order
<!-- more -->

<div class="docs-note">I've posted an update to this blog post with clarifications from folks <a href="http://blog.lusis.org/blog/2014/06/22/feedback-on-paas-realism/">here</a>. You should read it after this one</div>

Before we start, we should define some things. It's always important to be on the same page:

- PaaS: Platform as a Service (no distinction between public or private). When talking about public, it's usually Heroku.
- Private PaaS: A PaaS run "in-house". I'm using "in-house" loosely here. You could be running this on top of AWS for all I care. You're running it yourself. There are a lot of players in this space but the biggest name is CloudFoundry. There's also OpenShift and then the plethora of docker-based ones like Deis and Flynn.
- Affinity: Definable placement policies for where applications run. I use this liberally to refer to both affinity and anti-affinity. Basically "I want this to run next to this" vs. "I don't want this to run next to this"
- Production: Business critical functions that warrant "waking someone up at 2AM"
- container: linux containers. Nothing else.
- docker: a specific container packaging format and ecosystem

I also want to be clear that I *ONLY* care about production workloads. The reason I defined production the way I did is because only the end user can define the business criticality of a service or system function. If you consider an idle bench of engineers unable to work because your build farm is down a bad thing, then your build farm is "production".

I also want to point out that I inherently belive that a Private PaaS is probably a really good thing for your business. My argument is largely that most enterprises are not ready for it and are not willing to live with the shift it will require.

# Benefits of a PaaS
Let's go with the good news first. A PaaS (and more importantly a private PaaS) has a lot of benefits.

  - It can simplify deployment models.
  - It can unify the workflow of development and production deployments.
  - It can codify a framework by which you develop your applications
  - Generally speaking, you also get a more consistent operational surface for your applications
  - Can create a culture and framework for self-service

## Simplified deployment and unified workflow
Most private PaaS solutions tend to follow the Heroku model of a PaaS. In the Heroku model, You follow a normal development workflow using your VCS as a model for deployment:

  - Develop code
  - Test code
  - Push code to Heroku remote for testing

That's the sum of the deployment. Your deployment to production is no different.

## Codified framework
With Heroku, the "moving bits" of your stack are hidden from you. You don't stand up a database in the traditional sense. You tell Heroku to wire up the database to your application as an add-on. Heroku exposes this add-on to your stack via environment variables. You reference those environment variables in your application code instead of hard-coded settings in property or yaml files. Both CloudFoundry and OpenShift follow the same model:

  - Create a service
  - Bind the service
  - Update the application to use the service

This is really awesome from a development perspective. You simply define the building blocks of your application, tell the platform to expose them to your application and off you go.

## Operational Surface
From an operations perspective, a private PaaS can create a consistent operational surface area. You spend less time worrying about individual operating systems. Your "host" nodes are largely a uniform target. Most of the private PaaS products ship with most common services prebuilt and ready to wire up to applications.

I would even argue that a private PaaS even simplifies the security model a bit as the concept of users are less relevant and much of the PaaS tooling is, by nature, provides proxy access to the things the developers need. Both of the dominant private PaaS solutions leverage kernel cgroups for resource management. Cloudfoundry uses LXC for isolation while OpenShift uses SELinux and MCS currently. I believe, however, that OpenShift is migrating to LXC as well.

## Self-service culture
Once your private PaaS is up and running, your development team is unleashed to deploy whatever and whenever they want. They aren't waiting around for a full-fledged OS to be provisioned that needs further configuration just to be servicable. Developers are free to experiment with arbitrary components (provided they exist in the PaaS service catalog).

# Rainbows and Unicorn Piss
However not everything is rosy and bright in the land of the private PaaS. There are downsides as well - some cultural and some technical

## Application Readiness
Unless your private PaaS is a bespoke solution, you WILL have to change your application model. You cannot simply forklift an application into a PaaS. Your application must be designed not only to work in a PaaS environment but also to work in a specific PaaS.

Most traditional "enterprise" applications are not ready for a PaaS solution. Many will have to be significantly rewritten. The most common model for this is the [12 Factor Application](http://12factor.net/).

I should state up front that I disagree with quite a few bits of the 12 Factor model. It's important to remember that, imho, the 12 Factor model was designed as a business strategy for Heroku. The steps follow exactly what makes an app work best on Heroku, not what is best for an application.

Regardless, as the private PaaS solutions are largely modeled on Heroku you might as well state that 12 Factor is the way you'll need to design your application.

## Magical autoscaling
As I said in my previous post, this really doesn't exist. Your application has to be DESIGNED to scale this way. As Adrian Cockcroft pointed out in the comments to my previous post Netflix "overallocated" on the dependency side up front to minimize the need and impact of things like rebalancing data and load balancer scaling. It's also worth noting that Netflix did NOT use a PaaS (though arguably the model for how they used AWS was PaaS-ish).

Most "enterprise" applications I've dealt with never scaled cleanly. They needed things like sticky sessions and made assumptions about data access paths. Quite frankly they also were not designed for this level of deployment volatility. I would go even further and say that if you have a release cycle measured in months, don't bother.

## Magical Autorecover
Just like autoscaling, this is also not what you think it is. Unless your application maintains exactly ZERO state, then you will never see this benefit. Do you write files to critical files to disk in your application? Yep those are gone when you "magically autorecover". The autorecovery that was promised you? It redeploys your application. Your state is lost and no you don't have NFS or shared storage or anything to fall back to. Get used to shoving your blobs in your database. Oh but what if your database fails?

This is where it gets interesting. I'm still sussing out the recovery models for the two primary players in this space but most likely you will LOSE that data and have to restore from a backup. I'm sure someone will call me on this and I'm willing to listen but I do know for a fact that the autofailover model of things like your MySQL instance depend on migratable or shared storage (at least from my reading of the docs).

This all of course leads me to the next part

# Technical Requirements
I alluded to this earlier but there are technical requirements that most companies are simply not ready for.

## Distributed Systems
All applications are inherently distributed systems even if you don't want to admit it. However a PaaS is more so than most shops are ready for. Let's run down the components for the current version of [CloudFoundry](http://docs.cloudfoundry.org/concepts/architecture/). I count 11 distinct components. If we move over to [OpenShift](http://openshift.github.io/documentation/oo_system_architecture_guide.html) I count 4 components.

Both of these applications use a service router, a message bus, a data store and `n` number of actual nodes running the deployed applications. In both cases, the documentation for these components requires you to already know how to scale and maintain these components. There are any number of places where these stacks can fall apart and break and you will need to be an expert in all of them.

Also one of the more hilarious bits I've found is the situation with DNS. I can't count the number of shops where DNS changes where things like wildcard DNS were verboten. Good luck with the PaaS dyndns model!

## Operational Immaturity
To be clear while I feel that most organizations aren't ready for the operational challenges of maintaining a PaaS, the job is made harder by the PaaS software. In both cases, the operational maturity of the products themselves simply isn't there.

Look at the "operators" documentation [here](http://docs.gopivotal.com/pivotalcf/concepts/high-availability.html) for CloudFoundry HA. I can sum it up for you pretty easily:

{% blockquote %}
GL;HF
{% endblockquote %}

Basically they punt everything over to you as if to say "Fuck if we know. Use that thing you sysadmin types use to make shit redundant.

And lest you think OpenShift is any better, OpenShift uses MongoDB with this nice bit of information:

{% blockquote %}
"All persistent state is kept in a fast and reliable MongoDB cluster."
{% endblockquote %}

What I'm about to say I stand behind 100%. Any company that tells you that MongoDB is "reliable" is basically saying:

  - We have no idea what we're talking about
  - We know f-all about operations
  - We hate you

Any tool that uses MongoDB as its persistent datastore is a tool that is not worth even getting started with. You can call me out on this. You can tell me I have an irrational dislike of MongoDB. I don't care. Having wasted too much time fighting MongoDB in even the most trivial of production scenarios I refuse to ever run it again. My life is too short and my time too valuable.

Additionally I've found next to zero documentation on how a seasoned professional (say a MySQL expert) is expected to tune the provisioned MySQL services. The best I can gather is that you are largely stuck with what the PaaS software ships in its service catalog. In the case of OpenShift you're generally stuck with whatever ships with RHEL.

Another sign of operational immaturity I noticed in OpenShift is that for pushing a new catalog item you actually have to RESTART a service before it's available.

## Disaster Recovery
After going over all the documentation for both tools and even throwing out some questions on twitter, disaster recovery in both tools basically boils down to another round of "good luck; have fun".

Let's assume your PaaS installation is a roaring success. You've got every developer in your org pushing random applications out to production. Self-service is the way of life. We've got databases flying all over the place.

How do you back them all up? Well this is a PaaS, Bob. It's all about self-service. The developers should be backing them up.

WAT.

Again based on the research I've done (which isn't 1000% exhaustive to be fair), I found zero documentation about how the administrator of the PaaS would back up all the data locked away in that PaaS from a unified central place. If your solution is to tell me that Susan's laptop is where the backups of our production database lives, I'm going to laugh at you.

## Affinity
Affinity issues make the DR scenario even MORE scary. I have no way of saying "don't run the MySQL database on the same node as my application". This makes the risk surface area even more large. Combine that with the fact that a single host could be running multiple business critical applications. I realize that these tools have algos that are supposed to handle this for you but I've not seen any sort of policy enforcement mechanism for that in the documentation.

# So what's the answer?
I don't think ANY of the current private PaaS solutions are a fit right now. OpenShift is, imho, built on unsound ground. CloudFoundry in its current Ruby form is a mess of moving parts. In fairness CloudFoundry is going through a rewrite with some firm leadership behind it that I have quite a bit of faith in when it comes to operational concerns.

Additionally both tools are embracing containers and docker packaging to increase security but none of the tools offer, as far as I can tell, anything resembling a hybrid model. I don't trust docker storage containers yet personally.

And I want to be clear. I'm not trying to be a BOFH here with all my talk of "placement policy" and "disaster recovery". I fully embrace the idea of a private PaaS. I simply don't embrace it in any of the current ecosystem. Even a modicum of due diligence should rule them both out until they address what are basic business sanity checks. These platforms require real operations to run and maintain. If you're still throwing things over the wall to your operations team to deploy into your PaaS then you really haven't gained anything. Unless your engineering organization is willing to step up to the shared responsibility inherent in a PaaS, then you definitely aren't ready. Until then, your time and money is better spent optimizing and standardzing your development workflow and operational tooling to build your own psuedo-PaaS.

