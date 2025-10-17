I voiced my preference for the recent [interface default methods RFC](https://www.youtube.com/watch?v=lXsbFXYwxWU), and many people told me I was wrong: an interface is only a contract and shouldn't provide implementations. 

They are, of course, right, but only half. Let's talk about object relations.

You can think of the classic class/interface relation as an `<hljs keyword>Is A</hljs>` relation: you can say `<hljs type>Item</hljs> <hljs keyword>Is A</hljs> <hljs type>Purchasable Item</hljs>`, or, in technical terms: `<hljs keyword>class</hljs> <hljs type>Item</hljs> <hljs keyword>implements</hljs> <hljs type>Purchasable</hljs>`.

I don't think there's any disagreement here amongst developers, this is the classic definition of an interface. It allows us to write code that works with all types of purchasables, without worrying which concrete implementation we're dealing with.

```php
function createPayment(<hljs type>Purchasable</hljs> $purchasable): <hljs type>Payment</hljs>
{
    $price = $purchasable-><hljs prop>getPrice</hljs>();
    
    // …
}
```

This is where people who argue against interface default methods stop. If this is the only way you're using interfaces, then yes, you're right: interface default methods aren't necessary.

However, there is another way interfaces are used. And mind you: I'm not saying "there is another way interfaces _can_ be used", no, I'm saying this _is_ happening in modern PHP code, today, in many projects.

Here goes. Interfaces can be used to model an `<hljs keyword>Acts As</hljs>` relation — I have a perfect example, thanks to [Larry](*https://www.garfieldtech.com/blog/beyond-abstract).

Here's the so called `<hljs type>LoggerInterface</hljs>`, part of [PSR-3](*https://www.php-fig.org/psr/psr-3/):

```php
interface LoggerInterface
{
    public function emergency(
        <hljs type>string|\Stringable</hljs> $message, 
        <hljs type>array</hljs> $context = []
    ): <hljs type>void</hljs>;

    public function alert(
        <hljs type>string|\Stringable</hljs> $message, 
        <hljs type>array</hljs> $context = []
    ): <hljs type>void</hljs>;

    public function critical(
        <hljs type>string|\Stringable</hljs> $message, 
        <hljs type>array</hljs> $context = []
    ): <hljs type>void</hljs>;

    public function error(
        <hljs type>string|\Stringable</hljs> $message, 
        <hljs type>array</hljs> $context = []
    ): <hljs type>void</hljs>;

    public function warning(
        <hljs type>string|\Stringable</hljs> $message, 
        <hljs type>array</hljs> $context = []
    ): <hljs type>void</hljs>;

    public function notice(
        <hljs type>string|\Stringable</hljs> $message, 
        <hljs type>array</hljs> $context = []
    ): <hljs type>void</hljs>;

    public function info(
        <hljs type>string|\Stringable</hljs> $message, 
        <hljs type>array</hljs> $context = []
    ): <hljs type>void</hljs>;

    public function debug(
        <hljs type>string|\Stringable</hljs> $message, 
        <hljs type>array</hljs> $context = []
    ): <hljs type>void</hljs>;

    public function log(
        $level, 
        <hljs type>string|\Stringable</hljs> $message, 
        <hljs type>array</hljs> $context = []
    ): <hljs type>void</hljs>;
}
```

As you can see, most methods are essentially shortcuts for the `<hljs prop>log</hljs>` method: they are convenience methods so that you don't have to manually provide a logging level. It's a great design choice to include in the interface, as it forces all implementations to have better accessibility. 

However, let's be honest, no one is ever going to need a logger with a different implementation of `<hljs prop>debug</hljs>` or `<hljs prop>info</hljs>` or any of the other shorthands. These methods will _always_ look the same:

```php
public function debug(
    <hljs type>string|\Stringable</hljs> $message, 
    <hljs type>array</hljs> $context = []
): <hljs type>void</hljs>
{
    $this-><hljs prop>log</hljs>(<hljs type>LogLevel</hljs>::<hljs prop>DEBUG</hljs>, $message, $context);
}
```

In essence, this `<hljs type>LoggerInterface</hljs>` is not only describing an `<hljs keyword>Is A</hljs>` relation — if that were the case we'd only need the `<hljs prop>log</hljs>` method. No, it also describes an `<hljs keyword>Acts As</hljs>` relation: a concrete logger implementation can `<hljs keyword>Act As</hljs>` as proper logger, including the convenience methods associated with it. `<hljs type>FileLogger</hljs> <hljs keyword>Acts As</hljs> <hljs type>LoggerInterface</hljs>`, `<hljs type>Monolog</hljs> <hljs keyword>Acts As</hljs> <hljs type>LoggerInterface</hljs>`.

It all boils down to the question: is this a valid way of writing and using interfaces? I would say, yes. 

I'd encourage you to take a close look at your own projects, and be totally honest for a moment: are any of your interfaces describing an `<hljs keyword>Acts As</hljs>` relationship? If they do, then you cannot make the case against interface default methods. 