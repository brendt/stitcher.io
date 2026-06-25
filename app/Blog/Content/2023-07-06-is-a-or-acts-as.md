---
title: '"Is A" or "Acts As"'
disableAds: true
---

I voiced my preference for the recent [interface default methods RFC](https://www.youtube.com/watch?v=lXsbFXYwxWU), and many people told me I was wrong: an interface is only a contract and shouldn't provide implementations. 

They are, of course, right, but only half. Let's talk about object relations.

You can think of the classic class/interface relation as an `Is A` relation: you can say `Item Is A Purchasable Item`, or, in technical terms: `class Item implements Purchasable`.

I don't think there's any disagreement here amongst developers, this is the classic definition of an interface. It allows us to write code that works with all types of purchasables, without worrying which concrete implementation we're dealing with.

```php
function createPayment(Purchasable $purchasable): Payment
{
    $price = $purchasable->getPrice();
    
    // …
}
```

This is where people who argue against interface default methods stop. If this is the only way you're using interfaces, then yes, you're right: interface default methods aren't necessary.

However, there is another way interfaces are used. And mind you: I'm not saying "there is another way interfaces _can_ be used", no, I'm saying this _is_ happening in modern PHP code, today, in many projects.

Here goes. Interfaces can be used to model an `Acts As` relation — I have a perfect example, thanks to [Larry](*https://www.garfieldtech.com/blog/beyond-abstract).

Here's the so called `LoggerInterface`, part of [PSR-3](*https://www.php-fig.org/psr/psr-3/):

```php
interface LoggerInterface
{
    public function emergency(
        string|\Stringable $message, 
        array $context = []
    ): void;

    public function alert(
        string|\Stringable $message, 
        array $context = []
    ): void;

    public function critical(
        string|\Stringable $message, 
        array $context = []
    ): void;

    public function error(
        string|\Stringable $message, 
        array $context = []
    ): void;

    public function warning(
        string|\Stringable $message, 
        array $context = []
    ): void;

    public function notice(
        string|\Stringable $message, 
        array $context = []
    ): void;

    public function info(
        string|\Stringable $message, 
        array $context = []
    ): void;

    public function debug(
        string|\Stringable $message, 
        array $context = []
    ): void;

    public function log(
        $level, 
        string|\Stringable $message, 
        array $context = []
    ): void;
}
```

As you can see, most methods are essentially shortcuts for the `log` method: they are convenience methods so that you don't have to manually provide a logging level. It's a great design choice to include in the interface, as it forces all implementations to have better accessibility. 

However, let's be honest, no one is ever going to need a logger with a different implementation of `debug` or `info` or any of the other shorthands. These methods will _always_ look the same:

```php
public function debug(
    string|\Stringable $message, 
    array $context = []
): void
{
    $this->log(LogLevel::DEBUG, $message, $context);
}
```

In essence, this `LoggerInterface` is not only describing an `Is A` relation — if that were the case we'd only need the `log` method. No, it also describes an `Acts As` relation: a concrete logger implementation can `Act As` as proper logger, including the convenience methods associated with it. `FileLogger Acts As LoggerInterface`, `Monolog Acts As LoggerInterface`.

It all boils down to the question: is this a valid way of writing and using interfaces? I would say, yes. 

I'd encourage you to take a close look at your own projects, and be totally honest for a moment: are any of your interfaces describing an `Acts As` relationship? If they do, then you cannot make the case against interface default methods. 