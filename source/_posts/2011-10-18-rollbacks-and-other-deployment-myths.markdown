---
layout: post
title: "Rollbacks and other deployment myths"
date: 2011-10-18 00:35
comments: true
categories: [Deploy, DevOps, Myths]
---

I came across an interesting post today via HN. I'm surprised (only moderately) that I missed it the first time around since this is right up my alley:

[Why are you still deploying overnight?](http://briancrescimanno.com/2011/09/29/why-are-you-still-deploying-overnight/)

I thought this post was particularly apropos for several reasons. I just got back from DevOpsDays EU **AND** I'm currently in the process of refactoring our deploy process.

I'm breaking this up into two parts since it's a big topic. The first one will cover the more "theoretical" aspects of the issue while the second will provide more concrete information.

<!--more-->
# Myths, Lies and other bullshit

Before I go any further, we should probably clear up a few things.

Understand, first and foremost, that I'm no spring chicken in this business. I've worked in what we now call web operations and I've worked in traditional financial environments (multiple places). If it CAN go wrong, it has gone wrong for me. Shit, I've been the guy who dictated that we had to deploy after hours.

Also, this is not a "tell you what to do" post.

So what are some of the myths and other crap people like to pull out when having these discussions?

- Change == Risk
- Deploys are risky
- Rollbacks
- Nothing fails
- SLAs

There's plenty more but these are some of the key ones that I hear.

## Change is change

There is nothing inherent in change that makes it risky, dangerous or anything more than change. Change is neither good or bad. It's just change.

The idea that change has a risk associated with it is entirely a human construct. We have this false assumption that if we don't change something then nothing can go wrong.
At first blush that would make sense, right? If it ain't broke, don't fix it.

Why do we think this? It's mainly because we're captives to our own fears. We changed something once, somewhere, and everything went tango uniform. The first reaction after a bad experience is never to do whatever caused that bad experience again. This makes sense in quite a few cases. Touch fire, get burned. Don't touch fire, don't get burned!

However this pain response tends to bleed over into areas. We deployed code one time that took the site down. We changed something and bad things happened. Engage overcompensation - We should never change anything.

## Deploys are not risky

As with change, a deploy (a change in and of itself) is not inherently risky. Is there a risk associated with a deploy? Yes but understand that the risk associated with pushing out new code is the culmination of everything you've done up to that point.

I can't even begin to count the number of ways that a deploy or release has gone wrong for me. Configuration settings were missed. Code didn't run properly. The wrong code was deployed. You name it, I've probably seen it.

The correct response to this is **NOT** to stop doing deploys, do them off-hours or do them less often. Again with the overcompensation.

The correct way to handle deployment problems is to do MORE deploys. Practice. Paraphrasing myself here from an HN comment:

> Make deploys trivial, automated and tolerant to failure because everything fails.

"Release early, release often" isn't just about time to market. The way to reduce risk is not to add more risky behavior (introducing more vectors for shit to go wrong). The way to reduce the risk associated with deploys is to break them into smaller chunks.

You need to stop thinking like Subversion and start thinking like Git.

One of the reasons people don't feel comfortable performing deploys during the day is because deploys are such a big deal. You've got to make deploys a non-issue.

## Rollbacks are a myth

Yes, it's true. You can never roll back. You can't go back in time. You can fake it but understand that it's typically more risky to rollback than rolling forward. Always be rolling forward.

The difficulty in rolling forward is that it requires a shift in how you think. You need to create a culture and environment that enables, encourages and allows for small and frequent changes.

## Everything fails. Embrace failure.

It amazes me that in this day and age people seem to think you can prevent failure. Not only can you not prevent it, you should embrace it. Learn to accept that failure will happen.  Often spending your effort decreasing MTTR (mean time to recovery) as opposed to increasing MTBF (mean time between failures) is a much better investment. Failure is not a question of 'if' but a question of 'when'.

Systems should be designed to be tolerant of failure. This is not easy, it's not always cheap and it can be quite painful at first. Failure sucks. Especially as systems administrators, we tend to personalize a failure in our systems as a personal failure.

The best way to deal with failure is to make failure a non-issue. If it's going to happen and you can't prevent it, why stress over trying to prevent it? This absolutely doesn't mean that you should do some level of due dilligence. I'm not saying that you should give up. What I'm saying is that you should design a robust system that handles failures gracefully and returns you to service as quickly as possible. It's called fault TOLERANCE for a reason.

## SLAs are not about servers

SLAs are in general fairly silly things. Before you get all twisted and ranty, let me clarify. SLAs have value but the majority of that value is to the provider of the SLA and not the person on the other end. SLAs are a lot like backup policies.

Look at it this way. I'm giving you an SLA for four nines of availability. That allows me to take around 50 minutes of downtime a year. Of course you assume that means 50 minutes spread over a year. What you fail to realize is that I can take all 50 minutes at once and still meet my SLA. Taking 50 minutes at one time is much more impacting than taking ten 5-minute outages. What's worse is I can take that downtime not only in one chunk but I can take it at the worst possible time for you.

The other side of SLAs is that we tend to equate them with servers as opposed to services. The SLA is a *Service Level Agreement*. Not a *Server Level Agreement*. Services are what matters, not servers. 

When you start to equate an SLA with a specific server, you've already lost.

# Wrap up and part 2

As I said, this topic is too big to fit in one post. The next post will go into specifics about strategies and techniques that will hopefully give you ideas on how to make deploys less painful.
