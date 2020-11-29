I went home that evening anxious and stressed out. I'm not ashamed to admit I had to fight back a few tears on the train during my commute back home. Those weren't tears of sadness, mind you, rather tears of fear.

It had only been a few hours since my manager came to me saying that one of our clients complaint that someone called them, saying they expected to have gotten a call after filling in the contact form and they never did.
They asked me to look into it.

See, this client sold a particular service to people, and they could fill in a form on the website to ask for a quote. Depending on their residence the closest office would call them back after they'd filled in that form. This wasn't a super complex form, it had three steps, and asked some information about people's project, some check boxes, radio buttons and drop downs — you've seen such forms before. Every request submitted is called "a lead" (a potential client), hence the name we internally gave the form: "the lead form".

When one of those leads called our client's office to complain about never receiving a phone call, the office manager was clever enough to ask for the lead's email address, making my job easier. All I had to do was go in the database, find the form submission for that lead's email address, and look at what went wrong.

{{ ad:carbon }}

As a junior developer, my first thought was to look for external problems. Every time a lead submitted the form, an email would be sent to the corresponding office with all information, my guess was that either that email ended up in spam for some reason, or that maybe there had been a problem with our mailing service.

I couldn't find an entry in our database for that email address though.

"The address is probably wrong" — I remember thinking. See, the first thing that happens when the form is submitted is that its contents are saved into the database. That way we always have a copy of the data, in case something went wrong with mailing the office. So I told my manager to contact our client, telling them the email address they gave me is probably wrong, and went on with my day.  

A few minutes later and my manager tells me that the email address is absolutely correct. 

Ok, that means that the form was never submitted then? I sigh out loud: "it probably has to do with all third party javascript trackers and marketing tools we're using on the site". Team marketing was quite into using A/B testing libraries or tools like Mouseflow to record a user's screen so that they can analyse how people interact with the website. I wouldn't be surprised if one of those tools messed up the form submission.

So I call their marketing team and ask if they know anything more about this lead. They tell me they'll look into it and I, again, went on with my day. An hour goes by and I've already forgotten about the whole affair thanks to my "not my problem" mentality. Suddenly my manager's back again: team marketing wants to speak to me and he hands me the phone.

They have found the screen recording for that particular lead. The lead actually submitted the form. They'll send me the recording now.

This is the point where I start to consider that it _might_ be my problem after all, but let's not jump to conclusions. I watch the recording, and yes indeed: the lead fills in step one, step two, step three, submits it and… that's not the right page. He should see a "thank you" page after submitting the form. Instead he sees a page with the header and footer of the website, but without any content on it.

That's not good.

I go into the test environment and submit the form with exactly the same data. I end up on the same blank page, and sure enough my submission isn't saved into the database. is this page? I've never seen it before. So I look at the logs. An error: `null passed instead of boolean`. That's a type error deep down in _my_ code.

---

Before moving on, let me tell you about the error, why it happened and why no one knew of it. 

So this form had a bunch of input fields, including check boxes. Whenever a lead request was submitted, we captured the data and prepared to save it into the database. It looked something like this:

```php
$formSubmission = new <hljs type>FormSubmission</hljs>();

$formSubmission-><hljs prop>name</hljs> = $form-><hljs prop>get</hljs>('name');
$formSubmission-><hljs prop>email</hljs> = $form-><hljs prop>get</hljs>('email');
// …

$orm-><hljs prop>save</hljs>($formSubmission);
```

The bug, simple as it may sound, was a simple typo:

```
$formSubmission-><hljs prop>more_feedback_required</hljs> = 
    $form-><hljs prop>get</hljs>('more_feedback_requierd');
```

Can you spot the `requierd` when calling `$form-><hljs prop>get</hljs>()`?

As it turns out, our form library — written by us for our in-house framework — wouldn't give an error when you're trying to get a unknown property, it simply returns `null`. 

The database however expected a boolean value, and thus an error was thrown.

This isn't that big of a problem, albeit not that no one knew of it. The second part of the bug was the empty page: this one was served directly from nginx whenever an error would occur — we didn't want visitors to be bothered by error pages. It turned out that one of my colleagues had set it up a few months ago, and I didn't know of it. When a lead saw the empty page, they probably thought the form was submitted just fine, they didn't know there should be a "thank you" page instead. We never saw the error happening during testing, because no one (including myself) thought about checking that checkbox: it was only conditionally shown if another checkbox was enabled in a previous step in the form; so the erroneous code never ran during testing.

Finally we also weren't automatically notified of errors in production, which meant no one knew about it until the client complained. 

---

I deployed a fix for the typo (which I wrote to begin with), I told my manager about the problem and that it's fixed. In turn he asks me how many people were affected by it. I tell him to check up on this. I go back to my desk and check when this bug was committed and deployed to production. A month ago.

I remember a sickening feeling starting to rise from this point on.

I check the log files, and scan for the error. I'm stunned for a few moments. It's not just a few errors. It's hundreds. Hundreds of leads that we lost over the past month. I started to shake a little. After a few inutes I reported back to my manager. I again explained the bug itself, why it had been undetected for a month, and finally gave him the hard numbers. There was nothing we could do: those leads were lost. 

## The aftermath

The following days were probably the most stressful in my career up until today. I'm thankful that no one blamed me directly: we all agreed that this was the team's responsibility and not my own. Still I was the one who wrote the typo, I was the one tasked with testing and deploying the changes to the form a month earlier.

I can honestly tell you that I had a hard time dealing with this feeling of guilt, even though my manager and colleagues were understanding.

I could of course point fingers to others: we shouldn't have used our own framework with its quirky form validation; devops should have installed some kind of error tracking; the empty error page should never have existed. I admit I thought all these thoughts the days after, I even felt anger towards some of my colleagues for a while. But I learned that anger didn't get me anywhere. I better spent my time looking at how _I_ could learn from all of this, instead of pointing fingers at others.

So, after a few weeks of processing, what felt to me like a trauma; I found the courage to look at what I could have done differently. What I could learn from this.

First of: ask my colleagues for reviews. It doesn't matter if I'm a junior or senior; a fresh look on my code is always valuable. I've learned that most people are busy themselves and won't help you unless you specifically ask them to. To this day I often ask my colleagues to review a PR. Even when I might have more years of experience, I highly value my colleagues' input. No matter how long I've been doing this, I still make mistakes, and I shouldn't be too proud to admit that.

Next: I don't use tools I don't know or trust. This [wasn't the first time](/blog/dont-write-your-own-framework) I stumbled upon the limitations of a custom, in-house framework. I've learned that it's better to use tools that are supported by a large open source community. It wasn't my decision which framework to use on that particular project, but it was a requirement for my next job: I only want to use well-known, community supported frameworks when it comes to client projects in production.

Third: I should test better myself, I shouldn't assume that a checkbox is just a checkbox. I should be skeptical towards every line of code I write. This incident is one of the reasons I came to love strongly typed systems more and more. I don't want a language that tries to be smart and juggle types here and there. I want a language that crashes hard when something happens that shouldn't happen, the stronger the type system, the better.

Finally: I tend to code more defensively. I constantly ask myself: are null values possible? Anything unexpected that could happen? I prefer to add an extra check rather than assuming it'll work fine.

---

It's strange writing this blogpost. I was a young developer when this happened to me, and it's something that I think has had an lasting impact on my programming career. I also think we don't talk about this often enough. I lay awake at night thinking about how angry the client would be, what the impact would be on the company I worked for. Even though I realise most of my feelings were exaggerated, they still were there. I wasn't able to just turn them off. I felt fear and shame, and had little people to talk about it.

We write about our success stories, but what about our mistakes? What about the impact of these kinds of experiences on our mental health? Many of us in the software industry are introverts, or feel like we can't talk about our deepest emotions. 

Let's keep an eye out for each other instead. 

{{ cta:diary }}
