+++
title = "Berks"
date = "2014-07-15"
slug = "2014/07/15/berks"
Categories = []
+++

Note this post has comments disabled. This is a first for me but if someone wants to ask me anything else, there are multiple personal ways to contact me.
<!-- more -->

Recently I posted a message to the `chef-users` mailing list. It was a hard post for me to write because I was attempting to be as tactful as possible about the issue. While I think it was the right move and some good came out of it, I now feel like I have to defend myself for some reason.

My concern over Berkshelf came from one single place, what is the implication to Chef (the tool) itself where everything in the ecosystem adopts Berkshelf for dependency solving.

Why do I care? Because EVERY SINGLE EXPERIENCE I've had with Berkshelf has been crappy. I'm never quiet about crappy software. Ask anyone I work with. I'm just as matter of fact about it in our own software as well as software I've had to use.

Understand that my frustration with Berkshelf came out of using it with omnibus early on and the resultant breakage from either vagrant-berkshelf or omnibus. Every attempt I've made to use Berkshelf internally has ended badly including restoring my local checkout from an internal repo.

You can ask anyone on our operations team but I have said if our team decides we want to use Berkshelf then we'll use it. Multiple attempts have been tried and everyone has come away with a bad experience.

Why is that? Why is something as simple as a dependency solver so painful? Julian Dunn said it best:

{% blockquote %}
We grabbed Berkshelf, the first thing that was convenient & met the need. But we also got a lot of things that are kind of superfluous to the goal. The "Berkshelf Way", "Environment Cookbooks", and friends all have little to do with that, and will not be used by most people
{% endblockquote %}

I hope this makes my concerns about where Berkshelf itself is going in Chef much more clear. No one is arguing that depsolving isn't a problem. The issue is that along with that comes a set of entirely unrelated things. If the entire Chef ecosystem is moving to Berkshelf (the depsolver) plus Berkshelf (the everything else) then, yes, that lessens the value of the ecosystem to me. I stand by my statement that Berkshelf is impossible to avoid these days because it *IS* integrated into every part of the ecosystem regardless if it's actually a requirement of chef itself.

Understand I am not paid to work on Chef, Chef ecosystem tools or anything else. I am paid to keep our applications and servers running. To do this, we leverage tools like Artifactory Pro, Chef, Jenkins, Rundeck, Omnibus, Vagrant, Nginx and countless others. If any of those tools start causing an impediment to our ability to ship then we have an obligation to revisit if that tool is the right fit. Maybe we've outgrown it. Maybe the tool (or its ecosystem) has taken a different approach. In the end it doesn't matter. If the tool isn't working, we either have to deal with finding a replacement or fight it until we can find (or create) a replacement. Every tool choice we make has implications on everything from availability to business agility to how often people get woken up at night.

I'm truly sorry if people took these criticisms personally. I realize the discussion got heated and others were a bit more stern in the criticism they used. I never asked anyone to "come to my defense". I also have never pushed my view on anyone else and told them they shouldn't use Berkshelf. I've been blunt about my experiences and pain points but never said either "You shouldn't use Berkshelf or I don't think you should use Berkshelf". 

The people working on these tools are nice people (at least the ones I've met in person). I realize how hard it is to separate the opinion of something you've created or maintain from yourself. Your effort in maintaining opensource software is truly appreciated.

The last thing I want to say in my defense is this. If you think that any of this over the past few days is somehow my fault or that I started all this, that's simply wrong. I addressed this in the mailing list post. If you want to be mad at ME that's fine but consider this. You don't always get negative feedback from your users or your customers. At best you get a small chance to salvage the relationship from a public complaint but more often that not, people just go away mad.

The problem is that this carries over in to discussions you never hear about. Joe asks Sally her opinion on X and Sally recounts her personal experience. This shapes Joe's perception of X.

If anyone would like to contact me about this, I will be glad to talk about it as time permits but I'm this is last I plan on talking about it in public.
