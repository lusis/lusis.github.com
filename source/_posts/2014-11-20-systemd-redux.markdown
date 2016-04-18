---
layout: post
title: "systemd-redux"
date: 2014-11-20 00:21
comments: true
categories: 
---
I figured it was about time for a followup on my systemd post. I've been meaning to do it for a while but time hasn't allowed.
<!-- more -->

# The end of Linux
Some people wrongly characterized this as some sort of hyperbole. It was not. Systemd *IS* changing what we know as Linux today.
It remains to be seen if this is a good or bad thing but Linux is becoming something different than it was.

## Linux is in for a rough few years
I do honestly believe this will end up being the start of a rocky period for Linux.

- Lennart has already said that the expectation is that SystemD and the kernel will be upgraded in lockstep
- SystemD is consuming more and more of what is currently userspace
- SystemD is reinventing existing software stacks from scratch. See the recent systemd-resolvd cache poisoning issue and the journald transaction issues.

Additionally, while not Systemd specific but legitimately all inter-related, kdbus is coming and its already got its [fair share of issues in the first implementation](https://lkml.org/lkml/2014/10/29/854) including breaking userspace.

We also have distros like SLES adopting btrfs as the default filesystem.

All of these things combined mean that Linux is pushing the bleeding edge of a lot of unbaked technologies. Time will tell if this turns people off or not. I expect that enterprise shops will probably freeze systems at RHEL6 for a good while to come (and not just the standard "we're enterprise and we don't like to upgrade" time period).

## Systemd isn't going away
Systemd is here to stay. The only way you will have a system without it is to roll your own. I don't expect many distros to chose to back out. My best hope is that they'll all freeze at the current version. Maybe a few things will get backported here and there for security fixes.

## SystemD components are *NOT* optional
I know everyone likes to tout this but, no, the various systemd components while not pid 1 are realistically not optional. Kdbus, single parent hierarchy for namespaces (systemd is taking this one of course), udev changes - the kernel and distros are changing and coallescing around whatever systemd ships. Most distros will probably use systemd-networkd for instance. Look at what happened with Debian just today. The (albeit way late to the game) recommendation to support alternate init systems was rejected. I encourage you STRONGLY to read the systemd-devel mailing list for the kinds of issues you'll possibly have to deal with.

# Options
To be clear if you're going to stick with Linux, you will have to deal with systemd. It's up to you to decide if that's something you're comfortable with. Systemd is bringing some good things but, like other discussions I've been involved with, you're going to be stuck with all the other stuff that comes along with it whether you like it or not.

It's worth noting that FreeBSD just got a nice donation from the WhatsApp folks. It also ships with ZFS as part of the kernel and has a jails which is a much more baked technology and implementation than LXC. While you can't use docker now with jails, my understanding is that there is work being done to support NON-LXC operating system level virtualization (such as jails and solaris zones).

Speaking of zones and Solaris, if that's an option for you it's probably the best of breed stack right now. Rich mature OS-level virtualization. SmartOS brings along KVM support for when you HAVE to run Linux but backed by Solaris tech under the hood. There's also OmniOS as a variant as well.

If you absolutely MUST run Linux, my recommendation is to minimize the interaction with the base distro as much as possible. CoreOS (when it's finally baked and production ready) can bring you an LXC based ecosystem. If they were to ever add actual virt support (i.e. KVM), then you could mix and match as needed. If you're working for a startup or a more flexible organization, you can go down this path. If you're working for a more traditional enterprise, your options are pretty limited. At least you'll have the RedHat support contract.
