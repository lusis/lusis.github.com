---
layout: post
title: "Go for System Administrators"
date: 2013-08-11 15:44
comments: true
categories: ["golang", "development", "operations"]
---


{% blockquote %}
If I never directly touch a Go concurrency primitive, I'm convinced I'm going to write all my cli apps with it just for ease of deployment.
{% endblockquote %}
<!-- more -->

This is something I said the other day. I figured it deserved a more detailed blog post.

## NKOTB
Most people who know me professionally know two things about me:

- I'm fairly pragmatic and somewhat conservative about technology decisions
- I'm a language tourist

This second one is something Bryan Berry attributed to me in an early FoodFight episode. What's interesting is the two things seemingly conflict.

I love learning new programming languages. This comes as a pretty big shock to me on a regular basis because I'm not a professional programmer. I didn't go to college for programming (I actually didn't go to college at all). My career in IT has been pretty much 100% focused on the area of operations. Anything I've ever touched - qa, dba, dev - has always been from that lens and to satisfy some need operationally.

So it's weird that I find myself 18 years later having a working knowledge of ruby, python, perl, java and a few other languages to a lesser degree. Mainly I come to new languages to scratch an itch.

This leads me to picking up Go.

If you haven't heard of Go, there are countless articles, blog posts and a shitload of new tooling written in it. The latest batch of hotness around linux containers and new deployment models (docker) is based on Go. There are also quite a few other "big" name projects built in Go as well - packer, etcd. Mozilla is doing all new internal tooling in Go (as I understand it) and quite a few folks are switching to it.

Mind you I don't pick up languages based on popularity. I don't care for JavaScript and Node at all, for instance. Originally I had no interest in Go either. I figured it was another Google experiment that was more academic than anything else. Besides, if I had to get a handle on a c-like language, why not just learn C and be done with it?

I actually attempted that route working on a PAM module for StormPath. While it was somewhat satisfying, it was ultimately VERY frustrating. 

## So why Go now?
One of the reasons I decided to give Go another shot was that it appeared to be around for the long haul after all. That made at least a contender for me.
But then some of the tooling I was using operationally was being built in Go. Since I wanted to be able to fix issues in those tools (especially considering they were new projects which would surely need fixes) I really needed to pick up on the language.

However one tool really pushed me that last step - [etcd](https://github.com/coreos/etcd).

You can read up on etcd yourself but if you know my history with [Noah](https://github.com/lusis/Noah), you realize WHY I have such an interest in this.

What surprised me was when I decided that I'd probably be writing a lot of tooling myself in Go.

## On Pragmatism
All the internal tooling my team develops at Dell Enstratius is written in Python. This was a pragmatic choice for us:

- It's the least common denominator on platforms our product supports (So it will always be on customer systems)
- It's rigid in the right ways for new programmers (of which we had quite a few on our team)
- Regardless of skill level with Python, you can usually look at someone else's code and follow it thanks to the previous item

Why didn't we go with Ruby considering I was personally much stronger at Ruby and we had some Ruby experience via Chef internally?

- Have you seen the state of Ruby on distros?
- We didn't want to conflict with any possible customer tooling using Ruby
- Not enough rigidity for the new folks
- As comfortable as I am at Ruby, because of the flexibility of the language and metaprogramming, it can be downright impossible to navigate someone else's code

Our team weighed all the options here and we all agreed on Python. I set out to write a library for accessing our API. This would give us a foundation for our tooling as well as serve as a reference project - with tests, project structure, bin scripts and the like - for new tooling.

Things are/were going great up until a recent situation with a customer. We try and minimize dependencies in our tooling for obvious reasons. However there are a few libraries that just make things SO much easier - [requests](https://github.com/kennethreitz/requests), [envoy](https://github.com/kennethreitz/envoy). We also like to use [Fabric](http://fabfile.org) to wrap some things up.

However we ran into a situation where a customer refused to let us pull packages in externally. So while we could "sneakernet" the bulk of our tools over, some things wouldn't work. Tracking down all the transitive deps and vendoring everything was a pain in the ass.

This is what lead to my statement above.

## Tooling in Go
Go, while not as tight a feedback loop as Python, is still pretty tight. Compilations happen fast and you can test fairly quickly. But the dependency issue is really the killer. It simply doesn't exist. I can take that binary I compiled and move it around with no problem without needing the runtime installed. There are lots of batteries included in the stdlib as well.

I can also compile that same code on osx, windows or linux with no modification. This bit us in Python with some of our deps as well.

As I said, while the tooling I'm currently writing has no need for any of the advanced concurrency stuff in go, it's nice that's it there out of the box should I want to use it.

## This isn't a switching story
We're not switching to Go for our tooling but I probably will. I'm already working on writing a wrapper for our API in Go so I can duplicate some of the tools. This will be really handy when I'm on a system where dependencies are limited. That's really what this post is about. 

If you're in operations, there is no reason you shouldn't learn Go.

The syntax is easy. The stuff that made C painful is largely hidden from you. Meanwhile you don't need to worry about what version of Python or Ruby is installed on your systems. It's a great language to use for bootstrap tools where you don't yet have your deps installed. It'll also help should you start adopting tools like docker, packer or etcd.

Give it a shot.