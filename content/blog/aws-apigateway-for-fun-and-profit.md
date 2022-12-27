+++
title = "Aws Apigateway for Fun and Profit"
date = "2015-12-09"
slug = "2015/12/09/aws-apigateway-for-fun-and-profit"
Categories = []
+++

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
description = "Current value
		details: event,
Tags = ["`ok`", "`too high`", "`too low`", "`anomalous`"