---
layout: post
title: "The Configuration Management Divide"
date: 2011-08-22 22:07
comments: true
categories: [devops, configuration management, puppet, chef]
---

_wherein John admits he was wrong about something_

As I was checking my Github feed tonight (as I've been known to do on occassion), I saw an update for a project that I was watching. This project is, for all intents and purposes, a redo of Capistrano with some additional system management stuff on top. From the description:

> All together mixed to make your life easier.
> As mentioned before do is a fun mix of capistrano, rake, thor and brew.
> With XXXXX you are be able to do easily common task on your local or remote machine. The aim of XXXXX is to become the unique and universal tool language that permit you to make your own brew, apt-get or yum package, same syntax for all your machine.

Now I've said before I'm a tool junkie. I'm always looking for ideas and inspiration from other tools. I also love to contribute where I can.
What bothered me particularly about this project is that it felt like a "solved problem".

<!-- more -->
## Open mouth, insert foot (at least partially)

So at DevOps Days Mt. View this year, I made a statement. I said with quite a bit of firmness that I thought configuration management was a solved problem. I didn't get a chance to clarify what I meant by that. At least not before [Andrew Clay Shafer](http://twitter.com/littleidea) strongly disagreed with me. Before I go any further, I want to clarify what I meant by that.

I, personally, feel like there comes a point where a class of tool reaches a certain level that makes it "good enough". So what is good enough?
By "good enough", I mean that it solves the first in a broader series of problems. By no means am I implying that we should no longer pursue improvement. But what I am suggesting is that at a certain point, tools are refining the first stage of what they do.

When we get to this point in a class of tool (let's stick with CM), we start to see gaps in what it can do. At this point, you get a rush of tools that attempt to fill that gap. What we essentially do is try and figure out if the gap should be filled by the tool or does it make sense to exist apart. So taking the discussion I was involved in at the time - orchestration, what I was attempting to say (epic fail, I might add) is that the current crop of configuration management tools have reached a usable point where they do enough (for now). What we're seeing as questions now are "How do I think beyond the single node where this tool is running?". What we might find is that functionality DOES belong in our CM tool. We might also find that, no, we need this to exist apart from it for whatever reasons. Basically I was attempting to say "Hey, CM is in a pretty good state right now. Let's tackle these OTHER problems and regroup later".

So where was I [wrong](http://youtu.be/V3y3QoFnqZc)?

## Developers, developers, developers

I made the following comment on Twitter earlier:

> The sheer number of what are essentially Capistrano clones makes me realize that the config. management message fails to resonate somewhere.

What I find most interesting about these various "clones" is that they're all created by developers. People apparently pine for the days of Capistrano. Come to think of it though, who blames them?

As I look at the current crop of configuration management tools, and follow various tweets and blog posts I realize that people either aren't aware of tools like puppet and chef or, in the worst cases, they feel that they're too much hassle/too complicated to deal with. What we're seeing is the developer community starting to come to the same realizations that the sysadmin community came to a while ago. What's also interesting is that the sysadmin community is coming to the same realizations that developers came to a while ago.

It's like some crazy romantic movie where the two lovebirds, destined to be together in the end have the pivotal mix-up scene. He leaves the bar to stop her from getting on the airplane. Meanwhile she heads to the bar upon realizing that she can't get on the plane without him.

People are going to great lengths, either out of ignorance\* about available tools or from frustration about the complexities involved.

## So where's the divide?

I think the divide is in the message. While I still stand behind the statement that the "dev" vs "ops" ratios are regionally specific, I'm starting to agree that the current crop of tools **ARE** very operationally focused. That's not a bad thing either. Remember that the tools were created by people who were primarily by sysadmins.

When you look at the tools created by people who were primarily developers, what do you see?

- Apache Whirr
- Do
- Fabric
- Capistrano
- Glu
- DeployML

What's interesting about all of these tools is that they're designed to get the code out there as easily as possible or to stand up a stack as quickly as possible. What's frustrating for me, as primariy a sysadmin, is that I see the pain points that will come down the road. These tools don't really scale. Yes, sometimes you need "ssh in a for loop" however inherting these types of environments can be painful. Concepts like idemopotence and repeatability don't exist. They generally don't take into account the non-functional requirements. They certainly don't handle multiple operating environments or distributions very well.

What's really sad is that the end goal is the same. We just want to automate away the annoying parts of our jobs so we can get on to more important shit.

### You arrogant prick

I figured I'd get that out of the way now. It's very easy to dismiss these concerns as some pissy sysadmin but I don't think that's entirely fair. Remember what I said earlier. Sysadmins are starting to come to realizations that developers had a long time ago. The same exists for developers. Both sides, regardless of primary role, still have much to learn from each other.

## What do we do?

I'm going to share a fairly generic dirty little secret. Developers don't want to write puppet manifests/modules. They don't want to write chef cookbooks/recipes. In most every situation I've been in, there has been little interest in those tools from outside the sysadmin community. That's changing. I think as people are getting introduced to the power they have, they come around. But there's still a gap. Is the gap around deployment? Partially. I think the gap also exists around configuration. Obviously I'm biased in that statement.

A bit of a personal story. At my previous company, we were a puppet shop. We tried to get developers to contribute to the modules. It didn't work. It just didn't make sense. It's not that they were stupid. It's that it was an extra step. "Oh you mean I have to put that variable over here instead of in my configuration file? That's annoying because I have to be able to test locally". What ended up happening was that one of the guys on the operations side learned just enough Java to write lookup classes himself. Instead of us populating hosts using ERB templates, we were now querying Cobbler for classes using XML-RPC. There were some other factors at play, mind you. It was a really large company with developers who came from the silo'd worldview. There was also quite a bit of ass-covering attitude running around.

This is why I'm so bullish on bridge tools like Noah and even Vagrant. We're all attempting to do the same thing. Bridge that last little bit between the two groups. Find a common ground. Noah is attempting to bridge that by allowing the information to be "shared" between teams. ZooKeeper let's the developers manage it all themselves (just push this app out, we'll find each other). Vagrant takes the route of bringing the world of production down to the developer desktop.

But none of these tools address the deploy issue. Do we want to use the Opscode deploy cookbook? We can but it's fairly opinionated\*\*. Maybe we decide to use FPM to generate native system packages and roll those out with a Puppet run? Putting on my fairly small developer hat, none of these sounds really appealing to me. Maybe as the java developer, I just want to use JRebel. That's nice but what about the configuration elements? Do we now write code to ask Puppet or Chef for a list of nodes with a given class or role?

Look at what Netflix does (not that I approve really). They roll entire fucking AMIs for releases. While the sysadmin in me is choking back the bile at the inflexibility of that, stepping into another perspective says "It works and I don't have to deal with another server just to manage my systems".

## I don't have all the answers

That much is obvious. What I can say is that I see a need. I'm addressing it the way I think is best but I still see gaps. Shared configuration is still complicated. Deploy is still complicated. While I would generally agree that things like rolling back are the wrong approach, I also realize that not everyone is at the point where they can roll-forward through a bad deploy. Hell, we do all of our deploys with Jenkins and some really ugly bash scripts and knife scripts.

I'd love any commentary folks might have. I'll leave you with a few comments from other's on twitter when I made my original comment:

> Capistrano serves a different need. Rapid deployment with rollback isn't implicit in the current crop config mgmt tools - [@altobey](https://twitter.com/altobey)


> deploying code with a CM tool (eg, the chef `deploy` resource) is a grey area for me. for example: does rollback really fit? - [@dpiddee](https://twitter.com/dpiddee)


> or that it might be missing some use case? - [@jordansissel](https://twitter.com/jordansissel)


> I've played with using deployml in its local only mode kicking off deploys with mcollective, worked really nice - [@ripienaar](https://twitter.com/ripienaar)


> Roll forward, never back. - [@bdha](https://twitter.com/bdha)


> This is a fight I have at least two or three times at every conference I speak at and in a bucket load of other places - [@kartar](https://twitter.com/kartar)


> further thought: I consider capistrano to be one impl of a "deployment service" on the same level as, say, a CM tool  - [@dpiddee](https://twitter.com/dpiddee)



- "Ignorance": I don't mean ignorance in the sense of stupidity. I mean it strictly in the sense of not having knowledge of something. No judgement is implied
- "Opinionated" - Opinionated isn't bad. It's just opinionated.
