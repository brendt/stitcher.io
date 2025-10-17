---
title: 'Unfair Advantage'
meta:
    canonical: 'https://tempestphp.com/blog/unfair-advantage/'
    description: 'Why Tempest instead of Symfony or Laravel?'
---

__Note: this post was originally published on [the Tempest blog](https://tempestphp.com/blog/unfair-advantage/)__


Someone asked me: [_why Tempest_](https://bsky.app/profile/laueist.bsky.social/post/3l7y5v3bm772y)? What areas do I expect Tempest to be better in than Laravel or Symfony? What gives me certainty that Laravel or Symfony won't just be able to copy what makes Tempest currently unique? What is Tempest's _unfair advantage_ compared to existing PHP frameworks?

I love this question: of course there is already a small group of people excited and vocal about Tempest, but does it really stand a chance against the real frameworks?

Ok so, here's my answer: Tempest's unfair advantage is **its ability to start from scratch and the courage to question and rethink the things we have gotten used to**.

Let me work through that with a couple of examples.

## The Curse

The curse of any mature project: with popularity comes the need for _backwards compatibility_. Laravel can't make 20 breaking changes over the course of one month; they can't add modern PHP features to the framework without making sure 10 years of code isn't affected too much. They have a huge userbase, and naturally prefer stability. If Tempest ever grows popular enough, we will have to deal with the same problem, we might make some different decisions when it comes to backwards compatibility, but for now it opens opportunities.

Combine that with the fact that Tempest started out in 2023 instead of 2011 as Laravel did or 2005 as Symfony did. PHP and its ecosystem have evolved tremendously. Laravel's facades are a good example: there is a small group of hard-core fans of facades to this day; but my view on facades (or better: service locators disguised behind magic methods) is that they represent a pattern that made sense at a time when PHP didn't have a proper type system (so no easy autowiring), where IDEs were a lot less popular (so no autocompletion and auto importing), and where static analysis in PHP was non-existent.

It makes sense that Laravel tried to find ways to make code as easy as possible to access within that context. Facades reduced a lot of friction during an era where PHP looked entirely different, and where we didn't have the language capabilities and tooling we have today.

That brings us back to the backwards compatibility curse: over the years, facades have become so ingrained into Laravel that it would be madness to try remove them today. It's naive to think the Tempest won't have its facade-like warts ten years from now — it will — but at this stage, we're lucky to be able to start from scratch where we can embrace modern PHP as the standard instead of the exception; and where tooling like IDEs, code formatters, and static analysers have become an integral part of PHP. To make that concrete:

- Tempest relies on attributes wherever possible, not as an option, but as the standard.
- We embraced enums from the start, and don't have to worry about supporting older variants.
- Tempest relies much more on reflection; its performance impact has become insignificant since the PHP 7 era.
- We can use the type system as much as possible: for dependency autowiring, console definitions, ORM and database models, event and command handlers, and more.

That _clean slate_ is an unfair advantage. Of course, it means nothing if you cannot convince enough people about the benefits of _your_ solution. That's where the second part comes in.

## The courage to question

The second part of Tempest's unfair advantage is the courage to question and rethink the things we have gotten used to. One of the best examples to illustrate this is `symfony/console`: the de-facto standard for console applications in PHP for over a decade. It's used everywhere, and it has the absolute monopoly when it comes to building console applications in PHP.

So I thought… what if I had to build a console framework today from scratch? What would that look like? Well, here's what a console command looks like in Symfony today:

```php
#[AsCommand(name: 'make:user')]
class MakeUserCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
            ->addOption('admin', null, InputOption::VALUE_NONE);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $this->getArgument('email');
        $password = $this->getArgument('password');
        $isAdmin = $this->getOption('admin');
        
        // …  
    
        return Command::SUCCESS;
    }
}
```

The same command in Laravel would look something like this:

```php
class MakeUser extends Command
{
    protected $signature = 'make:user {email} {password} {--admin}';
 
    public function handle(): void
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $isAdmin = $this->option('admin');
    
        // …
    }
}
```

And here's Tempest's approach:

```php
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final readonly class Make
{   
    use HasConsole;
    
    #[ConsoleCommand]
    public function user(string $email, string $password, bool $isAdmin): void 
    {
        // …
    }
}
```

Which differences do you notice?

- Compare the verbose `configure()` method in Symfony, vs Laravel's `$definition` string, vs Tempest's approach. Which one feels the most natural? The only thing you need to know in Tempest is PHP. In Symfony you need a separate configure method and learn about the configuration API, while in Laravel you need to remember the textual syntax for the definition command. That's all unnecessary boilerplate. Tempest skips all the boilerplate, and figures out how to build a console definition for you based on the PHP parameters you actually need. That's what's meant when we say that "Tempest gets out of your way". The framework helps you, not the other way around.
- Another difference is that Laravel's `Command` class extends from Symfony's implementation, which means its constructor isn't free for dependency injection. It's one of the things I dislike about Laravel: the convention that `handle()` methods can have injected dependencies. It's so confusing compared to other parts of the framework where dependencies are injected in the constructor. In Tempest, console commands don't extend from any class — in fact nothing does — there's a very good reason for this, inspired by Rust. If you want to learn more about that, you can watch me explain it [here](https://www.youtube.com/watch?v=HK9W5A-Doxc). The result is that any project class' constructor is free to use for dependency injection, which is the most obvious approach.
- Symfony's console commands must return an exit code — an integer. It's probably because of compatibility reasons that it's an int and not an enum. You can optionally return an exit code in Tempest as well, but of course it's an enum:

```php
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\ExitCode

final readonly class Package
{
    use HasConsole;
    
    #[ConsoleCommand]
    public function all(): ExitCode 
    {
        if (! $this->hasBeenSetup()) {
            return ExitCode::ERROR;
        }
        
        // …
        
        return ExitCode::SUCCESS;
    }
}
```

- Something that's not obvious from these code samples is the fact that one of Tempest's more powerful features is [discovery](https://tempestphp.com/docs/internals/discovery/): Tempest will discover classes like controllers, console commands, view components, etc. for you, without you having to configure them anywhere. It's a really powerful feature that Symfony doesn't have, and Laravel only applies to a very limited extent.
- Finally, a feature that's not present in Symfony nor Laravel are console command middlewares. They work exactly as you expect them to work, just like HTTP middleware: they are executed in between the command invocation and handling. You can build you own middleware, or use some of Tempest's built-in middleware:

```php
use Tempest\Console\Middleware\CautionMiddleware;

final readonly class Make
{   
    use HasConsole;
    
    #[ConsoleCommand(
        middleware: [CautionMiddleware::class]
    )]
    public function user(
        string $email, 
        string $password, 
        bool $isAdmin
    ): void {
        // …
        
        $this->success('Done!');
    }
}
```

Now, you may like Tempest's style or not, I realize there's a subjective part to it as well. Practice shows though that more and more people do in fact like Tempest's approach, some even go out of their way to tell me about it:

> I must say I really enjoy what little I have seen from the Tempest until now and my next free-time project is going to be build with it. I have 20 years of experience at building webpages with PHP and Tempest is surprisingly close to how I envision web-development should look in 2024.
> — [/u/SparePartsHere](https://www.reddit.com/r/PHP/comments/1gg99la/tempest_alpha_3_releases_with_installer_support/luprt9i/)

> I really like the way this framework turns out. It is THE framework in the PHP space out there for which I am most excited about […]
> — [Wulfheart](https://github.com/tempestphp/tempest-framework/issues/681)

## Decisions

Two months ago, I released the first alpha version of Tempest, making very clear that I was still uncertain whether Tempest would actually become _a thing_ or not. And, sure, there are some important remarks to be made:

- Tempest is still in alpha, there are bugs and missing features, there is a lot of work to be done.
- It's impossible to rival the feature set of Laravel or Symfony, our initial target audience is a much smaller group of developers and projects. That might change in the future, but right now it's a reality we need to embrace.

But.

I've also seen a lot of involvement and interest in Tempest since its first alpha release. A small but dedicated community has begun to grow. We now almost have 250 members on [our Discord](https://tempestphp.com/discord), the [GitHub repository](https://github.com/tempestphp/tempest-framework) has almost reached 1k stars, we've merged 82 pull requests made by 12 people this past month, with 300 merged pull requests in total.

On top of that, we have a strong core team of experienced open-source developers: [myself](https://github.com/brendt), [Aidan](https://github.com/aidan-casey), and [Enzo](https://github.com/innocenzi), flanked by another [dozen contributors](https://github.com/tempestphp/tempest-framework/graphs/contributors).

We also decided to make Tempest's individual components available as standalone packages, so that people don't have to commit to Tempest in full, but can pull one or several of these components into their projects — Laravel, Symfony, or whatever they are building. [`tempest/console`](https://tempestphp.com/console/) is probably the best example, but I'm very excited about [`tempest/view`](https://tempestphp.com/view/) as well, and [there are more](https://tempestphp.com/docs/framework/standalone-components/).

All of that to say, my uncertainty about Tempest becoming _a thing_ or not, is quickly dissipating. People are excited about Tempest, more than I expected. It seems they are picking up on Tempest's unfair advantage, and I am excited for the future.

