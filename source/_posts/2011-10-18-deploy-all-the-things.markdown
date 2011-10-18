---
layout: post
title: "Deploy ALL the Things"
date: 2011-10-18 06:59
comments: true
categories: [Deploy, DevOps, Strategy, Tooling]
---

_This is part 2 in a post on deployment strategies. The previous post is located [here](http://blog.lusis.org/blog/2011/10/18/rollbacks-and-other-deployment-myths/)_

My previous post covered some of the annoying excuses and complaints that people like to use when discussing deployments. The big take away should have been the following:

- The risk associated with deploying new code is not in the deploy itself but everything you did up to that point.
- The way to make deploying new code less risky is to do it more often, not less.
- Create a culture and environment that enables and encourages small, frequent releases.
- Everything fails. Embrace failure.
- Make deploys trivial, automated and tolerant of failure.

I want to make one thing perfectly clear. I've said this several times before. You can get 90% of the way to a fully automated environment, never go that last 10% and still be better off than you were before. I understand that people have regulations, requirements and other things that prevent a fully automated system. You don't ever have to flip that switch but you should strive to get as close as possible.

# Technical Debt and Risk Management
Operations is an interesting word. Outside of the field of IT it means something completely different than everywhere else. [According to Wikipedia](http://en.wikipedia.org/wiki/Business_operations):

> Business operations encompasses three fundamental management imperatives that collectively aim to maximize value harvested from business assets 
> 1. Generate recurring income
> 2. Increase the value of the business assets
> 3. Secure the income and value of the business


IT operations traditionally does nothing in that regard. Instead IT operations has become about cock blocking and being greybeareded gatekeepers who always say "No" regardless of the question. We shunt the responsibility off to the development staff
