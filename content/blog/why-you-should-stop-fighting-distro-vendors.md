+++
title = "Why You Should Stop Fighting Distro Vendors"
date = "2012-03-16"
slug = "2012/03/16/why-you-should-stop-fighting-distro-vendors"
Categories = []
+++

Recently I saw a tweet from [Kohsuke Kawaguchi](https://twitter.com/#!/kohsukekawa/status/180717301795008512) that really got me frustrated.

<!-- more -->
I've addressed this topic a bit before [here](http://lusislog.blogspot.com/2010/09/distributions-and-dynamic-languages.html). At the time it was addressing specifically dynamic languages. However the post that Kohsuke wrote (and the post that inspired it) have led me to a new line attitude.

**Don't bother trying to get your packages into upstream vendor distros**

# Wait. What? Let's step back a sec
Let me clarify something first. System packages are a good thing. The hassle has always been with BUILDING those packages. It was simply easier to build the software on the machine and install to `/usr/local/` than to try and express anything more than the most moderately simple application in RPM or DEB build scripts:

- If what you are packaging has dependencies not shipped with the OS, now you've got to package those
- If your dependency conflicts with a vendor-shipped version, you're screwed.
- If your dependency is a language runtime, give up.
- If your dependency is a specific version of python, just go into another line of work.
- If it's a distro LTS release, just don't bother

# Ahh but we can work around this!
Yes, you're right. We now have tools like [fpm](https://github.com/jordansissel/fpm) that take the pain out of it! Maven has had plugins that generate rpms and debs for you for a while now. Things are looking up! Let's just use those tools.

So now you think, I'll just get these things submitted to Debian....

**KABLOCK**

I could rant a bit about Debian's packaging policy but it's addressed in the posts above. So maybe the Fedora people are more flexible?

![Imgur](http://i.imgur.com/px5ug.png)

**WAT**

So here we have the two major distros that won't even consider your package unless you give the end-user the "freedom" to make your application unusable. Essentially you are told if you want your package to be included in upstream then you have to make sure they can swap out `libfunkytown.so.23` with `libfunkytown.so.1`.

But maybe your application doesn't work on that version. So maybe you think, I'll just vendor ALL the things and shove it into `/opt` or `/usr/local`? Yeah that doesn't fly either (for various reasons). 

The point is that you'll probably never be able to get your package included upstream because you'll never be able to jump through the hoops to do it.

# So stop trying
I know, I know. It would be awesome if you could tell users to just `yum install kickass` or `apt-get install kickass` but it's not worth it for several reasons as enumerated above.

Distributions are not your friend. One could argue that its not thier job to be your friend. I would even agree with that argument. The distros have (or at least SHOULD have) an allegience to their user base. My argument is that position is directly opposed to your needs as a software provider.

## Things you should not do

- Waste your time trying to ensure that your software works on some busted as old version of libfunkytown that won't get upgrade for 7 years.
- Waste your time breaking your application into 436 interdependent subpackages just to please upstream
- Ignore the prexisting dependency management ecosystem of your language of choice (especially if it works)

## Things you should do

- Use your language's preexisting dependency management system to collect all your dependencies
- Rebar, bundle, virtualenv, mavenize, fatjar whatever ALL the dependencies
- Use FPM or some homegrown script to create a monolithic rpm or deb of your codebase that installs to `/opt/appname`
- Make these packages available to your users on your download site
- Alternately, create a repo and repo config file they can use to stay up to date

You will be happy. Your users will be happy. The distros can go lick themselves. We have reached something of a crossroads. As I argued in the previous post, the concept of a distribution is becoming somewhat irrelevant. Distros are more concerned about politics and making statements and broken concepts like software that doesn't need upgrading for 7 years (or even 2 years) than providing a framework and ecosystem that encourages developers to target software at it.

If someone takes up the noble cause of trying to get your software included upstream, I would go so far as to make it plainly clear on whatever communication you have that you simply cannot support an unofficial repackaging of your software. Be polite. These are still your potential userbase. Simply state that those were not created by you and that the official packages are here.

# A case in point
What I'm suggesting you do is not unheard of and honestly is the most tenable long term path for your users. Look at projects like Vagrant, Chef and Puppet among others. All of these tools are "owning their availability" the right way and are arguably providing better end user experiences than getting included in upstream could provide. In fact the experience of official packaging is above and beyond trying to do it yourself. As it should be.
