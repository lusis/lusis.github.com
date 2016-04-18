---
layout: post
title: "Stop fighting distros - part 2"
date: 2013-09-23 23:27
comments: true
categories: 
---

I'm a pretty harsh person. My wife and I have this discussion all the time about how things I say get interpreted. As the communicator, this responsibility lies squarely on my shoulders.

So before I start, I don't "hate" distributions or the packaging format they use or the people doing the work. To this even of you who toil to track countless security reports or maintain some software package in an upstream repository because of your love for that software, here's to you.
<!-- more -->

## Packaging policies
In general I hate very few things. When I say I hate something usually hate is even too strong of a word. 

I **really** hate stupid policies. I hate stupid policies that are predicated on the shape of a reality that either no longer exists or never existed in the first place. I hate policies that never evolve to the reality of the world. I hate policies that throw pragmatism and common sense out the window. I also really hate ego-driven policies.

What kinds of things fit that bill?

- *most* corporate security policies
- Various parts of PEP8
- a oddly large amount of government regulation and legislation
- FHS

And yes, distro packaging policies (mostly) fit that bill.

I've said this a lot recently (and I stand by it):

{% blockquote %}
Distribution packaging policies are not designed for people who package software
{% endblockquote %}

Packaging policies exist first and foremost for the benefit of the distribution. This isn't a bad thing or a good thing - it's just a thing.

Let's compare a few packaging policies from different distros. I spent most of my valuable time this evening between fighting a 3 year old who didn't want to go to sleep reading these.

- [Debian](http://www.debian.org/doc/debian-policy/)
- [Fedora](https://fedoraproject.org/wiki/Packaging:Guidelines?rd=Packaging/Guidelines)
- [Arch](https://wiki.archlinux.org/index.php/Arch_Packaging_Standards)
- [Gentoo](https://devmanual.gentoo.org/general-concepts/index.html)

Ignoring for a minute the sheer verbosity of some of these guidelines (I think Arch wins at simplicity and pragmatism), they all share a few common key themes:

- Break shit up into multiple packages
- It needs to be able to be built from source
- put X here, Y here, Z here
- ONLY 1 VERSION IS ALLOWED
- *These guidelines exist to make maintaining the distribution easier*

Note that of all the distributions Debian is quite possibly the most offensive at this. Fedora follows a close second.

I want to address a few of these though I might have done so in the previous version of this post. I'm also going to skip the "build from source" point because I have no real problem with that on a general level.

### Break shit into multiple packages
There are two arguments here that tend to crop up in support of this:

- security
- disk space

Disk space concerns are entirely subjective. I'm not hurting for disk space and I haven't been for quite some time. I'm anal about which packages I install, not because of disk space, but because I want to have to worry about as few security announcements as possible. Quite honestly this argument is the distro equivalent of PEP8's column width. It is not my concern how many ISOs the distribution has to span across. I either install from a private mirror or netboot off the internet. Regardless I haven't put an actual cd in a system in a VERY long time.

But the one that gets me is the "security" argument. The basis for this is that you can supposedly immediately get the benefit of a fix to, say openssl, in all packages that link against openssl.

This is bullshit.

For that to REALLY be true, you have to turn up the white noise reality distortion field pretty high. 

Let's take a vulnerability in a library that everything in the system links to like openssl. There have been [three CVEs](http://www.openssl.org/news/vulnerabilities.html) against openssl this year (2 of which affect versions that most distributions use today).

For a distribution to get a fix in place for openssl, they should ostensibly be testing EVERY SINGLE PACKAGE that links against openssl. In reality, you can get by with lesser testing of that fix assuming the ABI doesn't change but that's still a pretty big risk to take. I'm honestly not sure (and would love feedback) from distros about exactly what the test cycle is for these cases.

But let's assume they do test that. If the vulnerability was "responsibly disclosed" it's possible they've had time to do that but if not, you have a case where every single package on your system that links against openssl could be vulnerable.

So at most, what you have is a wash. I would actually argue that a package that brings its own deps is better off in this case.

Let's also not ignore the case that **RedHat 5 STILL USES RUBY 1.8.4 WHICH DOES NOT GET ANY FIXES UPSTREAM WHATSOEVER.** I don't think ANY ruby 1.8 version is getting fixes anymore. You are ENTIRELY dependent on Redhat to not only backport fixes from different versions of Ruby but also make sure they don't break any other Ruby packages the distro ships. In some cases, Redhat will actually have to figure out how to patch that version of Ruby.

### File placement
The whole "put X here, Y here, Z here" is really about consistency more than anything. I don't entirely disagree with it but it really is a matter of preference. I personally like my configs to live close to my application. I hate bouncing around the filesystem.

### Version restrictions
This is where it gets ugly and when combined with the "use existing libraries" model that the whole policy is downright combative to people who write software.

I've said previously that various language communities have coalesced around their own toolchains. One thing that's pretty common, however, is that those communities allow for multiple concurrent versions of a given package. How they isolate and define which version to use is specific to the language but it exists.

Shoehorning that into the vendor policy around versioning simply does not work.

Let's take a java application. I need version 42 of `commons-dingleberry`. The vendor has packaged version 39. If this was debian, they would tell me I need to make my application work with version 39. What is more likely the case is that my application simply won't ever be included in upstream because I'm not about to spend my free time that I contribute to an opensource project trying to make my software work with a version of a library that may simply not have the functionality I need.

This is really quite hilarious when you take something like Logstash which shades all the java deps it has in to its own jar. These packaging guidelines (and from what was communicated by someone attempting to get it into Fedora) requires someone to actually CHANGE Logstash such that the ElasticSearch version is its own package. Each version of Logstash is actually dependent on a specific version of ElasticSearch (due to the ability of Logstash to run ES embedded or as a non-data cluster member). Giving a user the "freedom" to switch ES versions actually creates a shitty experience for the user.

## And that's really the crux of it
Jordan has asked people repeatedly to NOT try and get Logstash into upstream repositories. Here's why:

<blockquote class="twitter-tweet"><p><a href="https://twitter.com/lusis">@lusis</a> <a href="https://twitter.com/robynbergeron">@robynbergeron</a> my fear is having the next 10 years spent telling rhel users &quot;sorry, logstash 1.2 is X years old, please upgrade&quot;</p>&mdash; @jordansissel (@jordansissel) <a href="https://twitter.com/jordansissel/statuses/382251492775710720">September 23, 2013</a></blockquote>
<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>

Jordan makes a valid point. And it's not just Logstash. The software that people are attempting to run today (yes even in the stodgiest of enterprises) changes too often to ever be valid as packaged in upstream. No software I have ever cared about running outside of the core OS was ever present in a distro's upstream repository. Running CentOS or RHEL? EPEL is the FIRST thing I have to add to the system.

# Remediation
Nothing kills the relevance of a distribution faster than how much effort someone has to go to for it to be usable. Why did Ubuntu start to "win"? I see two things:

- 2 year LTS cycle
- The addition of PPAs

Two years, while still an eternity, is frequent enough to be able to work in relevant changes in the industry. PPAs provide an outlet for someone to provide packages in a way that is largely well integrated into the distribution workflow. However even PPAs are not keeping up with the pace these days. I'm curious if an omnibus-style package would ever work in a PPA (assuming you would go to the effort of converting an omnibus project into a source deb.

# Software authors share the responsibility
Using the vendor's package format as an excuse not to provide packages doesn't fly anymore. FPM has eliminated that. You don't have to know a single goddamn thing about the RPM spec format or  deb control files.

And beyond that, with omnibus, you don't even need to worry about dependencies on the system. By default and omnibus project will build packages for every current LTS release of Ubuntu and RHEL/RHEL clones. There's not even a need to create a proper apt or yum repo. With an omnibus package you don't have any external dependencies.

# What can distribution vendors do?
I've talked at length about this issue with a few folks but none so passionate about it as [Sam Kottler](https://twitter.com/samkottler) and [Robyn Bergeron](https://twitter.com/robynbergeron). Robyn actively sought me out at PuppetConf a few years ago. I think we chatted for over an hour on distribution relevance.

In general I think what would help most at this point is for distributions to provide an avenue for authors to provide packages that don't meet strict guidelines. If Opscode wants to provide omnibus'd chef clients in /opt/chef without the user having to curlbash the package, that would awesome.

There is the concern to the user but my crusade in technology has been to not treat the user like a 3 year old. Give users enough clear information about risk and let them make the decision to install a package. Communication goes a long way.

# More to come
I'm going to have more to write about this topic. It's a never-ending source of fuel really. Thanks for reading.

