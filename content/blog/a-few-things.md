+++
title = "A Few Things"
date = "2014-11-21"
slug = "2014/11/21/a-few-things"
Categories = []
+++
I can't believe I have to even write this
<!-- more -->
but it seems that comprehension is hard and sadly I made the mistake of reading comments when someone posted my systemd redux article on HN.

## CoreOS
Yes I know CoreOS uses systemd. It seems that ONE comment got taken entirely out of context. If you take it WITH the context of the whole post:

- If you must use Linux
- And we've agreed that systemd is here to stay in Linux
- Use a Linux "distro" where you don't care that it's running systemd

CoreOS is not a traditional general purpose Linux. CoreOS is a firmware for running containers. A bespoke platform for explicitly running containers. You don't manage CoreOS like a traditional distro. You don't use CoreOS like a traditional distro.

And yes I said CoreOS wasn't production ready yet. But it will get there. One of the people I respect most in this industry (and used to work with), Kelsey Hightower , works for CoreOS and has an operations background. If anyone can help address the needs of production usage of a platform, it's Kelsey.

## SystemD/SysV
As a project, I dislike SystemD. In the specific, there's a shitload of things SystemD gets right. I like unit files. I like running everything contained. I love the security features going in to restrict visibility to processes.

I dislike the SCOPE and I dislike the stuff that extends beyond the init replacement. I dislike the reinvention of things and in the process of bringing in bugs that have been solved for a while.

I also dislike the lockstep systemd/kernel upgrade that's coming.

## Gnome requiring systemd
You're right, gnome doesn't explicitly require systemd. It requires something that's only provided by systemd currently.

## I'm not afraid of change
I think anyone who's familiar with me and this blog knows that to not be the case. As Chris Webber said earlier, there's a difference between bleeding edge and leading edge.

## Solaris/OmniOS/SmartOS
I'm perfectly aware of the differences between these things. I guess forgetting to add the word "derivative" after the word "Solaris" makes me a total idiot.

## I don't "hate" Poettering or think he's "evil"
Really? This is software we're talking about. Just...wow

## FreeBSD
No jails are not a 100% replacement for LXC. It is, however, a more mature technology for isolation.

## You're right, I'm not a developer
[I've never written a line of code in my life.](https://github.com/lusis)

There's probably a whole bunch of other aspersions cast my way in the comments on HN and frankly I can't be bothered to read them. I'm actually busy managing a production SaaS and writing code in two different languages at the moment.
