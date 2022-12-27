+++
title = "Omnibus Redux"
date = "2014-04-13"
slug = "2014/04/13/omnibus-redux"
Categories = []
+++

I've been a pretty big proponent of omnibus. I still think it's the right way to go but recent changes have removed the primary reason for recommending it
<!-- more -->

I did a lot of evangelism for [omnibus](https://github.com/opscode/omnibus-ruby) last year. Presentations, blogposts, a sysadvent article. It is/was a great tool however it no longer fits the primary usecase.

# Original workflow
Originally the biggest benefit to omnibus (outside of the core of what it did) was the Vagrantfile it generated. Because of this Vagrantfile, I could generate a project and publish the repo for anyone to use. That person didn't have to have any ruby tooling installed. They just needed vagrant and two plugins (`vagrant-omnibus` and `vagrant-berkshelf`).

They could check out the repository and just run `vagrant up` and the packages would be nice and neat dropped off in the `pkg` directory locally. I didn't see this as a problem workflow because I didn't listen to my own advise.

This is from the original generated README of a fresh omnibus project:

![original README](http://s3itch.lusis.org/1wzxjV.png)

This was the part of the workflow I was bullish on. In fact we went whole hog internally with this. Anyone could contribute because they could test locally with only the exising tools that we already had installed.

But that seems to have all changed with Omnibus version 3. Now omnibus requires a full ruby development environment just to do what it previously did with a `Vagrantfile` alone.
The reason for this is that instead of Vagrant, now omnibus uses [test-kitchen](https://github.com/test-kitchen/test-kitchen). Additionally it seems to ALSO require `Berkshelf` locally now.

This is where it gets really ugly.

It doesn't just require Berkshelf. It requires an UNRELEASED version of Berkshelf.

## Sidebar on tooling
I wanted to take a minute to talk a little bit about Chef tooling.

There is evidently a shift going on in the Chef community and I apparently haven't been keeping up. The Chef community flocked to Berkshelf for reasons I don't understand. It evidently solved a problem I didn't have. You see I used chef-client and knife (with several plugins). I work with a lot of folks who are NOT ruby or chef people. For us, Chef is a means to an end. It's a tool just like Maven or Artifactory. We use chef-solo as the installer for our platform, for instance. We are not ruby developers or users. All of our tooling is either in Python or Java and our application code base is in Java.

Opscode (or rather Chef) has previously made a big push to make being a Chef user mean not being a ruby expert. There seems to have been not only a shift in that thinking but also in how the tools are to be used.

A good example is chef-metal. This originally confused me because this was the chef model:

- `knife` is for your workstation
- `chef-client` is for your servers

With `chef-metal` that changes a bit because the understanding is that where you might use `knife rackspace <blah>` you'll now run `chef-client recipe[rackspace_servers]`.

So back to berkshelf and other tools...

Before these were optional. Slowly but surely they're becoming NOT optional. The problem with this is as I described above. No longer is the workflow:

- install knife
- checkout our chef repo
- edit/upload cookbooks

It's now become a ruby developer workflow of somekind. I don't have a cookbook directory. All of my cookbooks are somewhere in `~/.berkshelf` and I'm expected to have every cookbook be its own repo or something. I still don't fully understand what's going on here and frankly I don't have the time. I have chef novices on my team and I don't have any official documentation to point them at because this is something that exists outside of the official chef documentation.

I'm not trying to slander ANY of these tools. I'm sure they're all wonderful. `test-kitchen` looks great for being able to break away from tying the provisoner to vagrant but again that's not the workflow that works for us (or frankly anyone who just wants to use chef). My argument is simply that if these things are going to be the defacto model then they should be rolled into Chef somehow and be documented on the official documentation.

# So back to omnibus
So we have two issues here that make omnibus not a fit anymore:

- Requires a full blown ruby environment to **BUILD** a project whereas before it only required a full blown ruby environment to **CREATE** a project.
- the 3.x RELEASE of Omnibus had a dependency on an **UNRELEASED** artifact

That second one is really painful to swallow. Quite frankly it's just poor form to do that. You can argue about version numbers being meaningless or "it's stable just still in beta" but when you're asking someone to use and depend on your tools it's just not right. If your dependencies aren't released yet then you don't get to release. Let's also not forget that EVERY anicllary add-on in the Chef world seems to have its own dependency on Berkshelf now.

# How did this all come to a head
When Heartbleed hit, I needed to rebuild our two big omnibus packages. Recently I had switched over to a new laptop and didn't yet have anything checked out. This was fine because I had the omnibus projects checked out on my desktop. It was running a 1.4 release of vagrant and was where I did most of my builds before. So we generated new packages and were happy.

We also have new team members on our ops team. I was using this as an opportunity to show them the omnibus packages and let them build them as well. So I tell them to check out the repos, make sure they have the plugins installed and run `vagrant up`. This didn't work and it turns out somebody had vagrant 1.5 installed. No big deal I think. We'll punt on that one and just make a note that we'll need a new `vagrant-berkshelf` plugin when it's released.

But yesterday I went to work on a massive refactor of our omnibus packages since we're cleaning up a bunch of extras and changing things around. I knew that omnibus 3 had several things to make the whole build process go faster. It also allowed me greater control in build determinism. So I upgrade and generate a new project to see what the new layout looks like and test the builds. When I realize there's no Vagrantfile, I'm really confused. The readme says a Vagrantfile would be generated.

That set off the things I tweeted and posted on the mailing list. In the end it came down to me evidently relying on something I was never supposed to rely on and being told I should learn to RTFM.

# So where does that leave things?
Right now I have to take back everything I said about omnibus. It's not that I don't think it's a great tool and I certainly don't give two shits about getting subtweeted. I still think it's a great tool and I think the idea is the right one.

However the main reason I recommended omnibus and bothered to integrate it is gone. It's simply not the straightforward process it was and the removal of the vagrant build lab puts too much on the non-ruby-ecosystem user. This is where I didn't listen to my own advice. I frequently warned people that omnibus exists first and foremost for the needs of Chef. We got lucky that it worked this long and it was an awesome ride.

I'm still trying to figure out the best way to get BACK to the vagrant build-lab but it's not working out so well. I'm attempting to rebuild my [omnibus-omnibus-omnibus](https://github.com/lusis/omnibus-omnibus-omnibus) project to ship everything needed but now I'm back to stepping outside the community framework and making people who wanted to just create packages needing something extra I created.

# One last thing
I'm not posting this expecting anyone to change anything in Omnibus and I'm not trying to be passive-aggressive. I'm not entitled to anything from anyone. This is more about providing something I can link to for users of the omnibus builds I've already published since they will no longer work out-of-the-box.
