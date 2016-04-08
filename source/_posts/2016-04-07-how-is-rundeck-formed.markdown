---
layout: post
title: "how is rundeck formed"
date: 2016-04-07 18:31:35 -0400
comments: true
categories: rundeck,devops,scripting
---

I've mentioned a few times across various tweets that I'm a big fan of [Rundeck](http://rundeck.org). Since a few folks have asked why/how we use it I figured a blog post is better
<!-- more -->

# What is rundeck?
The way I typically describe rundeck is "a web interface for shell scripts". That's a bit unfair but that's the simplest thing for people to grok.
If I recall correctly most of the concepts of rundeck were spun out of a larger product from [DTO Solutions](http://dtosolutions.com/) called [Control Tier](http://www.controltier.com/).
In the same way some people use Jenkins freeform jobs to run things on remote systems, I use rundeck but without the "constraints" of using a system designed for build automation.

The funny thing is I've not always been a fan of Rundeck.

When I first heard of Rundeck, I was almost instantly turned off. I was going through my configuration management zealotry phase. Rundeck felt like a MAJOR step backwards.
If people didn't have to give up those shitty shell scripts then they would never "do it right". I mean CM is the fucking future right?

Well after some maturing and trying to shoehorn everything into my CM tooling's concept of how to do things, I realized that maybe there was a place for it.

What really blew it open for me was realizing that while Rundeck is a web interface for shell scripts, it inherently is an API for shell scripts.
Once I got that religion, it was only a matter of time before I became a Rundeck zealot ;)

# Key advantages
Before I get into how I use/have used rundeck, I want to discuss some key things that make it really valuable:

- You can port any "legacy" scripts to a multi-user system
- You can enforce constraints on the data passed to those scripts
- You can ensure that those scripts are run ONLY the "right" way via validation
- You can get historical auditing and logging of execution of those scripts
- You can easily parallelize those scripts across multiple nodes

Frankly that should be enough right there. Scripts (regardless of the language they're written in) aren't going away and honestly I don't think they need to.
For better or worse, shell scripting is sort of an entry level skill for operations work. Some things don't need to be written in go or python. That would be complicating it.
What's needed is to solve the "that thing Susie does on her laptop when the foo breaks" in a sane way. Rundeck can make that multi-user and help dull some of the sharp edges.

# So how do I Rundeck
At my last gig (Enstratus/D***), Rundeck was the backbone of all our deployment automation. We rarely used the rundeck web interface though.

Since rundeck exposes an API to its "jobs", we talked to that API from our chatbot.

For instance a release typically went like this:

```
user: hubot rundeck run release
hubot: running job release <jobid> <link to rundeck job status page>
user: hubot rundeck output <job id>
hubot: <some of the logging output from the job>
rundeck: job release completed successfully
user: hubot dell me happy
hubot: <image of a happy Michael Dell>
```

Now that might not be the most "devops-y" (where devops is misconstrued as being only about CD) but our release cycles were weekly so whatever.

The thing is our release process had to be coordinated across multiple systems in a strict order. That "release" job was actually a rundeck job that called OTHER rundeck jobs and coordinated the failure handling and such.

At my current gig, we're going even farther as Rundeck is almost the entire core of how we do ANYTHING operationally. When I came in we had a lot of knowledge locked away in shell scripts that were finicky and had sharp edges.

The first thing we did was migrate all of these shell scripts into rundeck to solve the finicky sharp edge problem.

Rundeck let's you define arbitrary options to any job. You can reference these options via a special variable syntax in your jobs. So if I need to do something like ensure that a specific option is required or it matches a specific format, I can do that. I can define a remote url or file to pull a list of valid options from too. Basically I can bring sanity to a process that isn't going away anytime soon.

Here's a few screenshots:

## Taking sensitive input
![Defining a secure input](/images/posts/rundeck/secret_option.png)
Unless you explicitly print out this variable, Rundeck will never log it and the text box for entering it is a password type.

## Enforcing a regex
![Defining a regex based input](/images/posts/rundeck/regex_option.png)
This is handy for values where you need to match a specific pattern

![no way, jose](/images/posts/rundeck/run_job_error.png)
As you can see, attempting to input invalid data prevents the job from running

## List of remote options
![Defining a list option that is sourced from an external file](/images/posts/rundeck/remote_options.png)
In many of our jobs we share options. By using this mechanism we don't have to duplicate the enumeration of valid values. We just store them in a file and use that across multiple jobs

# Wrapping powerful CLI tools
_I assume a bit of terraform knowledge here. If there's "demand" I can make a separate blog post_

As many people know, I'm a HUGE fan of [terraform](http://terraform.io). However, like most shell scripts, terraform can have some [sharp edges](http://charity.wtf/2016/02/23/two-weeks-with-terraform/). It also has some limitations currently due to how its resource graph works. A good example is that it doesn't have conditionals. Another example is that variables cannot contain references to other variables.

We use terraform for provisioning private deploys of our stack for customers as well as standing up staging instances. Pretty much any interaction we have with the AWS api comes from terraform.

But as I said, it has some limitations. To work around this, we wrap all of our terraform stuff with Makefiles.
Part of the job of the makefile is to take variables passed in, pass those through sed to a templated `terraform.tfvars` file and write the final file: The first part of almost all of our makefiles looks like this:

```
ifneq ($(wildcard override.mk),)
$(info $(shell echo "\033[0;31m\n**** Local overrides found. Applying: ****\n\033[0m\n"))
include override.mk
endif

SHELL     := /bin/bash
REQUIRED_VARS := $(shell grep -oP '@@\K(.*)@@' terraform.tfvars.tmpl | sed -e 's|@@||g' | uniq | sed -e 's|\n| |g')
_moddir := $(shell basename `pwd`)
export ORGNAME := $(ORGNAME)
export AWSREGION ?= us-west-2
export BACKENDINSTANCE ?= c3.2xlarge
export IS_RUNDECK ?= true
export TFFORCEDESTROY ?= false
export TFAPPLY ?= true

.PHONY: help

.DEFAULT_GOAL := help

help:
	@grep -h -P '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'


check-env: ## Check the environment to be sure it's okay to build
ifeq ($(ORGNAME),)
	$(error ORGNAME is required)
endif
ifeq ($(BACKENDINSTANCE),)
	$(error BACKENDINSTANCE is required)
endif
ifeq ($(AWSREGION),)
	$(error AWSREGION is required)
endif
ifeq ($(AWS_ACCESS_KEY_ID),)
	$(error AWS_ACCESS_KEY_ID is required)
endif
ifeq ($(AWS_SECRET_ACCESS_KEY),)
	$(error AWS_SECRET_ACCESS_KEY is required)
endif
	@if [[ `expr length "$(ORGNAME)"` -gt 16 ]]; then echo "orgname must be 16 characters or less"; exit 1; fi
	@if [[ "$(ORGNAME)" =~ [^a-zA-Z0-9] ]]; then echo "orgname must be alphanumeric only"; exit 1; fi
	@echo "Everything checks out"
	@echo "Orgname: $(ORGNAME)"
	@echo "AWS region: $(AWSREGION)"
	@echo "is rundeck?: $(IS_RUNDECK)"
	@echo "backend instance type: $(BACKENDINSTANCE)"
```

The repo that stands up copies of our infra is actually multiple distinct terraform "projects" that use our own collection of modules. Each of those projects uses terraform modules and writes its state to our artifactory server for use in other projects. This helps us isolate sharp edges AND allows us to modify distinct bits VERY selectively (i.e. we can add something to the environment without maintaing a huge dependency graph or risking blowing EVERYTHIGN up at once). 

So our directory layout looks something like this:

```
├── backend
├── cassandra
├── chef
├── config
├── consul
├── dns
├── elb
├── files
├── frontend
├── idsite
├── rds
├── rundeck
├── s3-iam-ssh
├── scripts
├── templates
└── vpc
```

Each of those directories has its own makefile and there's a specific order they need to be run in (i.e. the first step is the s3-iam-ssh "project" which creates ssh keys, iam roles and s3 buckets. That run pushes its state up and those are used downstream by vpc provisioning. So on and so on.

Obviously this could all be documented in a README (and there are READMEs in each directory) but having someone run that manually is "dangerous". We could write a parent Makefile and submake things but that's not as easy either. So we wrap this in a rundeck job that enforces order, handles failures by backing out previous steps.

Additionally, there are NON-terraform tasks that have to happen like provisioning a dedicated Chef org for each "environment" and running Chef. Terraform can do some of that but it's easier outside of Terraform right now.

As I said, all of these individual things that need to happen are their own Rundeck job. There's a wrapper job that handles calling them in the right order and passing the relevant information along. That wrapper job looks like this:


![SHIELDS UP](/images/posts/rundeck/terraform_options.png)

Now that's a LOT of input needed but it's also fire-and-forget to some degree. What's nice is that we don't have to store any AWS keys on the rundeck server (and the keys that are used initially are only used long enough to provision NEW limited keys for further terraform jobs). They're never logged either (because we use the secure input for that entry).

We can enforce a list of valid instance sizes (again due to lack of conditionals in terraform, we have to have specific modules for certain classes of instances - i.e. variable numbers of ephemeral volumes per instance type). We can enforce a specific regions (we only run in regions with 3 AZs).

This was all evolutionary too. This process was originally a bunch of shell scripts calling the AWS cli tools and some manual work with the console. The benefit of rundeck here was that we could bring some initial sanity to the process and then migrate to something "better".

Now I can understand that you would look at that and say "gee golly john this is nice and all but it's still a manual process and it's a web ui and blah blah". The thing is this is only the rundeck part. We've not yet integrated this into our chatbot but we HAVE fronted all of this rundeck "ugliness" with something much nicer and something more user friendly. We call it all via the rundeck api. 

The thing is terraform is really good at what it does. Could I have spent a shedload of time writing my own webapp that made AWS api calls and did coordination and bootstrapping nodes with Chef and all that? Sure but I already had a lot of that logic implemented elsewhere that I could reuse.

I didn't have to decide on a data store or a managing that state or building a fucking orchestration engine.

# Wrap-up
Rundeck isn't perfect. No tool is. We fought a few things:

- while rundeck has ssh as a default first-class execution engine, it has ZERO concept of using a jump host
- api keys are limited and can't really be used when using an external authentication source (we use LDAP for our rundeck auth - soon to be migrated to using Stormpath)
- jobs are REALLY hard to write outside of the web interface. Yes you can do it (yaml or xml) but simply due to the sheer flexibility of a job definition it's rather pointless. I don't entirely fault them for this.
- managing permissions is really cumbersome and has its own dsl

The upshot here is that it *IS* flexible:

- You can create custom execution "drivers" so we were able to pretty easily work around the jump box issue.
- Node definitions can come from multiple sources and multiple types of sources. And they're cumulative (i.e. we can define multiple wildcard paths very easily as well as an external source)
- Node definitions support arbitrary data that can be referenced. We used that bit to define a node's jump host and jump user/keypair
- There's a built in key store system (which we are not yet using) that can help with a few things
- managing permissions is REALLY REALLY fine-grained

We're already looking at migrating to using the [AWS SSM/RunCommand stuff](https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/execute-remote-commands.html) as the execution engine.

Anyway, that's how I used rundeck and why it's become so valuable internally. 

