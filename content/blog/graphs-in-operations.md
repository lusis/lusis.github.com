+++
title = "Graphs in Operations"
date = "2012-03-06"
slug = "2012/03/06/graphs-in-operations"
Categories = []
+++
So anyone who knows me knows I spend an inordinate amount of time bitching about Maven. I don't know if it's the type of companies I end up working for or what but I always seem to find myself ass-deep in Maven.
<!-- more -->
_please note that I'm drifiting into deeply unfamiliar territory for me. Someone once told me the best way to learn about something is to write about it. Keep that in mind when making comments?_

One of the more interesting parts of maven is the dependency graph and concepts like transitive and (god forbid) circular dependencies. These problems aren't exlcusive to java, mind you. See bundler for Ruby.

## A bit on graphs
Graph is a fairly overloaded term. In the context of this discussion I'm talking about graph theory (insofar as I can grok it). Specifically I want to talk about it in the context of IT operations.

Graphs are nothing "new". Programmers have binary trees. Network geeks have OSPF. Puppet and Git are fans of the DAG (directed acyclic graph). These are all rooted in the same place no? You have nodes and edges. It's math all the way down. Unfortunately I suck at math.

If the topic interests you at all, wikipedia has a good couple of articles worth reading. Seeing as I'm far from a domain expert, I can't vouch for the quality:

- [Graph Theory](http://en.wikipedia.org/wiki/Graph_theory)
- [Graph (mathematics)](http://en.wikipedia.org/wiki/Graph_(mathematics))
- [Glossary of graph theory](http://en.wikipedia.org/wiki/Glossary_of_graph_theory)

# How can graphs apply to IT operations
I've said for a while now that I feel like there's something fuzzy on the horizon that I can't make quite make out and it involves orchestration and graphs. I'm still not clear on how to express it but I'll try.

Anyone who has ever used Puppet or Git has dabbled in graphs even if they don't know it. However my interest in graphs in operations relates to the infrastructure as a whole. James Turnbull expressed it very well last year in Mt. View when discussion orchestration. Obviously this is a topic near and dear to my heart.

Right now much of orchestration is in the embryonic stages. We define relationships manually. We register watches on znodes. We define hard links between components in a stack. X depends on Y depends on Z. We're not really being smart about it. If someone disagrees, I would LOVE to see a tool addressing the space.

Justin Sheehy did an awesome high level presentation on distributed systems, databases and the like at Velocity last year. While the talk was good, one thing that stuck out with me was his usage of the Riak logo:

![Riak Logo](https://assets.github.com/img/b4d183fe3181209da593ed5c6bf0f4c805ab2a62/687474703a2f2f6769746875622d696d616765732e73332e616d617a6f6e6177732e636f6d2f626c6f672f323031302f7269616b2d6c6f676f2e706e67)

During the presentation he would zoom out of the logo and replace it with the same logo. It expressed the idea of moving up the stack. Macro versus micro. I have the same feeling about where orchestration is going.

## Express yourself
Currently we do a great job (and have the tools) to express relationships and dependencies at the node level:

- webapp needs container
- container needs java
- container needs system user

Going a level higher, we even have some limited ability to express relationships between nodes:

- Load balancer needs app servers
- App server needs database

We're not quite as good at this part yet but people have workarounds. I use Noah for this. enStratus also handles this very well.

But we're still defining those relationships manually.

When we get to this next level up, things get REALLY fuzzy. As people start to (re)discover SOA, we now have stacks that have dependencies on other stacks. Currently we use tools like Zookeeper to broker that relationship. But we still have to explcitly manage it.

The level of coupling here isn't the problem. You can mitigate failure in one stack as it relates to another stack. Fail fast and fall back to sane/safe defaults. Read any article about how Netflix architects to get an idea.

# What's missing?
What I feel like we're missing is a way to express those relationships and then trigger on them all the way up and down the chain as needed. We're starting to get into graph territory here. 

We must we be able to express and act on changes at the micro level (_I changed a config, I must restart nginx_) and even at the intranode level (_something changed in my app tier, need to tell my load balancer_) but now we need a way handle it at that macro level. Not only do we need a way to handle it but we must also be able to calculate what is impacted by that change.

- If I have this internode change, does it affect the intranode relationship?
- If I have an intranode change, does it affect the intrastack relationship?

It seems to me that a graph of SOME kind is the best way to express this. I just can't quite make it out. Does current graph technology even handle that subgraph relationship? Excuse the pun but where do we draw the line? Are there multiple lines?

Maybe this isn't an issue. Maybe through resilience engineering we simply keep that "intrastack" dependency as loose as possible so that we don't have this problem?


