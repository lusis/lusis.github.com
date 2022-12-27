+++
title = "It Sucks to Be Right"
date = "2012-03-20"
slug = "2012/03/20/it-sucks-to-be-right"
Categories = []
+++
So it looks like Adrian Cockcroft finally spilled the beans on [Netflix (no)Operations](http://perfcap.blogspot.com/2012/03/ops-devops-and-noops-at-netflix.html) and sadly it reads like I expected.
<!-- more -->

# Netflix still does operations
Regardless of what words Adrian uses, Netflix still does operations. [John Allspaw](http://twitter.com/allspaw) summed it up pretty well in this tweet:

![Imgur](http://i.imgur.com/OW0kh.png)

and here are the things, he mentions:

- Metrics collection
- PaaS/IaaS evaluation/investigation
- Automation (auto-build, auto-recovery)
- Fault tolerance 
- Availability
- Monitoring
- Performance
- Capex and Opex forecasting
- Outage response

# So what does Adrian get wrong?
These are just a few things that jumped out at me (and annoyed me)

{% blockquote %}
However, there are teams at Netflix that do traditional Operations, and teams that do DevOps as well.
{% endblockquote %}

Ops is ops is ops. No matter what you call it, Operations is operations. 


{% blockquote %}
Notice that we didn't use the typical DevOps tools Puppet or Chef to create builds at runtime
{% endblockquote %}

There's no such thing as a "DevOps tool". People were using CFengine, Puppet and Chef long before DevOps was even a term. These are configuration management tools. In fact Adrian has even said they use Puppet in their legacy datacenter:

![Imgur](http://i.imgur.com/RJIX1.png)

yet he seems to make the distinction between the ops guys there and the "devops" guys (whatever those are).

{% blockquote %}
There is no ops organization involved in running our cloud...
{% endblockquote %}

Just because you outsourced it, doesn't mean it doesn't exist. Oh and it's not your cloud. It's Amazon's.

# Reading between the lines
Actually this doesn't take much reading between the lines. It's out there in plain sight:

{% blockquote %}
In reality we had the usual complaints about how long it took to get new capacity, the lack of consistency across supposedly identical systems, and failures in Oracle, in the SAN and the networks, that took the site down too often for too long.
{% endblockquote %}

{% blockquote %}
We tried bringing in new ops managers, and new engineers, but they were always overwhelmed by the fire fighting needed to keep the current systems running.
{% endblockquote %}

{% blockquote %}
This is largely because the people making decisions are development managers, who have been burned repeatedly by configuration bugs in systems that were supposed to be identical.
{% endblockquote %}

{% blockquote %}
The developers used to spend hours a week in meetings with Ops discussing what they needed, figuring out capacity forecasts and writing tickets to request changes for the datacenter.
{% endblockquote %}

{% blockquote %}
There is no ops organization involved in running our cloud, no need for the developers to interact with ops people to get things done, and less time spent actually doing ops tasks than developers would spend explaining what needed to be done to someone else.
{% endblockquote %}

I'm glad to see this spelled out in such detail. This is what I've been telling people semi-privately for a while now. Because Netflix had such a terrible experience with its operations team, they went to the opposite extreme and disintermediated them.

Imagine you were scared as a kid by a clown. Now imagine you have kids of your own. You hate clowns. You had a bad experience with clowns. But it's your kid's birthday party so here you are making baloon animals, telling jokes and doing silly things to entertain the kids.

Just because you aren't wearing makeup doesn't make you any less of a clown. You're doing clown shit. Through the eyes of the kids, you're a clown. Deal with it.

Netflix is still doing operations. What should be telling and frightening to operations teams everywhere is this:

The Netflix response to poorly run operations that can't service the business is going to become the norm and not the exception. Evolve or die.

Please note that I don't lay all the blame on the Netflix operations team. I would love to hear the flipside of this story from someone who was there originally when the streaming initiative started. It would probably be full of stories we've heard before - no resources, misalignment of incentives and a whole host of others.

Adrian, thank you for writing the blog post. I hope it serves as a warning to those who come. Hopefully someday you'll be able to see a clown again and not get scared ;)
