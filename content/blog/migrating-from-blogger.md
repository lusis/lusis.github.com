+++
title = "Migrating From Blogger"
date = "2011-08-13"
slug = "2011/08/13/migrating-from-blogger"
+++

Many, many years ago I started blogging. It's fun and I like to feel self-important by dumping my ideas. I enjoy writing so blogging is cool like that.

Over the past 12 years I've gone through various tools to help me do that (in no particular order):

- PHPnuke
- Postnuke
- Moveable Type
- Plone
- CakePHP
- rst2html vim plugin
- Blogger
- Wordpress
- Homegrown shit

With the exception of one, all of these tools were simply a pain in the ass. They required lots of moving pieces, we're bug ridden and security nightmares.
The one tool I enjoyed the most? 

rst2html in Vim

The only reason I used those tools was because I hated writing HTML. I hated dealing with styling. About 3 years ago I finally settled on using Blogger. Now I'm changing again.
<!--more-->
# Blogger and Markdown
While I was perfectly happy with Blogger, one thing that kept annoying me was that writing posts became tedious. Working inside a textarea widget was still painful. I used Scribefire for a while in Firefox. For the last 4 months or so, I've been writing my blog posts in Markdown, manually converting them to HTML with pandoc and then pasting them into blogger.

I tried the google CLI tools but I still had to massage the generated output so I never bothered to automate any of the posting part. Additionally, as much more of posts became technical, I found myself jumping over to gists and pasting the embedded content link into the posts. The whole workflow sucked.

# Octopress and Github
The other day I came across [Octopress](https://github.com/imathis/octopress). The default style was attractive. It handled code VERY well. Best of all, I could work in Vim, run a few rake commands and my content would be published to Github.

WIN!

I find this workflow MUCH easier and sane. It's very coder friendly.

What's also nice is that, should github go away (god forbid), I have everything here self-contained to run on my own server.

# Old posts
I'm currently in the process of redoing what blogger posts I have that weren't already in Markdown. I'm converting the more "popular" posts first and then I'll get the remainder. It's a little painful but the light at the end of the tunnel is that I only have to do this once.
