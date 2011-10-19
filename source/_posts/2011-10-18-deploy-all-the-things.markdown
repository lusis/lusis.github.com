---
layout: post
title: "Deploy ALL the Things"
date: 2011-10-18 06:59
comments: true
categories: [Deploy, DevOps, Strategy, Tooling]
---

_This is part 2 in a post on deployment strategies. The previous post is located [here](http://blog.lusis.org/blog/2011/10/18/rollbacks-and-other-deployment-myths/)_

My previous post covered some of the annoying excuses and complaints that people like to use when discussing deployments. The big take away should have been the following:

- The risk associated with deploying new code is not in the deploy itself but everything you did up to that point.
- The way to make deploying new code less risky is to do it more often, not less.
- Create a culture and environment that enables and encourages small, frequent releases.
- Everything fails. Embrace failure.
- Make deploys trivial, automated and tolerant of failure.

I want to make one thing perfectly clear. I've said this several times before. You can get 90% of the way to a fully automated environment, never go that last 10% and still be better off than you were before. I understand that people have regulations, requirements and other things that prevent a fully automated system. You don't ever have to flip that switch but you should strive to get as close as possible.

# Understanding the role of operations
Operations is an interesting word. Outside of the field of IT it means something completely different than everywhere else in the business world. [According to Wikipedia](http://en.wikipedia.org/wiki/Business_operations):

> Business operations encompasses three fundamental management imperatives that collectively aim to maximize value harvested from business assets 
> 1. Generate recurring income
> 2. Increase the value of the business assets
> 3. Secure the income and value of the business


IT operations traditionally does nothing in that regard. Instead IT operations has become about cock blocking and being greybeareded gatekeepers who always say "No" regardless of the question. We shunt the responsibility off to the development staff and then, in some sick game of 'fuck you', we do all we can to prevent the code from going live. This is unsustainable; counter-productive; and in a random twist of fate, self destructive.

One thing I've always tried to get my operations and sysadmin peers to understand is that we are fundamentally a cost center. Unless we are in the business of managing systems for profit, we provide no direct benefit to the company. This is one of the reasons I'm so gung-ho on automation. [John Willis](https://twitter.com/botchagalupe) really resonated with me in the first Devops Cafe podcast when he talked about the 80/20 split. Traditionally operations staff spends 80% of its time dealing with bullshit fire-fighting muck and 20% actually providing value to the business. The idea that we can flip that and become contributing members of our respective companies is amazing.

Don't worry. I'll address development down below but I felt it was important to set my perspective down before going any further.

# Technical Debt and Risk Management
Glancing back to my list of take-aways from the last post, I make a pretty bold (to some people) statement. When I say that deploy risk is not the deploy itself but everything up to that point, I'm talking about technical debt.

Technical debt takes many forms and is the result of both concious, deliberate choices as well as unintended side-effects. Some examples of that are:

- Lack of or insufficient testing and associated 
- Overreliance on time consuming manual processes
- Shortcuts to meet deadlines - both artifical and real
- Violation of the 10-minute maxim
- Technological choices
- Cultural choices
- Fiscal limitations

All of these things can lead to technical debt - the accumulation of dead bodies in the room as a byproduct of how we work. At best, someone at least acknowledges they exist. At worst, we stock up on clothespins, pinch our nostrils shut and hope no one notices the stench. Let's address a couple of foundational things before we get into the fun stuff.

## Testing
Test coverage is one of the easiest ways to manage risk in software development. One of the first things to go in a pinch is testing. Even that assumes that testing was actually a priority at some point. I'm not going to harp on things like 100% code coverage. As I said previously, humans tend to overcompensate. Test coverage is also, however, one of the easiest places to get your head above water. If you don't have a culture of committment to testing, it's hard but not impossible to get started. You don't have to shutdown development for a week.

1) Start by having a commitment to write tests for any new code going forth.
2) As bugs arise in untested code, make a test case for the bug a requirement to close the bug. 
3) Find a small victory in existing code. Create test coverage for low hanging fruit.
4) Plan for a schedule to cover any remaining code

The key here is baby steps. Small victories. Think Fezzik in 'The Princess Bride' - "I want you to feel like you're winning".

Testing is one of the foundations you have to have to reach deploy nirvana. System administrators have a big responsiblity here. Running tests has to be painless, unobstrusive and performant. You should absolutely stand up something like Jenkins that actually runs your test suite on check-in. As that test suite grows, you'll need to be able to provide the capacity to grow with it. That's where the next point can be so important.

## Manual processes
Just as testing is a foundation on the code side, operations has a commensurate responsibility to reduce the number of human hands involved with creating systems. We humans, despite the amazing potential that our brains provide, are generally stupid. We make mistakes. Repeatability is not something we're good at. Some sort of automated and repeatable configuration management strategy needs to be adopted. As with testing, you can make some amazing progress in baby steps by introducing some sort of proper configuration management going forward. I don't recommend you attempt to retrofit complex automation on top of existing systems beyond some basics. Otherwise you'll be spending too much time trying to differentiate between "legacy" and "new" servers roles. If you are using some sort of virtualization or cloud provider like EC2, this is a no brainer. It's obviously a bit harder when you're using physical hardware but still doable.

Have you ever played the little travel puzzle game where you have a grid of moving squares? The idea is the same. You need just ONE empty system that you can work with to automate. Pick your simplest server role such as an apache webserver. Using something like Puppet or Chef, write the 'code' that will create that role. Don't get bogged down in the fancy stuff the tools provide. Keep it simple at first. Once you think you've got it all worked out, blow the server away and apply that code from bootstrap. Red, green, refactor. Once you're comfortable that you can reprovision that server from bare metal, move it into service. Make sure you have your own set of 'test cases' that ensure the provisioned state is the correct one. This will become important later on.

Take whatever server it's replacing and do the same for the next role. When I came on board with my company I spent many useless cycles trying to retrofit an automation process on top of existing systems. In the end, I opted to take a few small victories (using Chef in this case):

1) Create a base role that is non-destructive to existing configuration and systems. In my case, this was managing yum repos and user accounts.
2) Pick the 'simplest' component in our infrastructure and start creating a role for it.
3) Spin up a new EC2 instance and test the role over and over until it works.
4) Terminate the instance and apply the role on top with a fresh one.
5) Replace the old instances providing that role with the new ones and move to the next role.

Using this strategy, I was able to replace all of our legacy instances for the first and second tiers of our stack in a couple of months time. We are now at the point where, assuming Amazon plays nice with instance creation, we can have any role in those tiers recreated at a moment's notice. Again, this will directly contribute to how we mitigate risk later on.

## 10 minute maxim
I came up with this from first principles so I'm sure there's a better name for it. The idea is simply this:

> Any problem that has to be solved in five minutes can be afforded 10 minutes to think about the solution.

System Administrators often pride ourselves on how cleverly and quickly we can solve a problem. It's good for our egos. It's not, however, good for our company. Take a little extra time and consider the longer term impact of what solution you're about to do. Step away from the desk and move. Consult peers. Many times I've come to the conclusion that my first instinct was the right one. However more often than not, I've come across another solution that would create less technical debt for us to deal with later. 

A correlary to this is the decision to 'fix it or kick it'. That is i'do we spend an unpredictable amount of time trying to solve some obscure issue or do we simply recreate the instance providing the service from our above configuration management'. If you've gone through the previous step, you have should have amazing code confidence in your infrastructure. This is very important to have with Amazon EC2 where you can have an instance perform worse overtime thanks to the wonders of oversubscription and noisy neighbors.

Fuck that. Provision a new instance and run your smoke tests (I/O test for instance). If the smoke tests fail, throw it away and start a new one. It's amazing the freedom of movement afforded by being able to describe your infrastructure as code.

# Getting back to deploys
I would say that without the above, most of the stuff from here on out is pretty pointless. While you **CAN** do automated and non-offhour deploys without the above, you're really setting yourself up for failure. Whether it's a system change or new code, you need to be able to ensure that that some baseline criteria can be met. Now that we've got the foundation though, we can build on it and finally adopt some distinct strategies for releases.

# Building on the foundation
The next areas you need to work on are a bit harder.

## Metrics and monitoring
Shooting in the dark sucks. Without some sort of baseline metric, you authoritatively say whether or not a deploy was 'good'. If it moves, graph it. If it moves, monitor it. You need to leverage systems like [statsd](https://github.com/etsy/statsd) (available in non-node.js flavors as well) that can accept metrics easily from your application and make them availabile in the amazing [graphite](http://graphite.wikidot.com/).

The key here is that getting those metrics be as frictionless as possible. To fully understand this, watch [this presentation from Coda Hale of Yammer](http://pivotallabs.com/talks/139-metrics-metrics-everywhere). Coda has also created a kick-ass metrics library for the JVM and others have duplicated his efforts in their respective languages.

## Backwards compatibility
You need to adopt a culture of backwards compatibility between releases. This is not Microsoft levels we're talking about. This affects interim releases. As soon as you have upgraded all the components, you clean up the cruft and move on. This is critical to getting to zero/near-zero downtime deploys. This also allows you to 

## Reduce interdependencies
I won't go into the whole SOA buzzword bingo game here except to say that treating your internal systems like a third party vendor can have some benefits. You don't need to isolate the teams but you need to stop with shit like RMI. Have an established and versioned interface between your components. If component A needs to make a REST call to component B, upgrades to the B API should be versioned. A need version 1 of B's api. Meanwhile new component C can use version 2 of the API.

## Automation as a default
While this ties a lot into the testing and configuration management topics, the real goal here is that you adopt a posture of automation by default. The reason for this should be clear in [Eric Ries' "Five Whys" post](http://www.startuplessonslearned.com/2009/07/how-to-conduct-five-whys-root-cause.html):

> Five Whys will often pierce the illusion of separate departments and discover the human problems that lurk beneath the surface of supposedly technical problems.

One of the best ways to eliminate human problems is to take the human out of the problem. Machines are very good at doing things repeatedly and doing them the same way every single time. Humans are not good at this. Let the machines do it.

# Deploy Strategies
Here are some of the key strategies that I (and others) have found effective for making deploys a non issue.

## Dark Launches
The idea here is that for any new code path you insert in the system, you actually exercise it before it goes live. Let's face it, you can never REALLY simulate production traffic. The only way to truly test if code is performant or not is to get it out there. With a dark launch, you're still making new database calls but using your handy dandy metrics culture above, you now know how performant it really is. When it gets to acceptable levels, make it visible to the user.

## Feature flags
Feature flags are amazing and one of the neat tricks that people who perform frequent deploys leverage. The idea is that you make aspects of your application into a series of toggles. In the event that some feature is causing issues, you can simply disable it through some admin panel or API call. Not only does this let you degrade gracefully but it also provides for a way to A/B test new features. With a bit more thought put into the process, you can enable a new feature for a subset of users. People love to feel special. Being a part of something like a "beta" channel is an awesome way to build advocates of your system.

## Smoke testing at startup
This is one that I really like. The idea is simply that your application has a basic set of 'tests' it runs through at startup. If any of those tests fail, the code is rolled back.

Now this is where someone will call me a hypocrite because I said you should and can never really roll back. You're partially right. In my mind, however, it's not the same thing. I consider code deployed once it's taken production traffic. Up until that point, it's just 'pre-work' essentially. Let's take a random API service in our stack. I'm assuming you have two API servers in this case.

1) Take one out of service
2) Deploy code
3) Smoke tests run
4) If smoke tests fail, stop new code and start old code
5) If smoke tests pass, start sending production traffic to server
6) If acceptable, push to other server
7) profit!

Now you might see a bit of race condition as well there. I intentionally left out a step. This is a bit different than how shops like Wealthfront do it. They actually **DO** roll back if production monitoring fails. My preference is to use something similar to [em-proxy](https://github.com/igrigorik/em-proxy) to do a sort of mini-dark launch before actually turning it over to end-users. You don't have to actually use em-proxy. You could write your own or use something like RabbitMQ or other messaging system. This doesn't always work depending on the service the component is providing but it does provide another level of 'comfort' that by the time end-users (either internal components or customers) are actually interacting with the system, you've done everything you can to ensure that it's good.

Of course this only works if you maintain backwards compatibility.

## Backwards Compatibility
This is probably the hardest of all to accomplish. You may be limited by your technology stack or even some poor decision made years ago. Backwards compatibility also applies to more than just your software stack. This is pretty much a critical component of managing database changes with zero downtime.


