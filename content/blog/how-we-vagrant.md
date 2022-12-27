+++
title = "How We Vagrant"
date = "2012-12-17"
slug = "2012/12/17/how-we-vagrant"
Categories = []
+++

People may or may not have noticed but I've been largely offline for the past 4 weeks or so. This is because I've been in the middle of a pretty heavy redesign of a few key parts of our application stack. This also required me to learn Java so I've been doubly slammed.

As part of that redesign, I worked on what we lovingly refer to internally as the "solo installer". I gave a bit of background on this in a post to the Chef mailing list at one point but I'll go over it again as part of this post.

<!-- more -->

# Beginnings
To understand why this is something of a departure for us, it's worth understanding from whence we came. enStratus, like most hosted solutions, has experienced largely organic growth. One of the nice things about a SaaS product is that you have the freedom to experiment to some degree. You might be in the middle of a MySQL to Riak migration and still need two data stores for the time being. You might be in the process of changing how some background process works so you've got an older designed system running along side the newer system which is only doing a subset of the work.

With a hosted platform these kinds of things are hidden from the end-user to some degree. They don't know what's going on behind the scenes and they really don't care as long as you're doing X, Y and Z that you're being paid to do.

Now for those of you running/developing/managing some sort of SaaS/Hosted solution I want you to take a journey with me. Imagine that tomorrow someone walked into your office and said:

{% blockquote %}
We need to take our production environment and make it so that a customer can run it on-premise. Oh and it has to be able to run entirely isolated and can't always talk back to any external service.
{% endblockquote %}

That's pretty much the place enStratus found itself. enStratus is a cloud management platform. Not all clouds are public. Maybe someone wants to use your service but has regulatory needs that prevent them from using it as is. Maybe it needs to run entirely isolated from the rest of the world. There are valid reasons for all of these despite my general attitude towards security theater in the enterprise.

Now you've got an interesting laundry list in front of you:

- How do you teach someone to manage this organic "thing" you've built?
- How do you take not one application but an entire company and its stack and shove it in a box?
- Do you wrap up all the external components (monitoring, file servers, access control) and deal with those?

We aren't the first company to do this and we won't be the last. Take a look at Github. They offer a private version of Github. But we're not just talking about one part of Github - e.g. Gist. We're talking about the entire stack the company runs to provide Github as we know it.

Unless you design with this in mind, you can't really begin to understand how difficult of a task this can be. As I understand it, Github finally went the appliance route and offer prefab vms with some setup glue. Please correct me if I'm wrong here.

# Early iterations
Obviously you can see that this was/is a daunting task. Original versions of our install process were based around a collection of shell scripts. Because of certain details of the stack (such as encryption keys for our key management system), we had to maintain state between each component of the stack when it was installed. Currently there are roughly 7 core components/services that make up enStratus:

- The console
- The api endpoint
- The key management subsystem
- The provisioning subsystem
- The directory integration service
- The "worker" system
- The "monitor" system

and those are just the enStratus components. You also need RabbitMQ, MySQL and Riak (as we're currently transitioning from MySQL to Riak). All of these things largely talk to each other over some web service or via RabbitMQ. With one or two exceptions, they can all be loadbalanced in an active/active type of configuration and scaled horizontally simply by adding an additional "whatever" node.

So the original installation process was a set of shell scripts that persisted some state and this "state" file had to be copied between systems. Yes, we could use some sort of external configuration store but that's another component that we would have to install just to do the installation.

# Phase two
One of my coworkers, [Greg Moselle](https://twitter.com/zomgreg) was sort of "sysadmin number one" at enStratus. This was in addition to his duties as managing all customer installs. So he did what most of us would do and brute forced a workable solution with the original shell scripts. As enStratus started to offer Chef and Puppet support in the product, Greg gets this wild hair up his ass and thinks:

{% blockquote %}
I wonder if I can rewrite these shell scripts into something a bit more cross-platform and idempotent using chef-solo.
{% endblockquote %}

You might be thinking the same thing I originally did that this was largely a bad idea. In my mind we had a workable solution for the interim in the existing shell scripts that had the install of enStratus down to a day or so. Pragmatism right? It's also worth noting that this was how he wanted to learn Chef...

So off he goes and does what I recommend any new Puppet or Chef user does - exec resources all over the fucking place. Wrap your shell scripts in `exec`. Hardcode all the fucking things.

Once he did this, then I started working with him on some basic attributes to make them a bit more flexible. Before too long we had a stack of roles matching different components and we had moved everything from `cookbook_file` to `remote_file`. It was still a mess of execs but it worked.

But we still had this "state" we had to maintain between runs. This is not going away anytime soon. In production we store this state in attributes and use chef-server. We didn't have that luxury here.

Then [Jim Sander](https://twitter.com/jimsander) drops in and writes a small setup script that maintains some of that state for us. Basically a wrapper around raw `chef-solo`. Side note, if you ever need someone to drop some shell scripting knowledge on your ass, Jim's the man to see. Ask him about his Tivoli days to really piss him off.

At this point, I start working on cleaning up the recipes as a sort of tutorial for folks. I'd pick a particular recipe and refactor it to all native resources and make it data driven. I'd commit these in small chunks so folks could easily see what the differences were easily - stuff like "instead of execing to call rpm, we'll use the yum provider".

At this point we've got something pretty far evolved from where we were. Now that we've got this workable chef-solo repository, I decide to hack out a quick Vagrantfile. The problem was it wasn't entirely idempotent and we still had some manual steps that had to be dealt with. In addition to finishing up the recipes and ended up rewriting large chunks of the setup script. Now that I had something largely repeatable and localized, we suddenly had a Vagrant setup that folks could use for development. It wasn't fully automated but it worked. We also still had this shared state thing.

So I set out to refactor the setup script a bit more. What's important to keep in mind is that the primary use-case for this chef-solo repository wasn't for Vagrant. This is our "installer". The interesting part to me is that the improvements to how we do on-premise installs are coming as a direct result of making this work better with Vagrant. There's a lot of wrapper work tied up in the setup script that wouldn't need to be done if we used a base box that had more stuff baked in. However not baking stuff in actually gives us a more real-world scenario for installation.

Additionally we needed to be able to somehow pass user-specific configuration settings into the `vagrant up` process and get those into `chef-solo` by way of the setup wrapper. We have things like license keys, hostnames and my personal hated favorite - database credentials - that need to be handled in a way that we can make it so a developer can just type `vagrant up` and be running. If I have to require someone to edit a json file or anything else, the whole thing will fall flat on its face.

So any time we needed something like that, we added support to the setup wrapper and then used environment variables to pass that information in to vagrant.

# So how do we vagrant?
We leverage environment variables pretty heavily in our Vagrantfile. If it's something that someone might need to tune for whatever reason, it's an environment variable that triggers an option to our setup script. 

## Current list of tunables
This is just a subset of the tunables we control via environment variables. The majority of these map directly to options for the setup script:

- `ES_DLPASS` and `ES_LICENSE`: the basic set of credentials needed to fetch our assets and your personal license key. 
- `ES_MEM`: this is the result of some of our front end developers having less memory than others.
- `ES_CACHE`: We have an office in New Zealand and bandwidth there is "challenging". This allows us to cache as much as possible between calls to `vagrant up`. This not only triggers caching of system packages downloaded but also triggers the `prefetch` option in our setup script that predownloads all the assets. These assets are all stored in the `cache` directory of the repository which is not coincidently the value of `file_cache_path` in chef-solo. Remember that we may not always have external network access during installation so we offer a way to warm the `cache` directory with as many assets as possible.
- `ES_BOX`: let's you specify an alternate base box to use. This is how we test the installer on different distros.
- `ES_DEVDIR`: shares an additional directory from the host to the vagrant image. This is how development is done (at least for me). I map this to the root of all of my git repository checkouts.
- `ES_VAGRANT_NW`: Allows you to configure bridged networking in addition to the host-only network we use.
- `ES_PROFILE`: This directly maps to an option in our setup script for persisting state between runs.

There are other options that are specific to the enStratus product as well but you get the idea.

## The setup script
I can't post the full thing here but I can give you a general idea of how it works and some of the options it supports. This is a "sanitized" truncated version of the help output:

```
Usage: setup.sh [-h] [-e] [-f] -p <download password> -l <license key> [-s savename] [-c <console hostname>] [-n <number of nodes>] [-m <mapping string>] [-a <optional sourceCidr string>]
-------------------------------------------------------------------------
-p: The password for downloading enStratus
-l: The license key for enStratus

For most single node installations, specify the download password and license key.

optional arguments
------------------
-h: This text
-e: extended help
-f: fetch-only mode. Downloads and caches *MOST* assets. Requires download password and *WILL* install chef
-c: Alternate hostname to use for the console. [e.g. cloud.mycompany.com] (default: fqdn of console node)
-a: Alternate string to use for the sourceCidr entry. You know if you need this.
-s: A name to identify this installation
-n: Number of nodes in installation [1,2,4] (default: 1)
-m: Mapping string [e.g. frontend:192.168.1.1,backend:backend.mydomain.com]

About savename:
---------------
Savename is a way to persist settings between runs of enStratus.
If you specify a save name, a directory will be created under local_settings
will be created. It will contain a YAML file with your settings as well 4 JSON files.

The YAML file is the source of truth for the named installation. The JSON files MAY
be recreated if the contents of the YAML file change. They exist to migrate between systems.
If a save file is found, no other arguments are honored. If you need to change the 
download password or license key, please update the YAML file itself

If you lose this YAML file you will not be able to recover this enStratus installation.
You should save it somewhere secure and optionally version it.
```

### Persisting settings
One of the "gotchas" we have is how do we basically build a node JSON file for chef-solo to use with any information we need to persist. Since we don't know the state of all the systems involved when we go in, we have to "punt" on a few things. What we end up doing is something we call the `savename`. If you use this option, the settings you define will be persisted to a directory that git ignores called `local_settings`. This directory will contain directories named after the above `savename` parameter. The setup script (written for now in bash) will create a yaml file (easy to do in bash with HEREDOC as opposed to JSON) and also a copy of the generated encryption keys in a plain text file for the customer to store. 

The only thing we can count on being on the system up front is the Chef omnibus install (since that's a requirement). Instead of complicating things with ruby at this point (and chicken/egg issues since the setup script actually installs chef omnibus), we use the `erubis` binary that gets installed with omnibus to pass the yaml to to a JSON erb template. That generated JSON is the node json with attribute overrides. We actually support multi-node installation in the setup script if you provide a mapping of where certain components are running when calling setup. If you rerun setup using an existing `savename` parameter, the yaml file is updated (only certain values) and then regenerate the JSON file.

# The upshot
The best part of all of this is that we can now say the same process is used when installing enStratus locally in Vagrant, in our dev, staging and production environments (though production uses chef-server) as well as what we install on the customer's site. We version this repository around static points in our release cycle. We branch for a new release and create tags at given points in the branch based on either a patch release for enStratus itself in that release OR a patch to the installer itself.

It's not all unicorns pooping rainbows. The process is much more complicated than it needs to be but it's almost a world of difference from where it was when I started and it was entirely a team effort. This setup allowed us to do full testing to switch entirely off the SunJDK (and the need to manually download the JCE during customer installs) onto OpenJDK. We were able to migrate from Tomcat to Jetty and refactor our build process using this method. I was able to do this work without affecting anyone else. All I had to do when we were ready for full testing was tell everyone to switch branches, run `vagrant up` and test away.

# Special thanks
I want to give a serious shout-out to Mitchell Hashimoto and John Bender for the work they did with Vagrant. Last year I said that no two software products impacted my career more than ElasticSearch and ZeroMQ. This year, without a doubt, Vagrant is at the top of that list.

# Addendum
What follows is the sanitized version of our `Vagrantfile`. If anyone has any suggestions, I'm all ears:

```ruby
Vagrant::Config.run do |config|
  if ENV['ES_BOX']
    config.vm.box = ENV['ES_BOX']
  else
    config.vm.box = "es-dev"
    config.vm.box_url = "https://opscode-vm.s3.amazonaws.com/vagrant/boxes/opscode-ubuntu-12.04.box"
  end

  if ENV['ES_VAGRANT_NW'] == "bridged"
    config.vm.network :bridged
  else
    # If you change this address, the conditional logic
    # in console.rb will break
    config.vm.network :hostonly, "172.16.129.19"
  end

  # These entries allow you to run code locally and talk to a
  # "working set" of data services
  config.vm.forward_port 15000, 15000   # api
  config.vm.forward_port 3302, 3302     # dispatcher
  config.vm.forward_port 2013, 2013     # km
  config.vm.forward_port 5672, 5672     # RabbitMQ (autostarts)
  config.vm.forward_port 8098, 8098     # Riak HTTP (autostarts)
  config.vm.forward_port 8097, 8097     # Riak protobuf (autostarts)
  config.vm.forward_port 3306, 3306     # MySQL (autostarts)
  config.vm.forward_port 55672, 55672   # RabbitMQ management interface
  
  if ENV['ES_MEM']
    config.vm.customize ["modifyvm", :id, "--memory", ENV['ES_MEM']]
  else
    config.vm.customize  ["modifyvm", :id, "--memory", 8192]
  end

  if ENV['ES_DEVDIR']
    config.vm.share_folder "es-dev-data", "/es_dev_data", ENV['ES_DEVDIR']
  end

  if ENV['ES_CACHE']
    puts "Shared cache enabled"
    FileUtils.mkdir_p(File.join("cache","apt","partial")) unless Dir.exists?(File.join("cache","apt", "partial"))
    config.vm.share_folder("apt", "/var/cache/apt/archives", "cache/apt")
  end

  config.vm.provision :shell do |shell|
    ES_LICENSE=ENV['ES_LICENSE']
    ES_DLPASS=ENV['ES_DLPASS']
    ES_PROFILE=ENV['ES_PROFILE'] || "vagrant-#{Time.now.to_i}"

    if ES_LICENSE.nil? or ES_DLPASS.nil?
      puts "You must set the environment variables: ES_LICENSE and ES_DLPASS!"
      exit 1
    end
    ES_CLOUD=ENV['ES_CLOUD']
    ES_CIDR=ENV['ES_CIDR']
    ES_DEBUG=ENV['ES_DEBUG'] || false
    setup_opts = "-l #{ES_LICENSE} -p #{ES_DLPASS} -s #{ES_PROFILE} "
    setup_opts << "-c #{ES_CLOUD} " if ES_CLOUD
    setup_opts << "-a #{ES_CIDR} " if ES_CIDR
    ES_DEBUG ? chef_opts="-l debug -L local_settings/#{ES_PROFILE}/chef-run.log" : ""
    shell.inline = "cd /vagrant; ./setup.sh #{setup_opts}; chef-solo -j local_settings/#{ES_PROFILE}/single_node.json -c solo.rb #{chef_opts}"
  end
  if ENV['ES_POSTRUN']
    config.vm.provision :shell do |shell|
      shell.inline = "chef-solo -j /vagrant/local_settings/#{ES_PROFILE}/single_node.json -c /vagrant/solo.rb -o \"#{ENV['ES_POSTRUN']}\""
    end
  end
end
```
