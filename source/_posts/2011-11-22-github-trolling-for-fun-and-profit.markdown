---
layout: post
title: "Github Trolling for Fun and Profit"
date: 2011-11-22 21:43
comments: true
categories: [Github, Open Source, 0MQ]
---
Last Friday was a pretty crappy day.
<!-- more -->
I'm a fairly _active_ Twitter user.

Patrick has joked that it's as if I have Twitter wired directly to my brain. It's not far from the truth.
I like to engage people and normally Twitter is great medium for engaging folks. Unfortunately, the message size limit makes Twitter an imperfect medium for involved discussions.

I know better but sometimes I forget.

Anyway, last friday I realized near the end of the day that I had pretty much gone off the rails. If I wasn't bitching about Maven and Java, I was involved in random discussions about the SaltStack project. Combine that with normal inane bullshit and I somehow managed to pull off a 60+ tweets. It took a comment by `roidrage` on IRC to point out that I really needed to calm down.

With that, I declared communication blackout for the weekend. I decided to go to happy hour and spend the weekend just having fun with the family. It was awesome. So sorry for the sheer number of people I managed to piss off on Friday.

# Trolling Github
One of the things I also decided to do not stress about making time to hack. I knew that if I got working on one of my projects, I would totally stay distracted thinking about it.

So I went trolling. On Github.

I was just poking around Github, when I saw in my feed that some commits were done to the [user's guide for ZeroMQ](https://github.com/imatix/zguide). Because I have a serious geek woody for ZeroMQ, I got wrapped up reading the guide and looking at some of the more advanced examples. I've got some ideas I want to implement in Ark and Noah that involved 0mq so I figured it would be time well spent.

Now if anyone has bothered to read the [zguide](http://zguide.zeromq.org/), you'll know that one of the BEST parts is the code samples. Seriously. They have examples for all of the architectures in almost every language. I don't know a single goddamn person who knows [Haxe](http://en.wikipedia.org/wiki/HaXe), but there are examples in the guide for Haxe. You can see an example of what I'm talking about [here](http://zguide.zeromq.org/page:all#Divide-and-Conquer).

Notice at the bottom the list of examples for languages. If you mouse over the last entry, many times you'll get multiples highlighted. This means that chunk of highlighted languages doesn't have any examples written.

I noticed that quite a few of the advanced ones didn't have Ruby versions. I started back at the beginning of the guide until I found the first one that didn't have a Ruby example - the `interrupt` example.

# Challenge Accepted
So here I am - resolved not to work on any of my own projects and knowing that I didn't have time to get involved with something TOO heavy. I decided to fork the guide and start adding missing Ruby examples.

Now I only got two example done the entire weekend. This mainly revolved around how limited my time was but also around getting REALLY comfortable with [ffi-rzmq](https://github.com/chuckremes/ffi-rzmq). I wanted to make sure that the examples I wrote had the write mix of idiomatic Ruby and yet explicit enough for someone who didn't know the specifics of `ffi-rzmq`.

One that I really struggled with was this one:

[https://github.com/imatix/zguide/commit/4c231d1023819152813fad09a45458bd33cb02a9
](https://github.com/imatix/zguide/commit/4c231d1023819152813fad09a45458bd33cb02a9)

If you get familiar with the zguide, you'll see a lot of references to `zhelpers`. It's really just a bunch of boilerplate code that helps keep the actual examples to a nice consumable chunk size. There was not a `zhelpers` for the Ruby examples. I looked at the others to get an idea of what kinds of things were in there. In relation to the `identity` examples, there was a dump helper that just dumped the contents of a message. If you look at the [Python](https://github.com/imatix/zguide/blob/master/examples/Python/zhelpers.py) and [C](https://github.com/imatix/zguide/blob/master/examples/C/zhelpers.h) examples for `dump`, you'll see how they pull the identity of the message out. An interesting comparision, is how the [Scala](https://github.com/imatix/zguide/blob/master/examples/Scala/utils.scala) version of `dump` works.

Instead of focusing on duplicating the strategy employed by the C and Python versions, I went with something that fit how `ffi-rzmq` works a bit more. I realized that the point was not the content of the helpers so much as the end result, showing that 0mq would generate an identity for a message if one wasn't explcitly provided.

I'm quite sure that at some point, ZMQ::Message objects will get an attribute accessor to simply return the identity. Right now the code base is under a bit of a refactor.

# Call to Action
I really want to encourage others to do something like this. No pressure. Just troll Github. Look at some projects that are interesting. Look at the open issues. Fork, fix a bug or two and make some pull requests. After that, go on your merry way. No obligations. At worst, you've spent some time sharpening your skills. At best, however, you've made a lasting contribution.

And shit, it doesn't even have to be a code contribution. If you can grok the project well enough, add some wiki pages.

I dunno. Github just has this amazingly easy flow for contribution. Do unto others and all that.

