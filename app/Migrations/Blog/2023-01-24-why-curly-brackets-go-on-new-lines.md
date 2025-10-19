<p><iframe width="560" height="422" src="https://www.youtube.com/embed/763ogjW2Fk0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></p>


This is a curly bracket, or brace: `{`.

It’s rarely used as a punctuation mark, but it is one of the most used symbols in programming languages, where they are used to group code and create scopes. It’s also one of the more debated topics when it comes to code styles.

The question is simple: should an opening brace go on a new line or not? You might think: it’s all personal preference; but I would say: it’s not.

Take a look at this code snippet:

```php
public function __construct(<hljs type>string</hljs> $publicDirectory, <hljs type>string</hljs> $configurationFile, <hljs type>PageParser</hljs> $pageParser, <hljs type>PageRenderer</hljs> $pageRenderer) {
    // ...
}
```

It’s a constructor that takes a couple of arguments. So what's wrong with this code? Well first of all, you probably have to scroll sideways to read it. That's a bad thing. Scrolling requires an extra interaction with your code. You'll have to consciously search for information about the arguments of this method. That time distracts you from focusing on the application code.

Second, if you're into web development, you probably know that people don't read text, they scan. Usually from left to right and top to bottom. This is especially true for websites, but the same goes for reading code. Putting important information to the right makes it more difficult to find.

In case of this argument list, all arguments are equally important; yet a lot of useful information is pushed to that right, blurry side, where our focus isn’t by default.

So how do we pull useful information more to the left?

```php
public function __construct(<hljs type>string</hljs> $publicDirectory,
                            <hljs type>string</hljs> $configurationFile,
                            <hljs type>PageParser</hljs> $pageParser,
                            <hljs type>PageRenderer</hljs> $pageRenderer) {
    // ...
}
```

This could be the first solution you think about. But it doesn't really scale. As soon as you're refactoring a method name, the alignment breaks. Say we want to make this a static constructor instead of a normal one.

```php
public static function create(<hljs type>string</hljs> $publicDirectory,
                            <hljs type>string</hljs> $configurationFile,
                            <hljs type>PageParser</hljs> $pageParser,
                            <hljs type>PageRenderer</hljs> $pageRenderer) {
    // ...
}
```

See the alignment breaking?

Another issue is that arguments are still pushed rather far to the right; so let's take a look at another approach.

```php
public function __construct(
    <hljs type>string</hljs> $publicDirectory, <hljs type>string</hljs> $configurationFile,
    <hljs type>PageParser</hljs> $pageParser, <hljs type>PageRenderer</hljs> $pageRenderer) {
    // ...
}
```

The advantage here is that our alignment issue is solved. However, how will you decide how many arguments should go on one line? Will you make some styling guidelines about this? How will you enforce them? This example has four arguments, but what if it had three or five?

```php
public function __construct(
    <hljs type>string</hljs> $publicDirectory, <hljs type>string</hljs> $configurationFile, 
    <hljs type>string</hljs> $cachePath, <hljs type>PageParser</hljs> $pageParser, 
    <hljs type>PageRenderer</hljs> $pageRenderer) {
    // ...
}
```


Consistency is key. If we can find a consistent rule, we don't have to think about it anymore. And like I said before, if we don't have to think about it, there's room in our heads for more important things.

So let's continue our search for consistency.

```php
public function __construct(
    <hljs type>string</hljs> $publicDirectory,
    <hljs type>string</hljs> $configurationFile,
    <hljs type>PageParser</hljs> $pageParser,
    <hljs type>PageRenderer</hljs> $pageRenderer) {
    $this-><hljs prop>publicDirectory</hljs> = <hljs prop>rtrim</hljs>($publicDirectory, '/');
    $this-><hljs prop>configurationFile</hljs> = $configurationFile;
    $this-><hljs prop>pageParser</hljs> = $pageParser;
    $this-><hljs prop>pageRenderer</hljs> = $pageRenderer;
}
```

By giving each argument its own line, we solve all our problems. But we also created a new one: it's now more difficult to distinguish between the argument list and the method body.

I can illustrate it for you. Let's replace all characters in this code with X's:

```txt
XXXXXX XXXXXXXX __XXXXXXXXX(
    XXXXXX XXXXXXXXXXXXXXXX,
    XXXXXX XXXXXXXXXXXXXXXXXX,
    XXXXXXXXXX XXXXXXXXXXX,
    XXXXXXXXXXXX XXXXXXXXXXXXX) {
    XXXXXXXXXXXXXXXXXXXXXX = XXXXXXXXXXXXXXXXXXXXXXXXXXXX;
    XXXXXXXXXXXXXXXXXXXXXXXX = XXXXXXXXXXXXXXXXXX;
    XXXXXXXXXXXXXXXXX = XXXXXXXXXXX;
    XXXXXXXXXXXXXXXXXXX = XXXXXXXXXXXXX;
}
```


Can you see how difficult it becomes to spot where the argument list ends and the method body starts?

You might say "there's still the curly bracket on the right indicating the end". But that’s not where our focus is! We want to keep the important information to the left. How do we solve it? It turns out there is one true place where to put your curly brackets:

```txt
XXXXXX XXXXXXXX __XXXXXXXXX(
    XXXXXX XXXXXXXXXXXXXXXX,
    XXXXXX XXXXXXXXXXXXXXXXXX,
    XXXXXXXXXX XXXXXXXXXXX,
    XXXXXXXXXXXX XXXXXXXXXXXXX
) {
    XXXXXXXXXXXXXXXXXXXXXX = XXXXXXXXXXXXXXXXXXXXXXXXXXXX;
    XXXXXXXXXXXXXXXXXXXXXXXX = XXXXXXXXXXXXXXXXXX;
    XXXXXXXXXXXXXXXXX = XXXXXXXXXXX;
    XXXXXXXXXXXXXXXXXXX = XXXXXXXXXXXXX;
}
```

On a new line. Placing curly brackets on new lines gives our code space to breathe. It creates a visual boundary between argument lists and method bodies, it helps us to focus on things that matter.

```php
public function __construct(
    <hljs type>string</hljs> $publicDirectory,
    <hljs type>string</hljs> $configurationFile,
    <hljs type>PageParser</hljs> $pageParser,
    <hljs type>PageRenderer</hljs> $pageRenderer
) {
    $this-><hljs prop>publicDirectory</hljs> = <hljs prop>rtrim</hljs>($publicDirectory, '/');
    $this-><hljs prop>configurationFile</hljs> = $configurationFile;
    $this-><hljs prop>pageParser</hljs> = $pageParser;
    $this-><hljs prop>pageRenderer</hljs> = $pageRenderer;
}
```

<p><iframe width="560" height="422" src="https://www.youtube.com/embed/763ogjW2Fk0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></p>