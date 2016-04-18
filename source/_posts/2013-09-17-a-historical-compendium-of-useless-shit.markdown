---
layout: post
title: "A Historical Compendium of Useless Shit"
date: 2013-09-17 22:20
comments: true
categories: 
---

Yes it started on Twitter and ended up here. Circle of life and all that
<!-- more -->

_Fair warning: I'm not doing ANY research for this post. Normally I do but I'm doing this off-the-cuff so to speak.
If I fuck up some specific thing, please feel free to correct. This is basically a personal perspective/history
And it's probably full of typos so there's that_

So a discussion on twitter started because I brought up Mesos, Docker and OSv. I commented that none of these things were "new" technology really.

To be clear, it is a scientifically provable fact (for some values of 'scientifically provable fact') that when containers are brought up FreeBSD jails and Solaris Zones will work its way into the conversation. This is actually perfectly legitimate as the fit the same "model" as containers - lightweight 'virtualization'. And whenever virtualization is brought up, someone will bring up IBM and LPARs. Again, perfectly legit.

In rough order of quick wikipedia'ing and personal memory, it goes something like this:

- IBM virt (z, i and later p - can't recall if RS6k AIX did LPARS)
- FreeBSD jails (wikipedia says first in 4.0 so around 2000?)
- Solaris Zones (again the big interbrain says 2004)
- Xen (2003)
- VServer (???)
- OpenVZ (remember that shit? 2005ish)
- UML (????)
- VMware (late 90s iirc)

Also slot QEMU and KVM in here somewhere. These are all forms of 'virtualization'. I'm sure there are some pedants are drafting comments now but let's call it what everyone who isn't a pedant calls it - virtualization (this little bit is important). Each one is better or worse for different reasons.

All I'm trying to say is that everyone has a claim at some point in the stack. I've always argued that really IBM is the progenitor of this whole space but I'm sure someone will point out something I missed.

# So about `*`BSD
The point was made that everyone seems to forget about FreeBSD jails. I don't think that's the case at all. Here's my little take on why FreeBSD didn't "take off" (and the same applies to others as well). I'm probably wrong but whatever. Not like that's a first.

Everyone involved in this twitter convo has been in the industry a while. We remember when Linux wasn't the defacto operating system you would use. AIX, Solaris, HPUX - THOSE were the things REAL businesses used.

So why is Linux suddenly the defacto choice and why did technically superior solutions fall by the wayside? As I said, that wasn't always the case. We used to actually have to HIDE the fact that the print server wasn't running WindowsNT. Linux and FreeBSD were what we used at home. The others were what we used at work. I got my start with the whole Linux world around 95 with Slackware just as Yggdrasil was going away.

Anyway here are a few things I think that "hurt" FreeBSD a bit:

## Licensing
Licensing wars were almost as prolific as editor wars in on Slashdot. Regardless of your position on WHICH license is more 'liberal', this had an impact on the success. IIRC, the original BSD license was incompatible with the GPL. This meant there was little cross-pollination between the communities.

Regardless, what you ended up seeing was more "corporate" adoption of BSD into closed hardware products (firewalls, load balancers) and that never made it back into the community because it didn't have to.

On the Linux side, however, the GPL sort of promoted a community because of the forced pollination.

## Speciation
Probably not the best way to describe it but within the BSD community the 3 major derivitives ended up being pigeonholed

- FreeBSD was your server OS
- NetBSD was for weird-ass random hardware
- OpenBSD was for your firewalls ('cause Theo is mad crazy about security yo)

Again, whether or not this characterization was correct that was the general thought process most people I knew had.

Meanwhile, Linux was Linux. Yes there were distros but they largely set themselves apart by what they layered on top in userland. This is somewhat critical.

## Desktop
As I said above, FreeBSD was never really positioned as being for desktops among the commoners. Meanwhile Linux distributions were catering to that crowd. The thing is Linux was acceptable as a server OS as well. So if we were running Linux at home on our desktops, there was very little cognative disconnect to running it on our servers. Just don't install X and it's a server, right?

FWIW this is one of the things that I think helped Microsoft as well - knowledge portability.

## IBM
Quite honestly, IBM was probably the biggest thing to help Linux get that final push. Yes we'd been running Linux at home, on our personal web servers and in small corners of the office but now here's a name our bosses trust saying that Linux is a thing. That bald little boy talking about sharing shit and all that. Holy fuck! We made it. Then the COTS started coming. I remember getting that copy of Oracle 6 (or was it 7?) at Atlanta Linux Expo. Holy shit, Oracle runs on Linux!

So this IBM thing is a pretty big hurdle for a BSD to overcome. Marketing took over. Nobody cared that ufs was superior to ext2 or which is better ipfilter (then pf) vs. ipchains. IBM is backing Linux to the tune of 1 Billion dollars. Who gives a fuck about which license is more "free"?

Oh and IBM is doing it again though arguably it means fuckall at this point since Linux is, again, the defacto standard.

# Wrap up
Mind you this is mostly personal opinion/perspective. Is FreeBSD technically superior? Probably. Did it do certain things first and better? Yep (though as I brought up someone else did it before FreeBSD in some cases).

Let's face it, even as Linux fans you have to admit that Linux has been adopting shit from everyone else for a while and arguably as a shittier implementation. Don't get me wrong, I love Linux and I owe it a lot. This is one reason I try and give back as much as I can. I wasn't able to before.

I think FreeBSD has a chance for a comeback at this point (some of these have been going on for a while):

- REAL AWS support (not some hackish method). People start in AWS for better or worse.
- Having a native JDK by way of OpenJDK (as opposed to the ABI bullshit you used to have to do), will help.
- The general industry move AWAY from COTS. This has been going on for a while but nobody really fucking cares about if you can run Websphere.
- General embracing of alternative languages which have no problem running on BSD
- General frustration with Linux as a server OS. See CoreOS and talk to anyone at Fastly.

# Some things I find 'funny'
- For all the bitching we used to do about GPL vs BSD license, everybody I know these days is going with Apache over either of them
- After all this fucking time, "Linux on the desktop" is still a joke (and I've run Linux on my desktop for the past 15 years)
- Linux volume management STILL sucks compared to like....everywhere else
- People hated the BSD source-based model and yet Gentoo was a thing...


