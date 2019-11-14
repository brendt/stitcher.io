- [00. Preface](#preface)
- [01. Domains](/blog/laravel-beyond-crud-01-domain-oriented-laravel)
- [02. Working with data](/blog/laravel-beyond-crud-02-working-with-data)
- [03. Actions](/blog/laravel-beyond-crud-03-actions)
- [04. Models](/blog/laravel-beyond-crud-04-models)
- <span>05. Managing Domains</span>
- <span>06. Models with the state pattern</span>
- <span>07. Entering the application layer</span>
- <span>08. View models</span>
- More chapters are in the making

{{ ad:carbon }}

## Preface

> A blog series for PHP developers working on larger-than-average Laravel projects 

I've been writing and maintaining several larger-than-average web applications for years now. These are projects that take a team of developers to work on it for at least a year, often times longer. They are projects that take more than the well-known Laravel CRUD approach to stay maintainable.

In this time I've looked at several architectures which would help me and our team improve the maintainability of these projects, as well as help make the development more easy, both for us and our clients: DDD, Hexagonal Architecture, Event Sourcing.

Because most of these projects were large, yet not ginormous, these paradigms as a whole were almost always overkill. On top of that we were still dealing with fixed deadlines, meaning we couldn't spend ages on fine tuning the architecture.

In general, these were projects with a development lifespan of six months to one year, with a team of three to six developers working on them simultaneously. After going live, most of these projects are still heavily worked on for years to come.

In this series, I'll write about the knowledge we gained over the years in designing these projects. I will take a close look at the Laravel way, and what did and didn't work for us.
This series is for you if you're dealing with these larger Laravel projects, and want practical and pragmatic solutions in managing it.

I will talk about theory, patterns and principles, though everything will be in context of a real-life, working web application.

The goal of this series is to hand you concrete solutions to real life problems, things you can start doing different in your projects today. Enjoy!

## About me

My name is Brent, I'm a 25-year old web developer living in Belgium. I've been writing PHP professionally for the past 5 years, and have been programming since I was 13 years old.

As a professional, I've mainly worked on medium- to large sized web applications and APIs. Right now I work with Laravel at a company called [Spatie](*https://spatie.be), and before that I worked with both Symfony and company-specific frameworks.
