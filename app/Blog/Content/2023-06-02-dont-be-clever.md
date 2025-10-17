---
title: "Don't be clever"
meta:
    description: 'The story of the CRUDController'
    template: blog/meta/crudcontroller.twig
---

Ten years ago, I wrote the most beautiful, clever, over-engineered piece of code ever. I was building a REST API for a startup, and discovered lots of repetition between controllers: I was building the same kind of actions and copying code over and over again. So I came up with a solution: _The CRUDController™_.

It was a beautifully complex, abstract class that integrated with Doctrine (the ORM that I was using back then). It had every possible CRUD operation you could think of — not just for entities, but also for child- and has many relations; there were automatic overviews, filtering, pagination, validation, data persistence, routing, and whatnot.

And the only thing I had to do was to create a new controller, extend from my amazing _CRUDController™_, provide an entity class, and be done.

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

Except, of course: exceptions started to emerge. Not the programming kind, but the business kind. _Some_ controllers had to do _some_ things a little differently. It was small things at first: different URL schemes, different kinds of validation; but soon, things grew more and more complex: support for nested entities or complex filtering, to name a few.

And young me? I just kept going. Adding the proverbial knobs and pulls to my abstract class (which was growing into a _set_ of classes by now).

In the end, I created a monster; and — ironically — it had taken more time than if I had simply copied code between controllers over and over again. On top of that: I was leaving the startup, and no one really understood how more than 50 controllers actually worked.

You might assume I understood, but let me be clear: I didn't really know how much of it worked anymore.

Yes, I had failed to see the proper solution: a class generator — so that I didn't have to manually copy code again. It actually existed back then, I simply didn't know about it, no one told me about it, and I wasn't smart enough to question myself once I started going down a certain path.

It's such a classic example, many of you probably recognise it. I wouldn't say my coding skills were bad, by the way. No, I didn't question myself enough, I lacked self-reflection and wasn't able to critically look at my own ideas.

I wish I could show you _The CRUDController™_'s source code; but I don't have access to it anymore, unfortunately. Luckily, the memory still exists. And it's a memory I remind myself of very often when I'm going down the path of abstractions and complications. It's often enough to hold me back and look for better solutions. 

{{ cta:mail }}
