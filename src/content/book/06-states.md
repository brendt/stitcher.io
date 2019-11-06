> The state pattern is one of the best ways to add state-specific behaviour to models, while still keeping them clean.

After the philosophical chapter 5, it's time to dive back into the code. This chapter will talk about the state pattern, and specifically how to apply it to models. You can think of this chapter as an extension to [chapter 4](/blog/laravel-beyond-crud-04-models), where I wrote about how we aim to keep our model classes conceivable by them not handling business logic.

Moving business logic away from models poses a problem though, a very common use case are states: an invoice can be pending or paid, a payment can be failed or succeeded. Depending on the state, a model must behave differently; how do we bridge this gap between models and business logic?

States, and transitions between them, are a frequent use case in large projects, so frequent that they deserve a chapter on their own.

## The state pattern

At its core, the state pattern is a simple pattern, yet it allows for very powerful functionality. Let's take the example of invoices again: an invoice can be pending or paid. In 
