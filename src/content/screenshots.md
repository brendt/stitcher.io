


<div class="screenshot">

```php
class Tile extends Clickable, HasMenu, CanUpgrade
{ /* … */ }
```
</div>

<div class="screenshot screenshot-hidden">

```php
function <hljs prop>createPost</hljs>(
    <hljs type>PostData</hljs> <hljs text>$data,</hljs>    
    <hljs type>PostData</hljs> <hljs text>$data,</hljs>    
    <hljs type>PostData</hljs> <hljs text>$data,</hljs>    
<hljs text>) {</hljs>
    <hljs text>$post =</hljs> <hljs type>Post</hljs><hljs text>::</hljs><hljs prop>create</hljs><hljs text>($data);</hljs>

    return <hljs type>$post;</hljs>
<hljs text>}</hljs>
```

</div>


<div class="screenshot">

```php
$input = "key=value";

[$key, $val ?? <hljs keyword>null</hljs>] = <hljs prop>explode</hljs>('=', $input, 2);
```

</div>

<div class="screenshot">

```php
<hljs prop>json_validate</hljs>(
    <hljs type>string</hljs> $json, 
    <hljs type>int</hljs> $depth = 512, 
    <hljs type>int</hljs> $flags = 0
): <hljs type>bool</hljs>
```

</div>
<div class="screenshot">

```php
Callables that are not accepted by the $callable() syntax (but are accepted
    by call_user_func) are deprecated.
```

</div>
<div class="screenshot">

```php
$ffi = <hljs type>FFI</hljs>::<hljs prop>cdef</hljs>(
    "int printf(const char *format, ...);",
    "libc.so.6");
    
$ffi-><hljs prop>printf</hljs>("Hello %s!\n", "world");
```

</div>

<div class="screenshot">

```php
final class MyIterator implements \IteratorAggregate
{
    #[<hljs type>\ReturnTypeWillChange</hljs>]
    public function getIterator()
    {
        // …
    }
}
```

</div>

<div class="screenshot">

```php
final class MyIterator implements \IteratorAggregate
{
    public function getIterator(): \Traversable
    {

    }
}
```

</div>

<div class="screenshot">

```php
#[<hljs type>\AllowDynamicProperties</hljs>]
class Post { /* … */ }

// …

$post-><hljs prop>title</hljs> = 'Name';
```
</div>

<div class="screenshot">

```php
<hljs prop>error_reporting</hljs>(<hljs prop>E_ALL</hljs> ^ <hljs prop>E_DEPRECATED</hljs>);
```
</div>



<div class="screenshot">

```txt
$x = 1;

<hljs comment>// No explicit use needed</hljs>
<hljs keyword>fn</hljs> ($a) => $a ** $x; 

<hljs comment>// Explicit use needed to access outer scope</hljs>
<hljs keyword>function</hljs> (<hljs type>int</hljs> $a) <hljs keyword>use</hljs> ($x) => $a ** $x;
```

</div>


<div class="screenshot">

```php
<hljs keyword>fn</hljs> () => 'hello'; // Single line, no return needed

<hljs keyword>fn</hljs> (<hljs type>int</hljs> $a, <hljs type>int</hljs> $b) {
    $x = $a + $b
    
    return $x ** 2;
}; // Multi line, explicit return needed
```

</div>


<div class="screenshot">

```php
$result = <hljs prop>array_map</hljs>(
    <hljs keyword>fn</hljs> (<hljs keyword>string</hljs> $part) => <hljs prop>strtoupper</hljs>($part), 
    <hljs prop>str_split</hljs>(
        <hljs prop>htmlentities</hljs>("Hello World")
    ),
)
```
</div>

<div class="screenshot">

```php
$result = "Hello World"
    |> <hljs prop>htmlentities</hljs>(...)
    |> <hljs prop>str_split</hljs>(...)
    |> <hljs prop>array_map</hljs>(
          <hljs keyword>fn</hljs> (<hljs keyword>string</hljs> $part) => <hljs prop>strtoupper</hljs>($part), 
          ...
       );
```

</div>

<div class="screenshot">

```php
class PostData
{
    public function __construct(
        <hljs keyword>public readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>public readonly</hljs> <hljs type>Author</hljs> <hljs prop>$author</hljs>,
        <hljs keyword>public readonly</hljs> <hljs type>string</hljs> <hljs prop>$body</hljs>,
        <hljs keyword>public readonly</hljs> <hljs type>DateTimeImmutable</hljs> <hljs prop>$createdAt</hljs>,
        <hljs keyword>public readonly</hljs> <hljs type>PostState</hljs> <hljs prop>$state</hljs>,
    ) {}
}
```

</div>

<div class="screenshot">

```php
<hljs keyword>readonly</hljs> class PostData
{
    public function __construct(
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>public</hljs> <hljs type>Author</hljs> <hljs prop>$author</hljs>,
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$body</hljs>,
        <hljs keyword>public</hljs> <hljs type>DateTimeImmutable</hljs> <hljs prop>$createdAt</hljs>,
        <hljs keyword>public</hljs> <hljs type>PostState</hljs> <hljs prop>$state</hljs>,
    ) {}
}
```

</div>

<div class="screenshot">

```php
// This is the ideal situation

function app(<hljs type>class-string</hljs><<hljs generic>Type</hljs>> $className): <hljs generic>Type</hljs>
{ /* … */ }
```
</div>

<div class="screenshot">

```php
// You'd need to write strings instead

#[<hljs type>Template</hljs>('<hljs text>Type</hljs>')]
#[<hljs type>Return</hljs>('<hljs text>Type</hljs>')]
function app(#[<hljs type>ClassString</hljs>('<hljs text>Type</hljs>')] $className)
{ /* … */ }
```
</div>

<div class="screenshot">

```php
// The attribute alternative
// although this wouldn't work

#[<hljs type>Template</hljs>(<hljs generic>Type</hljs>)]
#[<hljs type>Return</hljs>(<hljs generic>Type</hljs>)]
function app(#[<hljs type>ClassString</hljs>(<hljs generic>Type</hljs>)] $className)
{ /* … */ }
```
</div>

<div class="screenshot">

```php
// Generics right now 

/**
 * @template <hljs generic>Type</hljs>
 * @param <hljs type>class-string</hljs><<hljs generic>Type</hljs>> $className
 * @return <hljs generic>Type</hljs>
 */
function app($className)
{ /* … */ }
```
</div>



<div class="screenshot">

```txt
<hljs comment>PHP</hljs>
<?php <hljs keyword>echo</hljs> <hljs prop>strtoupper</hljs>('welcome'); ?>

<hljs comment>Blade</hljs>
{{ <hljs prop>strtoupper</hljs>('welcome') }}

<hljs comment>Twig</hljs>
{{ 'welcome'|<hljs prop>upper</hljs> }}
```
</div>

<div class="screenshot">

```txt
<hljs comment>PHP</hljs>
<?php <hljs keyword>echo</hljs> 'http://' . $app-><hljs prop>request</hljs>-><hljs prop>host</hljs>; ?> 

<hljs comment>Blade</hljs>
{{ 'http://' . $app-><hljs prop>request</hljs>-><hljs prop>host</hljs> }} 

<hljs comment>Twig</hljs>
{{ 'http://' ~ app.<hljs prop>request</hljs>.<hljs prop>host</hljs> }}
```
</div>

<div class="screenshot">

```txt
<hljs comment>PHP</hljs>
<?php <hljs keyword>echo</hljs> 'http://' . $app-><hljs prop>request</hljs>-><hljs prop>host</hljs>; ?> 

<hljs comment>Blade</hljs>
{{ 'http://' . $app-><hljs prop>request</hljs>-><hljs prop>host</hljs> }} 

<hljs comment>Twig</hljs>
{{ 'http://' ~ app.<hljs prop>request</hljs>.<hljs prop>host</hljs> }}
```
</div>

<div class="screenshot">

```txt
<hljs comment>PHP</hljs>
<hljs keyword>foreach</hljs> ($users <hljs keyword>as</hljs> $user) {

<hljs comment>Blade</hljs>
@<hljs keyword>foreach</hljs> ($users <hljs keyword>as</hljs> $user)

<hljs comment>Twig</hljs>
{% <hljs keyword>for</hljs> user <hljs keyword>in</hljs> users %}
```
</div>

<div class="screenshot">

```txt
<hljs comment>PHP</hljs>
<hljs keyword>if</hljs> (! $user-><hljs prop>subscribed</hljs>) {

<hljs comment>Blade</hljs>
@<hljs keyword>if</hljs>(! $user-><hljs prop>subscribed</hljs>)

<hljs comment>Twig</hljs>
{% <hljs keyword>if not</hljs> user.<hljs prop>subscribed</hljs> %}
```
</div>

<div class="screenshot">

```txt
<hljs comment>PHP</hljs>
<?php echo $title ?>

<hljs comment>Blade</hljs>
{{ $title }}

<hljs comment>Twig</hljs>
{{ title }}
```
</div>





<div class="screenshot">

```txt
<hljs keyword>function</hljs> <hljs prop>greet</hljs>(person) {
  <hljs keyword>return</hljs> "Hello " + person.<hljs prop>name</hljs>;
}
```
</div>

<div class="screenshot">

```txt
<hljs keyword>interface</hljs> <hljs type>Person</hljs> {
  <hljs prop>name</hljs>: <hljs type>string</hljs>;
  <hljs prop>age</hljs>: <hljs type>number</hljs>;
}
 
<hljs keyword>function</hljs> <hljs prop>greet</hljs>(person: <hljs type>Person</hljs>) {
  <hljs keyword>return</hljs> "Hello " + person.<hljs prop>name</hljs>;
}
```
</div>

<div class="screenshot">

```txt
<hljs keyword>type</hljs> <hljs type>Result</hljs> = "pass" | "fail"
 
<hljs keyword>function</hljs> <hljs prop>verify</hljs>(result: <hljs type>Result</hljs>) {
    <hljs comment>// …</hljs>
}

<hljs prop>verify</hljs>(<hljs striped>'wrong'</hljs>)
```
</div>

<div class="screenshot">

```txt
<hljs keyword>interface</hljs> <hljs type>Account</hljs> {
  <hljs prop>id</hljs>: <hljs type>number</hljs>
  <hljs prop>displayName</hljs>: <hljs type>string</hljs>
  <hljs prop>version</hljs>: 1
}
 
<hljs keyword>function</hljs> <hljs prop>welcome</hljs>(user: <hljs type>Account</hljs>) {
    <hljs comment>// …</hljs>
}
```
</div>

<div class="screenshot">

```js
[] + {}
// '[object Object]'

{} + []
// 0
```
</div>

<div class="screenshot">

```txt
<hljs keyword>class</hljs> <hljs type>Foo</hljs> {
    <hljs prop>#id</hljs>;
    
    <hljs prop>constructor</hljs>(id) {
        <hljs keyword>this.</hljs><hljs prop>#id</hljs> = id;
    }
    
    <hljs keyword>get</hljs> <hljs prop>id</hljs>() {
        <hljs keyword>return</hljs> <hljs keyword>this</hljs>.<hljs prop>#id</hljs>;
    }
}
```
</div>


<div class="screenshot">

```txt
<hljs keyword>let</hljs> <hljs type>Foo</hljs> = {
    <hljs prop>init</hljs>(id) {
        <hljs keyword>this</hljs>.<hljs prop>setId</hljs>(id);
    },
  
    <hljs prop>setId</hljs>(id) { <hljs keyword>this</hljs>.id = id; },
    <hljs prop>getId</hljs>() { <hljs keyword>return</hljs> <hljs keyword>this</hljs>.id; },
};
```
</div>

<div class="screenshot">

```php
$users = new <hljs type>Collection</hljs><<hljs generic>User</hljs>>();
```
</div>


<div class="screenshot">

```php
function app(<hljs type>string</hljs> $className): mixed
{
    return <hljs type>Container</hljs>::<hljs prop>get</hljs>($className);
}
```
</div>


<div class="screenshot">

```php
function app(<hljs type>string</hljs> $className): mixed
{ /* … */ }

<hljs prop>app</hljs>(<hljs type>UserRepository</hljs>::class); // ?
```
</div>


<div class="screenshot">

```php
/**
 * @template <hljs generic>Type</hljs>
 * @param <hljs type>class-string</hljs><<hljs generic>Type</hljs>> $className
 * @return <hljs generic>Type</hljs>
 */
function app(<hljs type>string</hljs> $className): mixed
{ /* … */ }
```
</div>

<div class="screenshot">

```php
<hljs prop>app</hljs>(<hljs type>UserRepository</hljs>::class)->
```
</div>

<div class="screenshot">

```php
<hljs type>Attributes</hljs>::<hljs prop>in</hljs>(<hljs type>MyController</hljs>::class)
    -><hljs prop>filter</hljs>(<hljs type>RouteAttribute</hljs>::class)
    -><hljs prop>newInstance</hljs>()
    ->
```
</div>

<div class="screenshot">

```php
/** @template <hljs generic>AttributeType</hljs> */
class Attributes
{
    /**
     * @template <hljs generic>InputType</hljs>
     * @param <hljs type>class-string</hljs><<hljs generic>InputType</hljs>> $className
     * @return <hljs type>self</hljs><<hljs generic>InputType</hljs>>
     */
    public function filter(<hljs type>string</hljs> $className): self
    { /* … */ }
 
    /**
     * @return <hljs generic>AttributeType</hljs> 
     */   
    public function instanceOf(): mixed
    { /* … */ }
    
    // …
}
```
</div>


