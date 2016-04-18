---
layout: post
title: "Review: Site Reliability Engineering"
date: 2016-04-17 00:24
comments: true
categories: 
---

This is a first for me. I've never reviewed a book before. There's an uncomfortable amount of arrogance I feel in doing so but I thought it was important.

<!-- more -->

Recently the [Site Reliability Engineering](http://shop.oreilly.com/product/0636920041528.do) book came out. This is the description directly from the O'Reilly site:

{% blockquote %}
In this collection of essays and articles, key members of Google’s Site Reliability Team explain how and why their commitment to the entire lifecycle has enabled the company to successfully build, deploy, monitor, and maintain some of the largest software systems in the world. You’ll learn the principles and practices that enable Google engineers to make systems more scalable, reliable, and efficient - lessons directly applicable to your organization.
{% endblockquote %}

Now I should give fair warning that I was turned off by the title of this book alone because I originally thought it was "Site Reliability Engineers". I've railed multiple times about my take on renaming the functions of operations to something else. In general I don't care about titles but what I care about is what those titles convey.

I stand by my assertion (and experience) that having a title of "devops" or "sre" is a bad idea. The inherent implication is that you're doing something different from operations which has yet to be shown as further from the truth.

Still, once I realized I had the title wrong I was sort of happy. I figured here was a book of how Google engineers for reliability. Sadly it wasn't as the title might have well been "Google SRE: Employee Orientation Guide"

## The style of the book
The style of the book isn't new. It's very similar to the format of the [webops](http://shop.oreilly.com/product/0636920000136.do) book published in 2010. (I'm being explicit with the date for a reason as dates are very important to this review.)

The book is a collection individual essays about various topics in how google does "site reliability engineering".
The book clocks in at 33 chapters each written by and reviewed by someone inside of Google. In fact the only non-Google input in the book really appears to be the forward by Mark Burgess.

I happen to like this style in the general but it falls apart pretty quickly when all the content is written by the same group of people from the same company. Compare this with the webops book or something like the amazing [AOSA](http://aosabook.org/en/index.html) series where the authors may work for competing companies.

This particular aspect is what made the book a hard read for me in light of my previously mentioned frustration with the concept of SRE as a distinct job. Almost every chapter reintroduced the concept of Google's SRE program and how "different" it was from operations.

This, in and of itself, I probably could have worked with except that every time the "definition" of SRE was repeated, the description was always self-aggrandizing and condescending.

The essays frequently shift person in frustrating ways and multiple times the second person appears to be other Google employees such as describing how you (a Google SRE person) should submit a project internally to have it be accepted by SRE peers.

All of these factors combine to have the book read more like a Google employee onboarding guide than anything else.

I'll get to this near the end but frankly the target audience appears to be Google itself while other parts don't actually have an audience that would pick up this book.

## How this review will work
[Dan Luu](http://danluu.com/google-sre-book/) has done a good approach here. He hits many of the key points in each chapter. I don't see any value in duplicating what he has written. My approach is going to be a bit more general in nature. I may repeat some of the things Dan says only because we came to the same conclusions on certain things.

I want to clarify a few things here first:

- Yes I came into reading this with a bias against it
- Nothing I write should be construed as a personal attack or criticism against the individuals who wrote that part.
- You should read the book for yourself and come to your own conclusions

# The good parts
There are several parts of this book that were really good reads. The chapters that were mostly technical in nature provide good insight into how you might build that "thing" yourself (something I'll get to later).

Chapter 4 on Service Level Objectives was really good and something I've been crowing about for a long time.

In particular the following chapters had lots of "meat":

- 20: Load Balancing in the datacenter
- 21: Handling Overload
- 22: Addressing cascading failures
- 23: Distributed Consensus for Reliability

Chapter 24 on distributed cron is short but as system-level cron is one of the most abused parts (or rather improperly used parts) of infras, it's worth reading.

Surprisingly, Chapter 26 on Data Integrity was an AMAZING read as it wrapped up the previous sections with an in-depth walkthrough of a Google Music recovery event. It was really well done as it took everything it wrote up to that point and gave a real-world example.

At the end of the book, there's a lot of good material as well that scales down very easily:

- Example Postmortem
- Example Launch Coordination Checklist
- Example Production Meeting Minutes
- Example Incident State Document

These will need to be munged for your org but they're great starting points if you don't already have these things in place.

The following section had good material but required wading through something I'll address further on as a core "issue" I had with the book:

- 10: Practical Alerting from Time-Series Data

I somewhat enjoyed some of the more soft-skills sections listed here but they did suffer from the "googleness" aspect a bit too much. There's still extractable meat in them:

- 12: Effective Troubleshooting
- 13: Emergency Response
- 14: Managing Incidents
- 15: Postmortem Culture

# The other parts
As I said earlier, I had issues getting through earlier parts of the book (and to some degree - later parts) due to an overriding theme that kept jumping out.

The first chapter of the book is what sent me into my caremad tweet fest. This was the section that attempted to layout how an "SRE" was different than an operations person in Google's mind.

Again, I came into this book with an established bias AGAINST the concept. Google's "perspective" of operations was pretty much the same as [that of Netflix](http://blog.lusis.org/blog/2012/03/20/it-sucks-to-be-right/).

What was different and the most frustrating was that the tone was MASSIVELY condescending. I captured a lot of it in my tweetstorm but the most often repeated statement was (paraphrased):

{% blockquote %}
Google SREs are different and better than system administrators because they write code and use software engineering perspectives instead of operations perspectives
{% endblockquote %}

The problem here is that the premise is flawed on several levels. It assumes:

- System Administrators don't write code
- System Administrators don't have a software engineer perspective

The other frustrating part is that the title of "site reliability engineer" implies that "site reliability" is the domain of a single team. That may not be true in practice but it doesn't change the implication.

My argument through all of the tweets was that:

- reliability is everyone's concern
- operations are not subhuman

As I stated in the netflix post, you are still doing operations and giving it a different name or applying a different "perspective" to it doesn't make it any less of operations.

And honestly, bringing a "software engineering" perspective to a problem isn't always the best or right solution. This is not intended as a slight against software developers but there was an overriding theme of NIH and overengineering in how many problems were approached.

# 10
You'll see this number dropped a lot in the book. The most explicit usage/reference to it is in the time-series section. This is one of the first sections of the book where it actually acknowledges something outside of Google.

The exact phrase used was:

{% blockquote %}
This chapter describes the architecture and programming interface of an internal monitoring tool that was foundational for the growth and reliability of Google for almost 10 years...
{% endblockquote %}

10 is the roughly the number of years ago much of the content in the book was "created".

This is important because what Google is giving us in this book is justification for something that arose from a need 10 years ago (and in some cases longer).

The best way I can sum this up is that the book was written about ten year old decisions written over the last year mostly ignoring the last five to six years of our industry.

The book is EXCEEDINGLY insular in nature. To some degree this is to be expected as it *IS* a book about Google. It goes overboard, however, in what seems like an attempt to attribute invention to Google. One of the more glaring parts of this was this particular line from Chapter 8 - Release Engineering:

{% blockquote %}
We accomplish this task by rebuilding at the same revision as the original build and including specific changes that were submitted after that point in time. We call this tactic _cherry picking_
{% endblockquote %}

I literally re-read that sentence multiple times to make sure I didn't misunderstand it. It may have been my existing frustration at this point with the book but cherry picking is not new. Google didn't not invent cherry picking of commits. It's great that you call the tactic "cherry picking" but so does almost everyone else in the world who used this amazing tactic.

The early part of the book is exceedingly tone deaf to what has happened in our industry outside of Google. It approaches everything as if it was a Google invention never before seen in the world and is now being handed down to the rest of us.

## Tablescraps
Let's go back to that 10 number for a moment. I've made the comment a few times that our industry is built on Google tablescraps to some degree. My original hope for this book was that we would get some NEW insight into Google's operations and engineering process. To some degree there was but the rest was stuff that we already knew.

The somewhat seminal post from Tim O'Reilly [Operations: The Secret Sauce](http://radar.oreilly.com/2006/07/operations-the-new-secret-sauc.html) is sort of applicable here. Interestingly enough this article is almost 10 years old as well. The thing is much of what comes out of Google still feels like they treat it as "secret sauce" while the rest of the industry has been telling us what was in that secret sauce down to the actualy ingredients list in the form of code.

Google frequently releases details of its internals after they've moved on from it which many times spurs a flood of clones. This is what I mean by tablescraps.

In 2015, Google released details of [Borg](http://research.google.com/pubs/pub43438.html). Of course by this point, the secret sauce of how companies like Heroku used containers at scale and the rise of friendly containers via Docker were already well and truly on the map.

Interestingly one could say that Kubernetes is the counter-example to this as the stated goal (as I understand it) is that k8s will eventually replace Borg. If that all proves out then this could be a turning point (along with some of the content in this book) that Google has come around to how the industry has changed in terms of openness.

# Applicability
The valley has tendancy (as an outside participant) of latching on to the every word that flows from the mouth of the "tech giants". One of these giants publishes a repo on github and next week everyone is rearchitecting to take advantage of it - and most of the times they don't even need it. Microservices are REALLY good examples of this.

A frequent problem I see is that "we" adopt this tools and techniques in the same way people adopted agile early on - half-assed. Yes, Kafka is an amazing tool. You know what though, LinkedIn WROTE the tool and has the capabilities and resources and motivation to keep it working - for them. You, dear startup, do not have the resources to run Kafka the same way LinkedIn does.

What do I mean by that? I mean Kafka is a great tool. I like it. Plenty of companies are running it to great success.

This is the general problem I have with the SRE book. Many of the things listed here are REALLY good ideas. No they aren't original (Coming to something by first principles does not mean you were the first to think of it).

The problem is that you should not be doing these things per se. Your company doesn't have a core competency on writing a FastPaxos implementation. Writing your own distributed consensus system or filestore does not get your product to market.

So much of the information in the SRE book makes PERFECT sense if you're Google. It even makes sense if you have a fraction of Google-scale problems.

The thing is...the people who have those problems (say Facebook, LinkedIn, Twitter, Netflix), already solved it their own way. They reached a point where spending money and resources on solving it made sense.

Your startup with a single-purpose application does not have the luxury of having your operations team say "I'm sorry you're over your error budget". Shit, you may not even have a REAL budget much less one for how many times your application is allowed to page someone.

Doing things the way a "tech giant" does them just because a tech giant is doing them is REALLY dangerous. You need to distill those things to something that actually makes sense at non "planet scale".

That's not to say you shouldn't be concerned with those things. The thing is....you should have already been concerned about them.

A good example is what Google says is "acceptable" availability. They make the (somewhat valid) point that 99.99% availability for a service is acceptable because external availability (a user on a mobile device for instance) is less than that.

This makes sense if that end user is one of millions of end users. Sure there's no reason to invest more in availability of your service (say, additional nodes to handle failure) when the likelihood of it being experienced is so small. The return isn't there.

However the standard company has 1 service and can afford 2 servers and requires a much higher availability just to even be running.

What I'm really driving at is not specific to this book but the fact that it *IS* a book that will get recommended and passed around as canon makes it have greater visibility and impact.

This was a problem with "The Phoenix Project" too. I liked the book. It was a good book and it had a lot of visibility at very high levels of organizations. That's great but the book did handwave a lot of things. It handwaved a lot of things to the point that it could be dangerously implemented.

# Is the book a good book
Yes. It's a good book. I don't think it stands up to the webops book. It's not groundbreaking if you've followed anything in the industry over the last 5-6 years.

Personally, I found the early parts of the book to be VERY insulting and (I used this once before) tone-deaf. There are parts where it tries to be "human" as when it addresses the issue of being on-call and handling a pager or the later section on "management". Unfortunately it still comes of as very mechanical and lacking humanity. This is not a new charge leveled at Google and some parts of the book bear that out. I think much of the problem here lies in the fact that it was also edited internally thus creating a feedback loop.

Teasing out what is applicable to you and your organization is doable but you definitely shouldn't take it as gospel.

# A personal note
When I started reading this book, I succumbed to one of the worst parts of being a human. I let my rage take over and used Twitter as a platform for snide comments. As I said later on, I stand by my assesments and the intent of the statements but the statements were unneccessarily harsh.

The people who worked on this book no doubt worked long and hard. If you've followed anyone like Katherine Daniels, Jennifer Davis or Lara Hogan as they've tweeted and talked about their books you feel joy along with them. Having some internet rando come along and shitbird their amazing work is unfair and cruel.

If anyone involved on the Google SRE book felt that way, I apologize.

As a deep consumer of Google technology, I am reassured after reading this book that Google is doing what is best for Google to provide and operate the services I consume. I don't agree with how they word it but I cannot argue with the results.
