+++
title = "Why Monitoring Sucks"
date = "2011-06-05"
slug = "2011/06/05/why-monitoring-sucks"
Categories = []
+++

_(and what we're doing about it)_

About two weeks ago someone made a tweet. At this point, I don't
remember who said it but the gist was that "monitoring sucks". I
happened to be knee-deep in frustrating bullshit around that topic and
was currently evaluating the same effing tools I'd evaluated at every
other company over the past 10 years or so. So I did what seems to be
S.O.P for me these days. I started something.
<!--more-->

# But does monitoring REALLY suck?

Heck no! Monitoring is AWESOME. Metrics are AWESOME. I love it. Here's
what I don't love: - Having my hands tied with the model of host and
service bindings. - Having to set up "fake" hosts just to group
arbitrary metrics together - Having to either collect metrics twice -
once for alerting and another for trending - Only being able to see my
metrics in 5 minute intervals - Having to chose between shitty interface
but great monitoring or shitty monitoring but great interface - Dealing
with a monitoring system that thinks **IT** is the system of truth for
my environment - Perl (I kid...sort of) - Not actually having any real
choices

Yes, yes I know:

 > You can just combine Nagios + collectd + graphite + cacti +
 > pnp4nagios and you have everything you need!

Seriously? Kiss my ass. I'm a huge fan of the Unix pipeline philosophy
but, christ, have you ever heard the phrase "antipattern"?

# So what the hell are you going to do about it?

I'm going to let smart people be smart and do smart things.

Step one was getting everyone who had similar complaints together on
IRC. That went pretty damn well. Step two was creating a github repo.
Seriously. Step two should ALWAYS be "create a github repo". Step three?
Hell if I know.

Here's what I do know. There are plenty of frustrated system
administrators, developers, engineers, "devops" and everything under the
sun who don't want much. All they really want is for shit to work. When
shit breaks, they want to be notified. They want pretty graphs. They
want to see business metrics along side operational ones. They want to
have a 52-inch monitor in the office that everyone can look at and say:

 > See that red dot? That's bad. Here's what was going on when we got
 > that red dot. Let's fix that shit and go get beers

# About the "repo"

So the plan I have in place for the repository is this. We don't really
need code. What we need is an easy way for people to contribute ideas.
The plan I have in place for this is partially underway. There's now a
*monitoringsucks* organization on Github. Pretty much anyone who is
willing to contribute can get added to the team. The idea is that, as
smart people think of smart shit, we can create new repository under
some unifying idea and put blog posts, submodules, reviews,
ideas..whatever into that repository so people have an easy place to go
get information. I'd like to assign someone per repository to be the
owner. We're all busy but this is something we're all highly interested
in. If we spread the work out and allow easy contribution, then we can
get some real content up there.

I also want to keep the repos as light and cacheable as possible. The
organization is under the github "free" plan right now and I'd like to
keep it that way.

## Blog Posts Repo

This repo serves as a place to collect general information about blog
posts people come across. Think of it as hyper-local delicious in a
DVCS.

Currently, by virtue of the first commit, Michael Conigliaro is the
"owner". You can follow him on twitter and github as @mconigliaro

## IRC Logs Repo

This repo is a log of any "scheduled" irc sessions. Personally, I don't
think we need a distinct #monitoringsucks channel but people want to
keep it around. The logs in this repo are not full logs. Just those from
when someone says "Hey smart people. Let's think of smart shit at this
date/time" on twitter.

Currently **I** appear to be the owner of this repo. I would love for
someone who can actually make the logs look good to take this over.

## Tools Repo

This repo is really more of a "curation" repo. The plan is that each
directory is the name of some tool with two things it in:

-   A README.md as a review of the tool
-   A submodule link to the tool's repo (where appropriate)

Again, I think I'm running point on this one. Please note that the
submodule links APPEAR to have some sort of UI issue on github. Every
submodule appears to point to Dan DeLeo's 'critical' project.

## Metrics Catalog Repo

This is our latest member and it already has an official manager! Jason
Dixon (@obfuscurity on github/twitter - jdixon on irc) suggested it so
he get's to run it ;) The idea here is that this will serves as a set of
best practices around what metrics you might want to collect and why.
I'm leaving the organization up to Jason but I suggested a
per-app/service/protocol directory.

# Wrap Up

So that's where we are. Where it goes, I have no idea. I just want to
help where ever I can. If you have any ideas, hit me up on
twitter/irc/github/email and let me know. It might help to know that if
you suggest something, you'll probably be made the person reponsible for
it ;)

**Update!**

It was our good friend Sean Porter (@portertech on twitter), that we
have to thank for all of this ;)

  [![image](https://lh5.googleusercontent.com/-O6mNvCvCPyU/TexPV1P9YaI/AAAAAAAAAWk/7ZQ8BkXUyn8/s144/monitoring-sucks.png)](https://picasaweb.google.com/lh/photo/Zi1k9F_7lBKjcN8dtJlXXQ?feat=embedwebsite)
  From [Public Photos](https://picasaweb.google.com/lusisjv/PublicPhotos?feat=embedwebsite)

**Update (again)**

It was kindly pointed out that I never actually included a link to the
repositories. Here they are:

[https://github.com/monitoringsucks](https://github.com/monitoringsucks)
