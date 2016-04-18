---
layout: post
title: "The End of Linux"
date: 2014-09-23 22:53
comments: true
categories: 
---
I've done a lot of tweeting about systemd lately. My internal conscience constantly reminds me of John Allspaw saying that twitter is just pretty much perfect for snarky comments (paraphrase).
<!-- more -->

Al Tobey asked me a good question:

{% blockquote %}
I honestly want to know why you dislike it so much. You clearly know wtf is going on. I haven't heard a specific technical problem.
{% endblockquote %}

First mistake is thinking I know wtf is going on. However the question was asked. What "technical" concerns do I have with systemd?

I don't (sort of). Here are my primary ones:

- journald
- architecture

## `journald`
Yes `journald` can be setup to use syslog but by default, it'll use a binary log format. Sure you can use "strings" on it but is anyone seriously considering that a proper way to get to your system logs? In fairness, `journalctl` provides some nice mechanisms for targeting specific message types, sources and scope but at the expense of having to use `journalctl` as the unified interface. Keep this tradeoff in mind when I get to the "real problems" section.

## Architecture
On the architecture of systemd, I have a legitimate concern with the scope. Let's use the image from wikipedia:

{% img /images/posts/systemd/systemd-arch.png %}

I previously stated that `systemd` provided a nice juicy attack surface. There are valid arguments that not all these components are "core" `systemd`. Regardless, they are still components and there is an implicit trust relationship with "core" vs "components". Yes `systemd` sticks everything in `cgroups` (another minor issue I have) but with the coming Dockerpocalypse didn't everyone learn that cgroups were not a security mechanism (nor are containers for that matter)? I still stand by my statement that the "big one" linux exploit will somehow be tied to systemd.

But back to that architecture for a minute. There are a lot of things in there, that while possibly optional, are things I have zero need for where systemd affects me the most. Telephony? Graphical sessions? I didn't even know what Tizen is before this post (and I think the modified image on wikipedia came from the Tizen wiki). Maybe it's not required. I can't tell. Keep this in mind also.

Probably the best argument against the architecture of systemd is from one of the primary authors [here](http://0pointer.de/blog/projects/why.html "Why Systemd"). A list of "advantages" that includes the SCM system in use or the fact that there are "Specialized professional consulting and engineering services available" is not a valid technical merit. Finally buried deep in the text near the end, we come to understand the biggest architectural problem of all:

{% blockquote %}
systemd is in the process of becoming a comprehensive, integrated and modular platform providing everything needed to bootstrap and maintain an operating system's userspace. 
{% endblockquote %}

I also personally think that the [`systemd` design motivations](http://0pointer.de/blog/projects/systemd.html) are "flawed" at the core:

{% blockquote %}
Now, if that's all they are waiting for, if we manage to make those sockets available for connection earlier and only actually wait for that instead of the full daemon start-up, then we can speed up the entire boot and start more processes in parallel. So, how can we do that? Actually quite easily in Unix-like systems: we can create the listening sockets before we actually start the daemon, and then just pass the socket during exec() to it. That way, we can create all sockets for all daemons in one step in the init system, and then in a second step run all daemons at once. If a service needs another, and it is not fully started up, that's completely OK: what will happen is that the connection is queued in the providing service and the client will potentially block on that single request.
{% endblockquote %}

and

{% blockquote %}
Because this is at the core of what is following, let me say this again, with different words and by example: if you start syslog and and various syslog clients at the same time, what will happen in the scheme pointed out above is that the messages of the clients will be added to the /dev/log socket buffer. As long as that buffer doesn't run full, the clients will not have to wait in any way and can immediately proceed with their start-up. As soon as syslog itself finished start-up, it will dequeue all messages and process them. Another example: we start D-Bus and several clients at the same time. If a synchronous bus request is sent and hence a reply expected, what will happen is that the client will have to block, however only that one client and only until D-Bus managed to catch up and process it.
{% endblockquote %}

I'm going to go on record and say that this is quite possibly the worst idea to anyone running a server. The acceptable use cases for this are so narrow that it's hardly justification for everything that followed.

# The real problems
I know my "technical" arguments are flimsy. The fact is there some really cool shit in `systemd` including many of the things listed in the linked post.

The problem with `systemd` is that it is the single most invasive change to Linux in a long line of changes that ultimately mean that Linux may be headed towards uselessness as a server operating system.

## The invasion of "desktop linux"
I'm going to state up front (and people are free to disagree with me) that I believe you cannot provide a distribution of Linux that is both designed for the "server" and the "desktop" and provide a product that is worth using on either.

We've see this happening with regularity in other places such as `d-bus`. Again, these things aren't neccessarily BAD things (and kdbus will enable some REALLY cool shit) but at what cost? I think motivation matters considerably here.

Understand that I exclusively run "linux on the desktop" and I have for a VERY long time. I have a vested interest in Linux not sucking on the desktop. However I have a GREATER interest in Linux on the server not sucking. My linux desktop doesn't send me pager alerts at 3AM when pulseaudio shits the bed because of some USB interrupt issue with my headset. Pagerduty will, however, call me on the phone and wake up my sleeping partner when there's a kernel panic.

In fact, I'll go even further and say that ANY kernel or distro related change that was driven by "the linux desktop" is suspect to me.

The problem is I don't have that luxury. I have competing responsiblities. I have to provide a system that runs reliably and can easily be reasoned about and yet I have to build it on distributions created by people who consider how long it takes to get to the fucking GDM login screen and if shutting the laptop lid will cause the system to hibernate properly or not.

I realize that there is overlap in these cases. A power efficient operating system has benefit to me sure but it's not my primary concern.

## Maturity
This could be classified as technical but it's not just about the project itself. Systemd *IS* an immature system. Wikipedia puts the initial release as 3/30/2010. Lennart's "announcement" has a date of 04/30/2010. Let's call it four years among friends.

We have a system that has gone from a blog post to being the "comprehensive, integrated and modular platform providing everything needed to bootstrap and maintain an operating system's userspace".

I don't think so.

Let's also not forget that systemd uptake was LARGELY restricted to Fedora up until the point that the Gnome team decided that `logind` would be a future requirement. I want that to sink in VERY clearly.

Systemd did not get to the place it was in UNTIL it became, by proxy, a requirement for GNOME. What did that give us?

- Ubuntu 14.04 (the LTS release) is running on a hacked fork of `logind`.
- RHEL/CentOS7 (again LTS releases) are running v208 of systemd that was tagged in October of last year

Now I'm the first person to complain about distros keeping way old versions of stuff around but this is ridiculous. You cannot tell me that is considered "baked" by any stretch of the imagination.

## History
I can't say much here except that my experience with previous projects from Poettering (pusleaudio and avahi) give me very little faith in systemd.

Is that a fair assessment? I think it has relevance. There's a question of what the driving force is behind someone's logic. There's a question of previous quality. Does the person go after the new shiny and abandon some previous project?

The only upshot, I guess, is that distros have a vested interest now as there is no avoiding systemd. This is likely a change that will never get rolled back. We can only hope that the Linux certain people want is the Linux that meets our needs as system administrators.


## Compatibility
This issue is probably irrelevant to most people but it bothers me greatly.

With the systemd adoption comes the first steps to more applications being "Linux" only. When the creator of systemd says that we should ignore POSIX compatability and systemd itself relies on Linux-only features like cgroups there's really little hope left. GNOME requiring logind means realistically that GNOME will only ever run on Linux. Will something step up and take its place? Maybe but it will likely see zero adoption and be a niche player in the overall scheme.

Linux is becoming the thing that we adopted Linux to get away from.

# Wrap up
I am fully willing to concede that systemd is going to enable some really cool stuff. CoreOS has adopted nspawn. Unit files can provide a straight up dead simple mechanism for applications to daemonize themselves. Finally you can say "just daemonize this command" for me. Cgroups integration is really quite awesome as well. I'm totally for Linux adopting some of these awesome new things.

The problems I have are the tradeoffs. This is very similar to a previous discussion I was involved in regarding another tool. In this case you came for cgroups and faster boot times(\*) and you got stuck with:

- binary logging formats
- an http server in your init system (oh sure you can use unix domain sockets but have fun with Java talking to those)
- QR codes

Oddly enough, the thing that is giving more uniformity to Linux is making it less "Linux" to me.

## Comments
I am, as always, willing to be corrected in places where I got things wrong. Please be aware that I check in for comments on posts and don't get notified on new ones. If you feel like the discussion doesn't cleanly fit in a comment, feel free to post a gist and I'll respond there and link here.

_(*) The fastest part of booting my servers is the init. It's the 7 minutes of POST+device enumeration that takes more time. My instances in CloudStack are nearly instant boot as well._

