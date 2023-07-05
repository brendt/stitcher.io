Hi there, it's time for another newsletter! In today's roundup: I made some progress on that procedurally generated game I hinted at previously, PhpStorm's EAP, some thoughts about type systems, PHP Core updates, and more; but first, I want to tell you the story of the CRUDController.

---

Ten years ago, I wrote the most beautiful, clever, over-engineered piece of code ever. I was building a REST API for a startup, and discovered lots of repetition between controllers: I was building the same kind of actions and copying code over and over again. So I came up with a solution: _The CRUDController_.

It was a beautifully complex, abstract class that integrated with Doctrine (the ORM that I was using back then). It had every possible CRUD operation you could think of — not just for entities, but also for child- and has many relations; there were automatic overviews, filtering, pagination, validation, data persistence, routing, and what not.

And the only thing I had to do was to create a new controller, extend from my amazing _CRUDController_, provide an entity class, and be done.

```php
class TimesheetController extends CRUDController
{
    public function getEntity(): string
    {
        return <hljs type>Timesheet</hljs>::class;
    }
}
```

Such a blast!

Expect, of course: exceptions started to emerge. Not the programming kind, but the business kind. _Some_ controllers had to do _some_ things a little differently. It were small things at first: different URL schemes, different kinds of validation; but soon, things grew more and more complex: support for nested entities or complex filtering, to name a few.

And young me? I just kept going. Adding the proverbial knobs and pulls to my abstract class (which was growing into a _set_ of classes by now).

In the end, I created a monster; and — ironically — it had taken more time than if I had simply copied code between controllers over and over again. On top of that: I was leaving the startup, and no one really understood how more than 50 controllers actually worked.

You might assume I understood, but let me be clear: I didn't really know how much of it worked anymore.

Yes, I had failed to see the proper solution: a class generator — so that I didn't have to manually copy code again. It actually existed back then, I simply didn't know about it, no one told me about it, and I wasn't smart enough to question myself once I started going down a certain path.

It's such a classic example, many of you probably recognise it. I wouldn't say my coding skills were bad, by the way. No, I didn't question myself enough, I lacked self-reflection and wasn't able to critically look at my own ideas.

I wish I could show you _The CRUDController_'s source code; but I don't have access to it anymore, unfortunately. Luckily, the memory still exists. And it's a memory I remind myself of very often when I'm going down the path of abstractions and complications. It's often enough to hold me back and look for better solutions.

## Roundup

- **["The problem with `final` is mocking"](https://aggregate.stitcher.io/links/ac902c9b-d3ab-41f7-8662-4c47a6c89494)**
- **[Building a procedurally generated game with PHP](https://aggregate.stitcher.io/post/b2cc2d08-0868-463b-be4f-fd528fc2b171)**
- **[Explaining the code on the "Go with PHP" website](https://aggregate.stitcher.io/post/27c66321-e1b5-495e-9500-73780e09af02)**
- **[Build Your Own Service Container in PHP](https://aggregate.stitcher.io/post/e38b13e5-e40a-4a7f-a995-bf63b3388f31)** 
- **[Convincing you about typed languages and static analysis](https://aggregate.stitcher.io/post/91a979f4-14f0-4b54-a6ba-5231ce172747)**
- **[Five Ways Automated Testing Catches Issues Humans Can’t](https://aggregate.stitcher.io/post/e342ec40-efe4-49e0-8400-0bcf118bf558)**
- **[The PhpStorm 2023.2 Early Access Program Is Open](https://aggregate.stitcher.io/post/37bf5c10-1a9f-403d-96d2-37278a6dea78)**
- **[PHP in 2023](https://aggregate.stitcher.io/post/86a04694-3805-41bd-8eae-6a63ef11cfd0)**
- **[PHP Core Roundup #12](https://aggregate.stitcher.io/post/c36b7a2f-9341-4c4b-86a1-5699a066c60b)**

---

That's it for today's newsletter, have a great weekend!

Brent