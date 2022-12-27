+++
title = "Configuration Drift and Next Gen Cm"
date = "2012-05-24"
slug = "2012/05/24/configuration-drift-and-next-gen-cm"
Categories = []
+++

It always starts with a tweet. However it normally doesn't start with a tweet from [Cliff Moon](https://twitter.com/moonpolysoft).

{% blockquote %}
Of all the problems to fix in chef or puppet, the diffusion and drift of state that occurs in idiomatic usage seems highest priority.
{% endblockquote %}

<!-- more -->

Now for sure what spawned this comment was something unrelated but it got me thinking. Oddly enough [Tim Dysinger](https://twitter.com/dysinger) was either poking around in my head or just had the same idea:

{% blockquote %}
Devops tools should move towards an active assertion of state (instead of passive/polling). This the next-level.
{% endblockquote %}

Tim and I hooked up via Skype and bantered about this stuff back and forth. We were on the same wavelength. That's pretty cool because Tim is pretty fucking smart (and he was able to explain Maybe Monads to me over dinner).

# My thoughts on the subject
What follows is something of a brain dump on what both Cliff and Tim had to say. However I'm going to be scoping in the context of security because

- [Beaker](https://twitter.com/beaker) gave a [presentation at Gluecon today](http://www.rationalsurvivability.com/presentations/SMCES-Gluecon2012.pdf) (_warning! bigass pdf_)
- I work with [David Mortman](https://twitter.com/mortman) who is one of the folks I say "gets it" w.r.t configuration management and security
- Security was the FIRST context that came to mind
- I was lucky enough to be involved with [Mark Burgess](https://twitter.com/markburgess_osl), [Jeff Blaine](https://twitter.com/cjeffblaine) and [Nick Silkey](https://twitter.com/filler) about a very similar topic where Mark said

{% blockquote %}
Good point. I wonder why folks often tear down a perfectly good machine and rebuild it instead of fixing what is broken.
{% endblockquote %}

What I'm going to say isn't new to anyone and smarter folks than I are already working on this (I'm sure) but this is the Internet. I get to babble too!

## On Drift
So what exactly *IS* the problem here? What's configuration drift and how the hell does it even happen?

The problem here is that, as Tim said, configuration management systems aren't assertive enough. Look at how a typical CM client run behaves:

- Hey guys, cm is running
- Oh look, this file doesn't look like it's supposed to
- /me changes file
- File looks good
- Hey guys, cm isn't running anymore

That last line is part of the problem. I've talked about my Noah project to largish groups of folks (both Puppet and Chef users) a few times now and the answer to the question of "Do you leave puppet (or chef) running in the background?" has always been "No". There are plenty of valid reasons for this but this is what I would consider the primary cause of drift at the node level. Now maybe this isn't EXACTLY what Cliff was talking about. I'm not quite on his level so I sometimes misinterpret but when I heard 'drift of state that occurs in idiomatic usage', this was what came to mind.

The thing is that these tools are designed to verify state of a resource at the point they run

- Does this file look right? No! Fix it. 
- Is this service running? Yes! Cool.

And then they go away. They don't manage the state of those resources until they next inspect them. The act of managing those resources is not in response to those resources changing but in response to a user ASKING them to be checked. I would even wager that when a user runs `chef-client` the first thing on her mind isn't "I sure hope chef fixes my sudoers file" but "I need chef to update my Nagios configs again". The incorrect state of the sudoers file isn't really even thought about. That's because we shove that stuff into some "base" role or group. Something that's applied to all nodes in our infrastructure. We don't think of a node as being a "managed sudoers" node. We think of it as a "web server".

Additionally, because we aren't in a constant state of verification about these resources, we may have drift that occurs across nodes of different types whilst they share a common base block. Sure I just ran my CM tool to update my Nagios server but what about my web servers? I don't want to run it there because I **KNOW** nothing has changed in the web server role.

To me, this is the "idiomatic" usage Cliff spoke about. The tools encourage us to think in terms of composition and reusable patterns but the final function of the node is the way we classify it. Mind you, the answer here is really to run your CM tool in the background but that still doesn't take us to the next level. We're still exposed to drift even if it's for a short period of time. What's worse is these tools operate by default with a splay value. This actually makes the drift exposure even worse as you can't even guarantee that it will run at the interval specified.

I first heard about CFEngine when Mark talked about "Anomoly Detection" at LISA '04 in Atlanta. My mind was blown but I could never get past the idea that I couldn't dictate state immediately. The idea (partially a naive understanding on my part) that systems would not become X when I said "Become X" bothered me. The idea that systems have a personality that needs to be respected bothered me.

The point here is that when I want a system to look like X, I want it to look like X right then. I want it to STAY looking like X and I don't want it to try and account for localized variations. I might feel differently if I were managing a network of servers that were essentially treated like desktops.

## But does drift really matter?
Yes and no. If you're living the cloud life, probably not. The reason is that resources tend to have a short shelf life. If I'm autoscaling via 'the cloud' to meet capacity demands then it's highly likely that those systems won't be around long enough to drift that far. In the land of configuration management, drift is largely time driven. The longer systems stay around, the greater the chance for drift.

However if you're running physical hardware that you don't tear down regularly, then drift is likely to become more pronounced. Interestingly enough there's a psychological factor at play here. Systems become like pets instead of cattle. We become attached to them. "Oh that's just db1 acting up again. You know it's like the oldest one in the fleet". People start forgetting that the system will fail (_Everything fails. Embrace failure!_). The start storing one-off scripts on there. Maybe it's core kit and while it's managed with Puppet, it's not frequently touched.

Here's another interesting point. As time progresses and modules, cookbooks, recipes, bundles, promises whatever are more infrequently touched, the confidence in them goes down. I've frequently found myself saying "Shit...I wrote that code like...I don't even fucking remember when. I have no idea what would happen if I ran it now.". This uncertainty eventually leads to me MANUALLY COMPARING the current state of resources that would be modified with the versions that would be generated. I've even copied comment blocks wholesale from one-off changes I've made into ERB templates just to ensure that a service restart didn't happen.

How fucked is that? Pretty fucked, Alex.

This partially leads into a quote from [John Allspaw](https://twitter.com/allspaw) on the dangers of OVER automation:

{% blockquote %}
Some people, when confronted with a problem, think "I'll use more automation!" Now they have Three Mile Island problems.
{% endblockquote %}

and is even discussed in [Patrick McDonnell's](https://twitter.com/mcdonnps) talk at [ChefConf](http://www.youtube.com/watch?v=nSnJCJiZDDU).

I don't agree with John 100% on his take but I can totally understand his perspective. Maybe I'm just more optimistic around exactly how far we can automate.

## So where does security fit into this?

Here's yet another quote from Tim Dysinger on this topic:

{% blockquote %}
If a super-user logs in and changes the sshd_config, you could have the service change it back before he even exited vi.  they'd then find a warning email in their in-box.  if they tried it again, even on another box, it could send a warning to the team lead and lock the user out.
{% endblockquote %}

Mind you security is only one aspect of this thought process. The thing that makes it applicable is that the security domain has already tackled this problem a bit. The problem is it still requires human response. We have tools like Tripwire, Samhain and OSSEC that do active inspection of state but the response is left up to a human. Additionally they're cumbersome to configure (even with CM). What's missing is the 'glue' between the two problem spaces.

In my head I envisioned something much like Tim described. In fact I even thought about a way that existing tools could be leveraged. It's not pretty but it's possible. The idea here, if it wasn't already blindingly obvious, is that the response to an event that the security tool recognizes should be to run configuration management to correct the errant state.

There are a few problems with this approach that should also be immediately obvious:

- CM is currently an all or nothing approach. Something like the idea behind 'partial run lists' in Chef could sort of address this
- If we start down the path of partial CM, we now have to take into account dependencies.
- We have no way to express this. We lack primitives with which to build this logic
- Security is still somewhat in the dark ages. Many security decisions are still binary in nature

What's also missing in this picture is something to identify patterns. So now we're bolting three tools together - the tripwire component, our CM tool and some sort of CEP. But again, even if we had this wondertool nirvana, how do we express it?

Let me be clear that I firmly believe that configuration management is absolutely a part of the security story. I stand by my assertion that consistent and repeatable configuration of a system from base OS to in-service is the foundation. Being able to express in code what state a system should have means that you never have to think "Did I forget to disable that apache module?" or "Did I make sure and disable root logins over SSH". Where we find gaps in this is how we assert the negative without going insane.

Denied unless explicitly allowed is the mantra I followed for years when I was responsible for security. Ask me some time WHY I got out of security and why I don't have ulcers anymore.

The questions I find myself asking are:

- If we use our CM system as the source of truth, can we sanely infer policy based on our CM codebase?
- If we use the network as the source of truth, can we sanely infer policy based on our neighbors? Should we even trust our neighbors?
- Do we even have the language to express what we mean and is it flexible and primitive enough to be used in composition? You can only go so far with "trusted", "untrusted","mauve" and "taupe".

# Wrap up

As I said, the original discussion was around configuration drift. I realize I went off on a tangent about security but that was intended as an example. I do believe that folks are working on this idea of "active assertion of state" as Tim puts it. I just wanted to brain dump my take on it. I don't know that it can be solved with even the most flexible of CM tools. I do feel like it's going to have to be a new generation of tool that takes these ideas into account and includes them in a ground-up design.
