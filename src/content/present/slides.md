
<h1 class="generics-title">generics</h1>


---

## Brent 👋

@brendt_gd

---

![](/resources/img/slides/PhpStorm_icon.svg)

---

![](/resources/img/slides/annotated-logo.png)

---

![](/resources/img/slides/stitcher-logo-small.png)

---


<h1 class="generics-title">generics</h1>

---

# 🐛

---

## Tests

---

## Automation

---

## QA

---

## Types

---

```php
$posts = [];

<hljs blur>$posts[] = 1;
$posts[] = 'string';</hljs>
```
---

```php
<hljs blur>$posts = [];</hljs>

$posts[] = 1;
$posts[] = 'string';
```

---

```php
class PostCollection extends ArrayIterator
{
    public function current(): ?<hljs type>Post</hljs> { /* … */ }

    public function offsetGet(<hljs type>mixed</hljs> $key): ?<hljs type>Post</hljs> { /* … */ }

    public function offsetSet(<hljs type>mixed</hljs> $key, <hljs type>mixed</hljs> $value): void
    {
        if (! $value instanceof <hljs type>Post</hljs>) {
            throw new <hljs type>InvalidArgumentException</hljs>("…");
        }
        
        // …
    }
}
```

---

```txt
<hljs blur>class PostCollection extends ArrayIterator
{
    public function current():</hljs> ?<hljs type>Post</hljs> <hljs blur>{ <hljs comment>/* … */</hljs> }

    public function offsetGet(<hljs type>mixed</hljs> $key):</hljs> ?<hljs type>Post</hljs><hljs blur> { <hljs comment>/* … */</hljs> }

    public function offsetSet(<hljs type>mixed</hljs> $key, <hljs type>mixed</hljs> $value): void
    {</hljs>
        if (! $value <hljs keyword>instanceof</hljs> <hljs type>Post</hljs>) {
            <hljs keyword>throw</hljs> <hljs keyword>new</hljs> <hljs type>InvalidArgumentException</hljs>("…");
        }<hljs blur>
        
        <hljs comment>// …</hljs>
    }
}</hljs>
```

---

```php
$collection = new <hljs type>PostCollection</hljs>();

$collection[] = new <hljs type>Post</hljs>(1);

<hljs striped>$collection[] = 'abc';</hljs> <hljs error comment>// InvalidArgumentException</hljs>

foreach ($collection as $item) {
    echo $item-><hljs prop>getId</hljs>();
}
```

---

```txt
<hljs blur>$collection = new <hljs type>PostCollection</hljs>();

$collection[] = new <hljs type>Post</hljs>(1);</hljs>

<hljs striped>$collection[] = 'abc';</hljs> <hljs error comment>// InvalidArgumentException</hljs>

<hljs blur>foreach ($collection as $item) {
    echo $item-><hljs prop>getId</hljs>();
}</hljs>
```

---

```txt
<hljs blur>$collection = new <hljs type>PostCollection</hljs>();

$collection[] = new <hljs type>Post</hljs>(1);

$collection[] = 'abc';

foreach ($collection as $item) {
    echo </hljs>$item-><hljs prop>getId</hljs>()<hljs blur>;
}</hljs>
```

---

## Runtime

---

```txt
<hljs blur>$collection = new <hljs type>PostCollection</hljs>();</hljs>

$collection[] = $dataFromRequest;

<hljs blur>foreach ($collection as $item) {
    echo $item-><hljs prop>getId</hljs>();
}</hljs>
```

---


```php
class PostCollection extends ArrayIterator
{
    // …

    public function offsetSet(<hljs type>mixed</hljs> $key, <hljs type>mixed</hljs> $value): void
    {
        if (! $value instanceof <hljs type>Post</hljs>) {
            throw new <hljs type>InvalidArgumentException</hljs>("…");
        }
        
        // …
    }
}
```

---


```txt
<hljs blur>class PostCollection </hljs><hljs keyword>extends</hljs> <hljs type>ArrayIterator</hljs><hljs blur>
{
    <hljs comment>// …</hljs>

    public function offsetSet(<hljs type>mixed</hljs> $key, <hljs type>mixed</hljs> $value): void
    {
        if (! $value <hljs keyword>instanceof</hljs> <hljs type>Post</hljs>) {
            <hljs keyword>throw new</hljs> <hljs type>InvalidArgumentException</hljs>("…");
        }
     
        <hljs comment>// …</hljs>
    }
}</hljs>
```

---


```txt
<hljs blur>class PostCollection extends ArrayIterator
{
    <hljs comment>// …</hljs>

    public function offsetSet(<hljs type>mixed</hljs> $key, <hljs type>mixed</hljs> $value): void
    {</hljs>
        if (! $value <hljs keyword>instanceof</hljs> <hljs type>Post</hljs>) {
            <hljs keyword>throw new</hljs> <hljs type>InvalidArgumentException</hljs>("…");
        }
        <hljs blur>
        <hljs comment>// …</hljs>
    }
}</hljs>
```

---

```php
function createPostCollection(<hljs type>Post</hljs> ...$posts): PostCollection
{
    return new <hljs type>PostCollection</hljs>(...$posts);
}
```

---

```txt
<hljs blur>function createPostCollection(</hljs><hljs type>Post</hljs> ...$posts)<hljs blur>: PostCollection</hljs>
<hljs blur>{
    return new <hljs type>PostCollection</hljs>(...$posts);
}</hljs>
```

---

```php
$posts = <hljs prop>createPostCollection</hljs>(
    new <hljs type>Post</hljs>(<hljs prop>title</hljs>: 'Post A'),
    new <hljs type>Post</hljs>(<hljs prop>title</hljs>: 'Post B'),
);
```

---

```txt
<hljs blur>$posts = <hljs prop>createPostCollection</hljs>(</hljs>
    <hljs keyword>new</hljs> <hljs type>Post</hljs>(<hljs prop>title</hljs>: 'Post A'),
    <hljs keyword>new</hljs> <hljs type>Post</hljs>(<hljs prop>title</hljs>: 'Post B'),
<hljs blur>);</hljs>
```

---

```php
final <hljs keyword>readonly</hljs> class Post
{
    public function __construct(
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs comment>// …</hljs>
    ) {}
}
```

---

```txt
<hljs blur>final <hljs keyword>readonly</hljs> class Post
{
    public function __construct(</hljs>
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs comment>// …</hljs>
<hljs blur>    ) {}
}</hljs>
```

---

## More types

---

## More types = more certainty

---

## Without running it!

---

## Entry point

---

```php
$validated = $request-><hljs prop>validate</hljs>([
    'title' => ['required', 'unique:posts', 'max:255'],
    'body' => ['required'],
]);
```

---

## Static analysers

---

## PHPStan

---

## PHPStan

## Psalm

---

## PHPStan

## Psalm

## Phan

---

## PHPStan

## Psalm

## Phan

## PhpStorm

---

## Distinction

---

## PhpStorm = real-time

---

## Others = Bulk analysis

---

## Complement each other 🫂

---

![](/resources/img/slides/phpstorm-autocomplete-2.png)

---

![](/resources/img/slides/phpstan-error-2.png)


---

![](/resources/img/slides/phpstorm-error-1.png)

---

## They don't run it

---

<h1 class="generics-title">generics</h1>

---

## Types

---

## Types = overhead

---

```php
class Post {}
class Author {}
class NewsItem {}
class Feed {}
class User {}
class Message {}
class Like {}
class Comment {}
class Share {}
class Favorite {}
```

---

```php
class PostCollection extends ArrayIterator {}
class AuthorCollection extends ArrayIterator {}
class NewsItemCollection extends ArrayIterator {}
class FeedCollection extends ArrayIterator {}
class UserCollection extends ArrayIterator {}
class MessageCollection extends ArrayIterator {}
class LikeCollection extends ArrayIterator {}
class CommentCollection extends ArrayIterator {}
class ShareCollection extends ArrayIterator {}
class FavoriteCollection extends ArrayIterator {}
```

---

```php
class PostCollection extends Collection {}
class AuthorCollection extends Collection {}
class NewsItemCollection extends Collection {}
class FeedCollection extends Collection {}
class UserCollection extends Collection {}
class MessageCollection extends Collection {}
class LikeCollection extends Collection {}
class CommentCollection extends Collection {}
class ShareCollection extends Collection {}
class FavoriteCollection extends Collection {}
```

---

```php
abstract class Collection<<hljs generic>T</hljs>> extends ArrayIterator
{
    public function current(): ?<hljs generic>T</hljs> { /* … */ }

    public function offsetGet(<hljs type>mixed</hljs> $key): ?<hljs generic>T</hljs> { /* … */ }

    public function offsetSet(<hljs type>mixed</hljs> $key, <hljs type>mixed</hljs> $value): void
    {
        if (! $value instanceof <hljs generic>T</hljs>) {
            throw new <hljs type>InvalidArgumentException</hljs>("…");
        }
        
        // …
    }
}
```

---

```txt
<hljs blur>abstract class Collection</hljs><<hljs generic>T</hljs>> <hljs blur>extends ArrayIterator
{
    public function current(): </hljs>?<hljs generic>T</hljs><hljs blur> { <hljs comment>/* … */</hljs> }

    public function offsetGet(<hljs type>mixed</hljs> $key):</hljs> ?<hljs generic>T</hljs> <hljs blur>{ <hljs comment>/* … */</hljs> }

    public function offsetSet(<hljs type>mixed</hljs> $key, <hljs type>mixed</hljs> $value): void
    {</hljs>
        <hljs keyword>if</hljs> (! $value <hljs keyword>instanceof</hljs> <hljs generic>T</hljs>) {
            <hljs keyword>throw</hljs> <hljs keyword>new</hljs> <hljs type>InvalidArgumentException</hljs>("…");
        }<hljs blur>
        
        // …
    }
}</hljs>
```

---



```txt
$postCollection = <hljs keyword>new</hljs> <hljs type>Collection</hljs><<hljs generic>Post</hljs>>;
```

---

## Not possible!

```txt
<hljs blur>$postCollection = <hljs keyword>new</hljs> </hljs><hljs type>Collection</hljs><<hljs generic>Post</hljs>><hljs blur>;</hljs>
```

---

```php
$post = (new <hljs type>QueryBuilder</hljs><<hljs generic>Post</hljs>>)-><hljs prop>first</hljs>();
```

---

```php
function resolve(<hljs type>class-string</hljs><<hljs generic>Type</hljs>> $className): <hljs generic>Type</hljs> 
{
    // …
}
```

---

```txt
<hljs blur>function resolve(</hljs><hljs type>class-string</hljs><<hljs generic>Type</hljs>> <hljs blur>$className): </hljs><hljs generic>Type</hljs> 
<hljs blur>{
    // …
}</hljs>
```

---

## Not possible!

---

## Runtime

---

![](/resources/img/slides/phpstan.gif)

---

![](/resources/img/slides/indexing.gif)

---

## Runtime 💯

---

## Generics 🥹

---

## Monomorphic Generics

---

## Poly - Morph

---

## Mono - Morph

---

```txt
$collection = <hljs keyword>new</hljs> <hljs type>Collection</hljs><<hljs generic>Post</hljs>>;

<hljs blur>$collection = <hljs keyword>new</hljs> <hljs type>Collection_TPost</hljs>;</hljs>
```

---

```txt
<hljs blur>$collection = <hljs keyword>new</hljs> <hljs type>Collection</hljs><<hljs generic>Post</hljs>>;</hljs>

$collection = <hljs keyword>new</hljs> <hljs type>Collection_Post</hljs>;
```

---

```txt
<hljs blur>$collection = <hljs keyword>new</hljs> <hljs type>Collection</hljs><<hljs generic>Post</hljs>>;</hljs>

$collection = <hljs keyword>new</hljs> <hljs type>Collection_Post</hljs>;
$collection = <hljs keyword>new</hljs> <hljs type>Collection_Comment</hljs>;
```

---

```txt
<hljs blur>$collection = <hljs keyword>new</hljs> <hljs type>Collection</hljs><<hljs generic>Post</hljs>>;</hljs>

$collection = <hljs keyword>new</hljs> <hljs type>Collection_Post</hljs>;
$collection = <hljs keyword>new</hljs> <hljs type>Collection_Comment</hljs>;
$collection = <hljs keyword>new</hljs> <hljs type>Collection_Like</hljs>;
```

--- 

## Resources 🥵

---

## Reified Generics

---

## Runtime Generics

---

## Refactor Required 🥵

---

## Increased Complexity 🥵

---

> Complexity is a pretty big problem for us, and I think severely underestimated by non-contributors.

[Nikita](https://www.reddit.com/r/PHP/comments/j65968/ama_with_the_phpstorm_team_from_jetbrains_on/g7zg9mt/)

---

## Generics aren't coming

---

## Option #3

---

## Type Erasure

---

![](/resources/img/slides/phpstan.gif)

---

## We might not need runtime types 🤯

---

> If you are using a static analyser, then no, I don't think runtime type checking adds a lot of value.

[Nikita](https://www.reddit.com/r/PHP/comments/j65968/ama_with_the_phpstorm_team_from_jetbrains_on/g7zg9mt/)

---

## Problem

---

## Problems

---

## Not everyone's cup of tea ☕️

---

## Other uses 🎭

--- 

## Solutions

---

## Generics are a static feature

---

## Generics via Reflection

---

## Python

```python
person_list: <hljs type>List</hljs>[<hljs type>Person</hljs>] = <hljs prop>list</hljs>()
```

---

## On a personal note

---

## No runtime performance impact

---

## Trivial to implement

---

## All the benefits!

---

## It's different 🥵

---

## Mindshift

---

## Generics aren't coming 😭

---

## But

---

```php
/**
 * <hljs text>@template-extends</hljs> <hljs text>\</hljs><hljs type>ArrayIterator</hljs><hljs text><</hljs><hljs generic>int</hljs><hljs text>,</hljs> <hljs generic>Post</hljs><hljs text>></hljs>
 */
class PostCollection extends ArrayIterator
{
}
```

---

```php
/**
 * @template <hljs generic>Type</hljs>
 * @param <hljs type>class-string</hljs><hljs text><</hljs><hljs generic>Type</hljs><hljs text>></hljs> <hljs text>$className</hljs>
 * @return <hljs generic>Type</hljs>
 */
function resolve(<hljs type>string</hljs> $className): <hljs type>object</hljs>
{
    // …
}
```

---

## Generics 🎉

---

## Downsides

---

## No official specification

---

## No native syntax

---

## Inconsistent


---

```php
/**
 * @template <hljs generic>Type</hljs>
 * @param <hljs type>class-string</hljs><hljs text><</hljs><hljs generic>Type</hljs><hljs text>></hljs> <hljs text>$className</hljs>
 * @return <hljs generic>Type</hljs>
 */
function resolve(<hljs type>string</hljs> $className): <hljs type>object</hljs>
{
    // …
}
```

---

## What's next?

---

## Proper specification

---

## Native syntax

---

## First-party static analyser

---

## No runtime type checks?

---

<h1 class="generics-title">generics</h1>

---

## PHP Foundation

[https://thephp.foundation/](https://thephp.foundation/)

---

## PHStan

[https://phpstan.org/](https://phpstan.org/)

---

## Psalm

[https://psalm.dev/](https://psalm.dev/)

---

<h1 class="generics-title">generics</h1>
