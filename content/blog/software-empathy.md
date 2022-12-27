+++
title = "Software Empathy"
date = "2014-10-19"
slug = "2014/10/19/software-empathy"
Categories = []
+++
Over the past several months, I've written a lot of blog posts that were critical of various software. If it wasn't clear from these (sometimes) incoherent ramblings, there was a common thread that bound them all together
<!-- more -->

## Why do we write software?
I write software every now and then. When I do, if possible, I put it up on github for folks to consume. I've talked about this before. For me, being able to write code and somehow that code helps someone blows my mind.
I haven't always been able to write code (or at least I always told myself I couldn't). I've never been paid for that task. It's not the career path I chose all those years ago. I like operations. I like managing systems. Most of the code I write is for that purpose in some roundabout way.
Other people write code because they're paid to write code. Sometimes it gets released as open source. Sometimes it doesn't. There are no value judgements in that. It's just a thing that is.

Why do we create anything at all? Ego, altruism, enlightened self-interest - all are justifications given at some point.

Regardless, when it comes to software, we create something that doesn't just exist but exists to be consumed - either personally or professionally - and in something of an interesting twist this thing we create is consumed repeatedly even if we never realized it would be.

## On users
When you release software, regardless of the motivation, you always have a user. That user can just be you (the creator), it can be customers, it can be a minority community or, if you're really lucky, it can be "pretty much everybody".

The best (or worst) part of it is that once it's out there, you have no control over who that user is. Sure you can gate the userbase (think: customers or internal projects) but if you release something as "open source", you've just given up any control over who uses that software.

Yes, you have users and now you have to act accordingly

## Software as an expression of empathy
We've all heard of Conway's Law:

{% blockquote [Melvin Conway] [http://en.wikipedia.org/wiki/Conway%27s_law] %}
organizations which design systems ... are constrained to produce designs which are copies of the communication structures of these organizations
{% endblockquote %}

In that same vein I would argue that the software people create and its lifecycle - how they support it, how it behaves, how it interacts with other software - is a reflection of the empathy of the creator(s) for the user.

I want to use a couple of examples here. Of course these are only a few and they so obviously support my theory that I must be right.....

Seriously though.

### Logstash
Logstash is one of those projects that "everyone uses". Of course not everyone uses it but really, EVERYONE uses it. Logstash has a motto:

{% blockquote [Jordan Sissel] %}
Remember: if a new user has a bad time, it's a bug in logstash.
{% endblockquote %}

If any of you have had the wonderful privilege of knowing Jordan in person, you would understand that this is a DIRECT reflection of him as a person. He has empathy for the users of his software. These days, Jordan is paid to work on logstash and there's a commercial drive to it but this quote has been there well before that.

I'm going to come back to this quote in a few.

### Systemd/PulseAudio
These are a few project from Lennart Poettering. Systemd itself has its own "cabal" (self-titled). I'm not going to get into why I dislike systemd again except to say, I address the empathy issue there as well. Note that I'm not calling out Lennart specifically here, though there is history in bug reports and mailing lists from not just him but others in the "cabal". 

In contrast to logstash, bugs in PulseAudio or problems with systemd are never the fault of the creator but always the user or the user's hardware or the user's inability to move on. I was even told that I just wanted people to "get off my lawn" because I questioned systemd.

The point is, PulseAudio and systemd as projects have a distinct LACK of empathy from either the original authors or the proponents of it.

### Linux kernel
{% blockquote [Linus Torvalds] [https://lkml.org/lkml/2012/12/23/75] %}
Mauro, SHUT THE FUCK UP!
{% endblockquote %}

Looking at this you might say "Wow Torvalds is a dick. He has no empathy". And if you took his handling of kernel issues and maintainers as is, you'd probably be right. In fact, while I've never met the guy, let's just say "Sure he's a dick".

However further down in that same post he says this:

{% blockquote [Linus Torvalds] %}
WE DO NOT BREAK USERSPACE!
{% endblockquote %}

In an interesting crossover, you can even read his interactions with Kay Sievers (one of the core developers of systemd) that is a reflection of the same quote:

{% blockquote [Linus Torvalds] %}
Key, I’m f\*cking tired of the fact that you don’t fix problems in the code *you* write, so that the kernel then has to work around the problems you cause.
.....
But I’m not willing to merge something where the maintainer is known to not care about bugs and regressions and then forces people in other projects to fix their project. Because I am *not* willing to take patches from people who don’t clean up after their problems, and don’t admit that it’s their problem to fix.
{% endblockquote %}

# How is empathy formed?
Since we've not yet defined empathy yet, let's do that:

[the ability to understand and share the feelings of another](https://www.google.com/search?client=ubuntu&channel=fs&q=empathy&ie=utf-8&oe=utf-8)

Google helpfully tells us that the root of the word is from two Greek words:

- em: "in"
- pathos: "feeling"

Pathos is another interesting word. As technical people, we like to think that we're rational actors and that, by extension, all software and technical issues have a simple rational explaination. This plays to one interpretation of pathos (compared to logos) when we talk about our software.

Obviously if the software doesn't work for the user, there's a reason. Logos for engineers is almost the equivalent of "works for me" and if I can just explain that to the user, they'll understand. There's no room for emotion here, just logic.

## Empathy as it applies to software
When we go back to the previous examples, we ask "who is the target of the empathy if empathy exists at all?

In the case of Logstash, the target is clearly the user. If the user has a bad time, it's not the user's fault it's logstash's fault.

In the case of Systemd and PulseAudio, I would say that if there is any empathy at all, it's for the people that have to support the software and not the people that have to use it (yes, I said "have to").

In the case of the Linux kernel, however coarse, the empathy is for the user but ALSO for the creators and supporters of the software. Just not for one specific creator in this case. You don't break the user experience. You also don't put the people who have to maintain your code in a bind by breaking things.

## The user is not always right
I said this once on twitter and I want to say it again:

_*You don't have to accept that the user is always right but you have to accept that there is a user.*_

I want to go back to what the Logstash "motto" is. Logstash never says "the user is always right". If you talk to Jordan about this, the expanded version of that statement works more like this:

"If a user has a "bad time", then Logstash is at fault. It may be a legitimate bug. Maybe logstash isn't the right tool for the job in this case or maybe Logstash simply can't do what the user wants. Regardless, that's not the user's fault. It's the fault of the Logstash documentation or the fault of the community in its interactions with the user."

Software has an interesting facet. It's not uncommon in other areas of "creation" but it's interesting none the less. I want to use my rants on PaaS software as an example.

The most obvious target of PaaS software is the developer who will use the software. There's nothing wrong with this. When we create, we have a target in mind. The goal of PaaS itself is to make the developer experience as simple and awesome as possible.

This works great for Heroku and hosted options because the provider of the software doesn't have to worry about anyone other than the developers.

But when you create a PaaS product that has to run onpremises, you now have ANOTHER userbase that you never thought about - the people who have to OPERATE the software to provide it to the original target market. You don't get to punt on that. You have to address it. You can't hide the ugly bits from the people who have to operate it.

We're currently dealing with this with our OWN product now. We are investing considerable resources in not just the user experience in the traditional sense but also the user experience of the users who have to operate the software.

## Focusing on ALL the users (including the ones who didn't choose to be users)
Writing software is hard but it's even harder to maintain software. You simply cannot be a software maintainer and not have empathy for the people who use your software. If you don't clearly manage expectations about the scope of your software, then the fault for a user using it incorrectly pretty much falls on your shoulders. This can be as simple as saying in the README:

_"This software has been tested under X, Y and Z scenarios. It was created to do foo and it was created to do foo under these circumstances. I'm unable to test the software on bar (or 'I have no interest in testing on bar as it's not intended to run on bar'). I'm willing to accept patches to run this software on bar but this is a personal project and the primary focus is doing baz"_

A little communication goes a long way to managing expectations. I throw random crap up on github all the time but I always try to create a README that states things very clearly as to what state the code is in. I'm also always willing to turn something over to someone else to maintain rather than have the user have a bad time.

For the record I suck at this. When people email me about problems with something I wrote, I feel terrible and I feel REALLY terrible when I simply don't have the time to even reply to the email.

And really that's the crux of software empathy. We must not only have empathy for the target user or even the unexpected user. We must also go beyond the experience we want them to have with the software but also the scope of the problems they have.

When people open support tickets for OSS, ask a question on IRC or on a mailing list they may be in a very bad situation. They could be experiencing downtime, loss of data or any number of problems. They are stressed, possibly losing money and may even be just coming to grips with a really bad decision in using your software made by someone who doesn't even work for/with them anymore. At this point they just need to stop the bleeding. Again, you can be firm and simply say "I'm sorry that you're in this position but unfortunately there's not much we can do" but that goes a long way to helping them move on to the next phase of troubleshooting.

# Empathy has a place in software
Empathy is hard enough outside of software. Human beings are messy. We retreat to our machines where things are (largely) logical. If the there's a problem, either it's a logic problem or a bug. It's all solvable. The end-user is clearly defined on a card on a kanban board and we just have to do some user studies. 

Human problems don't always have clear solutions and we can't always predict how our software will be used. We just need to accept that and act accordingly when it does happen.
