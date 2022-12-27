+++
title = "Why Ebs Was a Bad Idea"
date = "2012-06-15"
slug = "2012/06/15/why-ebs-was-a-bad-idea"
Categories = []
+++

Since I just tweeted about this and I know people would want an explaination, I figured I'd short circuit 140 character hell and explain why I think EBS was the worst thing Amazon ever did to AWS.
<!-- more -->

_First time I've had to do this but: the following is my personal opinion and in no way reflects any policy or position of my employer_
# A journey through time
I remember when EC2 was first unleashed. At the time I was working at Roundbox Media (later Roundbox Global - because we had an office in Costa Rica!). I was asked frequently if we could possibly host some of our production stuff there.

It was pretty much a no go from the start:

- No persistent IPs
- No persistent storage

Sure we could bake a bunch of stuff into the AMI root but the ephemeral storage wasn't big enough to hold a fraction of our data. Still, we leveraged it as much as possible. We ran quite a bit of low risk stuff on it - test installs of our platform and demo sites for customers.

After I left RBX in Feb of 2008, I didn't get an opportunity to work with AWS for a year or so and by then quite a bit had changed. If Amazon does one thing really well, it's iterating quickly on its service offerings.

# So why is EBS a bad thing?
For Amazon, EBS is NOT a bad thing. It was probably one of the smartest business moves they made (along with Elastic IPs). They could now claim that EC2 was JUST like running your own kit - you have a SAN! you have static IPs!

The problem is it's not.

# The nature of block storage
Anyone who's dealt with any sort of networked filesystem knows the pains it can cause with certain application profiles. Traditional databases are notorious for expecting actual local storage and real block devices. It amazes me the number of people who put up with the pain of running a database in something like vmware using virtual disks hosted on an NFS device.

The point is the block devices have specific semantics and presumptions.

With EBS you're promised a tasty block device that your OS can address as if it were local disk. Only it's not....

## Latency
Let's get the biggest elephant out of the way. EBS is a block device to the OS but under the hood it's using the network. It may or may not be shared with non-block device traffic but it's still subject to network latencies. God I hope that EBS at least gets its own port on the host side...

## Shared
There's a whole lot of sharing going on here to:

- local bandwidth from the physical server where your instance is to a given EBS subsystem (array, CEC, whatever)
- aggregate bandwidth from all pysical servers talking to a given EBS subsystem
- disk I/O itself on a given EBS subsystem

I don't know how the connection from server to EBS is done. I would hope at least there are bonded ports or multiple uplinks/multipathing going on. I would REALLY hope that network I/O and Disk I/O are not on the same channel. Regardless, you're still sharing whatever the size of that connection is with everyone else on the physical server your instance is on if they're using EBS as well.

And the physical EBS array where your volume is? Depending on the size of your EBS volume, you're dealing with network I/O on that unit's connection from an unknown number of other customers. And to top it off, you're not just sharing network bandwidth, you're sharing disk bandwidth as well. There are still spindles under there folks. Sticking an API in front of it doesn't change the fact that there is spinning rust under the covers.

Above ALL of that, you've got competing workloads - sequential vs random read.

Sure, just stick your root OS volume on that. That's a great idea.

# Mixed messages
To me, however, the biggest problem with EBS is not the latency. It's not the shared resources. It's not even taking something that is fundamentally locality oriented and trying to shoehorn it into something distributed.

It's the fact that it sends the wrong damn message. I've said this before, I'll say it again and I'll stand by it.

**Unless you are willing, able or have designed your applications to have any single part of your infrastructure - connectivity, disk, node, whatever - ripped from under you with no warning whatsoever, you should not be running it on Amazon EC2.**

By providing EBS, Amazon sends the message that "you can treat this just like your own datacenter". Just use EBS and you can treat it just like a SAN. Look, we have snapshots!

Hell, I get pissy when folks refer to instances as "boxes" and talk about them like they're something they physically own. Stop trying to map physical datacenter analogies to AWS. It won't work and you'll be disappointed.

You want to know the real kicker? You should be designing like this ANYWAY. Yes, you have much greater control over failure points when you run everything yourself. You have much greater control over resource sharing and I/O profiles. That doesn't remove the need to design for failure. How far you take it is up to you (and realistically your budget) but when you're running on AWS, you need to be much more attentive to it.

# For the record
I still think AWS and public clouds are awesome. I really do. I think private clouds are just as awesome. The flexibility they offer is almost unmatched but that flexibility comes at a price - performance hits, multiple layers of abstraction and other things.
