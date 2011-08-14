---
layout: post
title: Monitoring Sucks - Watch your language
date: 2011-07-21 22:19:00
comments: true
categories: [monitoring sucks]
---

*The following post is a recap of what was discussed in the 07/21/11 #monitoringsucks irc meeting*

Before I get any further, I just want to thank everyone who attended,
either in virtual person or in spirit. There have been so many awesome
discussions going around this topic since we started. I am truly
priviledged to interact with each and every one of you. I struggle to
count myself a peer and I can only hope that I provide something in
return.

I mentioned to someone today that I’m literally sick of the current
landscape. I consider the current crop of monitoring solutions to be
technologically bankrupt. The situation is fairly untenable at this
point.

I just installed (after having a total loss of an existing Zenoss setup)
Nagios again. I’m not joking when I say that it depressed the everliving
hell out of me. The monitoring landscape simply has not kept up with
modern developments. At first it was mildly frustrating. Then it was
annoying. Now it’s actually a detriment.

Now that we have that out of the way….
<!--more-->
# Darmok and Jalad at Tanagra

Communication is important. Like Picard and Dathon, we’ve been stranded
on a planet with shitty monitoring tools and we’re unable to communicate
about the invisibile threat of suck because we aren’t even speaking the
same language. I say event, you hear trigger, I mean data point. So the
first order of business was to try and agree on a set of terms. It was
decided that we would consider these things primitives. Here they are:

Please read through this before jumping to any conclusions. I promise it
will all become clear (as mud).

## metric

*a numeric or boolean data point*

The data type of a metric was something of a sticking point. People were
getting hung up on data points being various things (a log message, a
“status”, a value). We needed something to describe the origin. The
single “thing” that triggered it all. That thing is a metric.

So why numeric *OR* boolean? It was pretty clear that many people
considered, and rightly so I would argue, that a state change is a
metric. A good example given by [Christopher Webber](http://twitter.com/cwebber) is that of a BGP route going away.
Why is this a less valid data point than the amount of disk space in use
or the latency from one host to another? Frankly, it’s not.

But here’s where it gets fuzzy. What about a log message. Surely that’s a data point and thus a metric.

Yes and No. The *presence* of a log message is a data point. But it’s a boolean. The log message itself?

## context

*metadata about a metric*

Now metadata itself is a loaded term but in this scope, the “human
readable” attributes are considered context. Going back to our log
example. The presence of the log message is a metric. The log message
itself is context. Here’s the thing. You want to know if there is an
error message in a log file. The type of error, the error message text?
That’s context for the metric to use in determining a course of action.

Plainly speaking, metrics are for machines. Context is for humans. This
leads us to….

## event

*metric combined with context*

This is still up in the air but the general consensus was that this was
a passable definition. The biggest problem with a group of domain
experts is that they are frequently unable to accept semantic
approximation. Take the discussion of Erlang Spawned process:

-   It’s sort of like a VM on a VM
-   NO IT’S NOT.
-   *headdesk*

The fact is that an Erlang spawned process has shades of a virtual
machine is irrelevant to the domain expert. We found similar discussions
around what we would call the combination of a metric and its context.
But where do events come from?

## resource

*the source of a metric*

Again, we could get into arguments around what a resource is. One thing
that was painfully obvious is that we’re all sick and tired of being
tied to the Host and Service model. It’s irrelevant. These constructs
are part “legacy” and part “presentation”.

Any modern monitoring thought needs to realize that metrics no longer
come from physical hosts or are service states. In the modern world,
we’re taking a holistic view of monitoring that includes not only bare
metal but business matters. The number of sales is a metric but it’s not
tied to a server. It’s tied to the business as a whole. The source of
your metrics is a resource. So now that we have this information - a
metric, its context and who generated it - what do we do? We take….

## action

*a response to a given metric*

What response? It doesn’t MATTER. Remember that these are primitives.
The response is determined by components of your monitoring
infrastructure. Humans note the context. Graphite graphs it. Nagios
alerts on it. ESPER correlates it with other metrics. Don’t confuse
scope here. From this point on, whatever happens has is all decided on
by a given component. It’s all about perspective and aspects.

# Temba, his arms wide

I’m sure through much of that, you were thinking “alerting! graphing!
correlation!”. Yes, that was pretty much what happened during the
meeting as well. Everyone has pretty much agreed (I think) at this point
that any new monitoring systems should be modular in nature. As [Jason
Dixon](http://twitter.com/obfuscurity) put it - “Voltron”. No single
system that attempts to do everything will meet everyone’s needs.
However, with a common dictionary and open APIs you should be able to
build a system that DOES meet your needs. So what are those components?
Sadly this part is not as fleshed out. We simply ran out of time.
However we did come up with a few basics:

## Collection

*getting the metrics*

It doesn’t matter if it’s push or pull. It doesn’t matter what the
transport is - async or point-to-point. Somehow, you have to get a
metric from a resource.

## Event Processing

*taking action*

Extract the metric and resource from an event. Do something with it.
Maybe you send the metric to another component. Maybe you “present” it
or send it somewhere to be presented. Maybe you perform a change on a
resource (restarting a service). Essentially the decision engine.

## Presentation

While you might be thinking of graphing here, that’s a type of
presentation. You know what else is presentation? An email alert. Stick
with me. I know what’s going through your head. No..not that…the other
thing.

## Analytics

This is pretty much correlation. We didn’t get a REAL solid defintion
here but everyone was in agreement that some sort of analytics is a
distinct component.

## The “other” stuff

As I said, we had to kind of cut “official” things short. There was
various discussion around Storage and Configuration. Storage I
personally can see as a distinct component but Configuration not so
much. Configuration is an aspect of a component but not a component
itself.

## Logical groupings

Remember when I said I know what you’re thinking? This is what I think
it was.

You can look at the above items and from different angles they look
similar. I mean sending an email feels more like event processing than
presentation. You’d probably be right. By that token, drawing a point on
a graph is technically processing an event. The fact is many components
have a bit of a genetic bond. Not so much parent/child or sibling but
more like cousins. In all honesty, if I were building an event
processing component, I’d probably handle sending the email right there.
Why send it to another component? That makes perfect sense. Graphing?
Yeah I’ll let graphite handle that but I can do service restarts and
send emails. Maybe you have an intelligent graphing component that can
do complex correlation inband. That makes sense too.

I’m quite sure that we’ll have someone who writes a kickass event
processor that happens to send email. I’m cool with that. I just don’t
want to be bound to ONLY being able to send emails because that’s all
your decision system supports.

# Shaka, when the walls fell

Speaking personally, I really feel like today’s discussion was VERY
productive. I know that you might not agree with everything here. Things
are always up for debate. The only thing I ask is that at some point,
we’re all willing to say “I know that this definition isn’t EXACTLY how
I would describe something but it’s close enough to work”.

So what are the next steps? I think we’ve got enough information and
consensus here for people to start moving forward with some things. One
exercise, inspired by something Matt Ray said, that we agreed would be
REALLY productive is to take an existing application and map what it
does to our primitives and components. Matt plans on doing that with
Zenoss since that’s what he knows best.

Let me give an example:

Out of the box, Nagios supports Hosts and Services which map pretty
cleanly to resources. It is does not only collection but event
processing and presentation. It not only supports metrics but also
context (Host down is the boolean metric. “Response timeout” is the
context. Through something like pnp4nagios, it can support different
presentations. It has very basic set of Analytic functionality.

Meanwhile Graphite is, in my mind, strictly presentation and deals only
with metrics. It does support both numeric and boolean metrics. It also
has basic resource functionality but it’s not hardwired. It doesn’t
really do event handling in the strict sense. Analytics is left to the
human mind. It certainly doesn’t support context.

I’d love to see more of these evaluations.

Also, I know there are tons of “words” that we didn’t cover - thresholds
for instance. While there wasn’t a total consensus, there was some
agreement that somethings were attributes of a component but not a
primitive itself. It was also accepted that components themselves would
be primitives. You correlation engine might aggregate (another word) a
group of metrics and generate an event. At that point, your correlation
engine is now a resource with its own metrics (25 errors) and context
(“number of errors across application servers exceeded acceptable
limits”) which could be then sent to an event processor.

That’s the beauty of the Voltron approach and not binding a resource to
a construct like a Host.

# Special note to the Aussies

I’m very sorry that we couldn’t get everyone together. I’ve scheduled
another meeting where we can start from scratch just like this one, or
build on what was discussed already. I’m flexible and willing to wake up
at whatever time works best for you guys

Thanks again to everyone who attended. If you couldn’t be there, I hope
you can make the next one.
