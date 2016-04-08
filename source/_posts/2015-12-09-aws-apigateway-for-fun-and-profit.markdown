---
layout: post
title: "aws api gateway for fun and profit"
date: 2015-12-09 22:06:59 -0500
comments: true
categories: ["monitoring", "signalfx", "opsgenie", "dirtydirtyhacks"]
---

At we're currently in the process of reconfiguring our monitoring, logging and alerting setup.
<!-- more -->
Obviously this is something near and dear to my heart. At my previous employer we did everything in house due to various constraints.
I've got a rich set of experiences in this space so my first inclination was to build not buy. However, due to OTHER constraints, building was not a practical solution at present.

After much research and evaluation, we finally settled on the following stack:

- Loggly
- SignalFx
- OpsGenie

I'm not going into reasons for switching in this post or details about WHY these providers were chosen. This is to discuss a specific AWS service and how it can really change the nature of ChatOps and more.

# Integration
Our monitoring and metrics provider, SignalFx, is still building out its integrations.
They have a rich set already and are iterating very quickly but coupled with the migration to OpsGenie, we needed to work out a solution for wiring the two together.
Using the power of google, I came across a great post from [R.I. Pienaar](https://twitter.com/ripienaar) about leveraging [Lambda and the AWS API Gateway products](https://www.devco.net/archives/2015/08/13/translating-webhooks-with-aws-api-gateway-and-lambda.php).

In his post, he didn't really go over the API Gateway configuration so I figured I would document what I "figured out" for others.

## The flow
The above post covers most of it but the general idea is:

- [SignalFx Alerting](https://support.signalfx.com/hc/en-us/articles/203824569-Detectors-and-alerts) webhook fires
- AWS API Gateway gets webhook and fires off a Lambda task
- Lambda task translates webhook into OpsGenie [create alert](https://www.opsgenie.com/docs/web-api/alert-api) api call
- OpsGenie wakes you from a dead sleep

Now you might wonder why go through all this hassle? OpsGenie can create email integrations and SignalFx can send alerts to email address.

# It's about context. 

When email alerts come in to OpsGenie, they're just "pings":
{% blockquote %}
Hey this thing sent us an email and here's the subject. Log in to the website to see the body
{% endblockquote %}

Frankly there's not much else they can do. SignalFx will also send another email when an alert auto-clears but to OpsGenie, that's another "ping". It's unrelated to the previous email.

![Hipchat alerts from OpsGenie](/images/posts/apigateway-lambda/hipchat-emails.png)

As you can see above this could get painful and I already have issues with "alert fatigue".

Additional, even if you **DID** log into the OpsGenie website to look at the details, there's really not much there:

![Emails in OpsGenie](/images/posts/apigateway-lambda/opsgenie-emails.png)

Yeah that's helpful...

So we need to accomplish two things:

- Correlate alerts and auto-closes from SignalFx
- Get some damn context into the alert so we can act intelligently on it

The end result, gives us this:

![Hipchat alerts from OpsGenie](/images/posts/apigateway-lambda/webhook-hipchat.png)
![SignalFx Metadata in OpsGenie](/images/posts/apigateway-lambda/signalfx-context-1.png)
![OpsGenie Alert History](/images/posts/apigateway-lambda/opsgenie-alert-history.png)

and that's MUCH more useful.

# How we did it
AWS API Gateway is a PRETTY intimidating thing. There's lots of very specific terminology and frankly I found the docs not so useful.
The general idea is this:

- Create an api
- Create a "resource" this is just a route that the api gateway responds to i.e. `/signalfx_to_loggly` via `POST`
- Create a "model": This is a json schema that describes what the incoming `POST` body looks like and its content type
- Create an "integration request": What do you do when you get it? In this case call a Lambda task

There are also concepts like "stages" which frankly were a bit useless in my case.
In the end, after you get all of this wired up, you'll be given a url that looks something like this:

`https://<random id>.execute-api.<region>.amazonaws.com/<stage name>/<resource name>`

and that's your webhook url.

## Models
In the case of SignalFx, a webhook post looks like this:
```json
{
  "incidentId":"XXXXXXX",
  "detectorUrl":"https://app.signalfx.com/#/detector/XXXXXXX/edit",
  "status":"too high",
  "rule":"500 errors over 10",
  "severity":"Major",
  "eventType":"_SF_PLOT_KEY_XXXXXXX_3_17",
  "currentValue":"2",
  "sources":"chef-external-elb",
  "detector":"ELB 5xx Detector"
}
```

which translates to the following model:

```json
{
  "$schema": "http://json-schema.org/draft-04/schema#",
  "title": "signalfx-webhook-model",
  "type": "object",
  "properties": {
    "incidentId": {"type":"string"},
    "detectorUrl":{"type":"string"},
    "status":{"type":"string"},
    "rule":{"type":"string"},
    "severity":{"type":"string"},
    "eventType":{"type":"string"},
    "currentValue":{"type":"string"},
    "sources":{"type":"string"},
    "detector":{"type":"string"}
  }
}
```

# The lambda function
I'm still cleaning up the banged out code for the Lambda function to post on github however here's the relevant bits from the `opsgenie.js` file I added to [R.I.'s code](https://github.com/ripienaar/lambda_webhook_gwy):

```javascript
  sfxNotification: function(event, config) {
	var result = {
		apiKey: config.api_key,
		alias: event.incidentId,
		note: event.detectorUrl,
		message: "Alert: "+event.rule+" ("+event.detector+")",
		description: "Current value: "+event.currentValue+" is "+event.status,
		details: event,
		tags: event.sources.split(","),
		
	};

	return result;

  }
```

This is the meat of the translation. You can customize this as needed but note how we immediately add a `note` to the incident with the link to the graph in SignalFX.
When you create a detector in SignalFx, any groupings you create in the signal function are put into the `sources` key as comma-separated values.
We take these and make them `tags` in OpsGenie.

We also leverage the OpsGenie `alias` to create our own ID for the event. Normally you would store this id somewhere for reference later but instead we use the unique id from SignalFX.
This make correlating a previous alert dead simple as you can see below:

```javascript
var self = module.exports = {
  publish: function(handler, event, context, callback) {
    var webhook = require("./webhook_request.js");
    var config = require("./config.js");

    if (event.status == "ok") {
	    var request = self.closeRequest(config.opsgenie);
	    var data = {
		    apiKey: config.opsgenie.api_key,
		    alias: event.incidentId
	    };
    } else {
    	var request = self.request(config.opsgenie);
    	var data = self[handler](event, config.opsgenie, context);
    }
    webhook.publish(JSON.stringify(data), request, context, callback);
  }
```

SignalFX has 4 status it can set for an alert:

- `ok`
- `too high`
- `too low`
- `anomalous`

Here we check if the status is `ok` and if so, we call a different function to generate the request object to a different path (the `close` alert path).
Since we created the alert using an `alias` of the SignalFx `incidentId`, we don't even need to do any more parsing - just `POST` to the `close` resource with the `alias` id.

As you saw above we get more more data and context about the alert and this is all also visible in the OpsGenie mobile app.

# Benefits
Honestly the biggest benefit is not having to wait on service providers to create native integrations.
Almost every service I've used over the past several years offers an outgoing webhook capability for their system.
API Gateway solves the part of getting those webhooks and its native Lambda support means I don't need to leave anything "running" to maintain, upgrade and support just to do something with that webhook.

However one other huge benefit that is also hinted in the other post is that using this model, you get a simple abstraction from your service provider.
If we wanted to move to some other alerting provider, we just change the lambda function to post there instead. No need to redo all the integrations in SignalFx.
My plan is to work towards a model where we try and utilize the API gateway as the webhook endpoint for various services and translate to our other providers from there.

I really enjoyed working with the gateway. Testing wasn't too painful as it has a "mock" mode as well where it behaves similarly to requestb.in. It supports a couple of different authentication methods well (though sadly they couldn't be leveraged in this case due to provider webhook formats (note if you offer webhook support, let your users define a custom header as part of the webhook!).

Lambda needs to support more languages but python is a happy medium from the mess of javascript and the heft of java.

# Future steps
I'm going to work on first cleaning up this specific Lambda task and making it available on github. After that my next steps are to rewrite the damn thing in Python because I "dislike" javascript.
We're already using this similar model for another integration as well: Loggly and HipChat.

Loggly has HipChat support already but it posts the messages in HipChat as a raw json dump which is useless:

![Loggly JSON](/images/posts/apigateway-lambda/loggly-json.png)

by migrating it to use API Gateway and Lambda, we get the following instead:

![Loggly better](/images/posts/apigateway-lambda/loggly-better.png)
_Obviously that's an MVP_

We'll probably go down this route farther for other integrations and especially when HipChat Connect goes GA. Then we'll likely start posting richer messages using "cards" similar to my previous experience with Slack's API.

## ChatOps
One thing that's really neat is that the api gateway approach can let you make some REALLY simple tools for ChatOps using outgoing webhooks from your chat system.

However it will REALLY open up when Lambda gets VPC support. Imagine a Lambda function that can fire inside your VPC and interact with all your private resources. It's both terrifying and thrilling assuming all the appropriate controls are in place (using api keys, rbac in your outgoing webhook).


Thanks for reading. I hope it was valuable to you.
