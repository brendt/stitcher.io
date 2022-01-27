


<div class="screenshot">

```php
function add($a, $b)
{
    return $a + $b;
}
```
</div>


<div class="screenshot">

```php
function add($a, $b)
{
    return $a + $b;
}

<hljs prop>add</hljs>('1', '2'); // 3
```
</div>


<div class="screenshot">

```php
function add($a, $b)
{
    return $a + $b;
}

<hljs prop>add</hljs>(true, []); // ?
```
</div>


<div class="screenshot">

```php
function add($a, $b)
{
    if (!<hljs prop>is_int</hljs>($a) || !<hljs prop>is_int</hljs>($b)) {
        return null;
    }
    
    return $a + $b;
}
```
</div>


<div class="screenshot">

```php
function add(<hljs type>int</hljs> $a, <hljs type>int</hljs> $b): <hljs type>int</hljs>
{
    return $a + $b;
}
```
</div>


<div class="screenshot">

```php
function login(<hljs type>User</hljs> $user): void
{
    $user->
}
```
</div>


<div class="screenshot">

```php
<hljs prop>add</hljs>(<hljs striped>'invalid'</hljs>, <hljs striped>'string'</hljs>);
```
</div>


<div class="screenshot">

```php
class Collection extends ArrayObject
{
    public function offsetGet(<hljs type>mixed</hljs> $key): <hljs type>mixed</hljs>
    { /* … */ }
    
    public function filter(<hljs type>Closure</hljs> $fn): self 
    { /* … */ }
    
    public function map(<hljs type>Closure</hljs> $fn): self 
    { /* … */ }
}
```
</div>

<div class="screenshot">

```php
class StringCollection extends Collection
{
    public function offsetGet(<hljs type>mixed</hljs> $key): <hljs type>string</hljs>
    { /* … */ }
    
    // …
}

class UserCollection extends Collection
{
    public function offsetGet(<hljs type>mixed</hljs> $key): <hljs type>User</hljs>
    { /* … */ }
    
    // …
}
```
</div>

<div class="screenshot">

```php
class BlogPostCollection extends Collection
{
    public function offsetGet(<hljs type>mixed</hljs> $key): <hljs type>BlogPost</hljs>
    { /* … */ }
}
```
</div>

<div class="screenshot">

```php
class ArticleCollection extends Collection
{
    public function offsetGet(<hljs type>mixed</hljs> $key): <hljs type>Article</hljs>
    { /* … */ }
}
```
</div>

<div class="screenshot">

```php
class VideoCollection extends Collection
{
    public function offsetGet(<hljs type>mixed</hljs> $key): <hljs type>Video</hljs>
    { /* … */ }
}
```
</div>

<div class="screenshot">

```php
class IntegerCollection extends Collection
{
    public function offsetGet(<hljs type>mixed</hljs> $key): <hljs type>int</hljs>
    { /* … */ }
}
```
</div>

<div class="screenshot">

```php
class TagCollection extends Collection
{
    public function offsetGet(<hljs type>mixed</hljs> $key): <hljs type>Tag</hljs>
    { /* … */ }
}
```
</div>


<div class="screenshot">

```php
class Collection<<hljs generic>Type</hljs>> extends ArrayObject
{
    public function offsetGet(<hljs type>mixed</hljs> $key): <hljs generic>Type</hljs>
    { /* … */ }
    
    // …
}
```
</div>

<div class="screenshot">

```php
$users = new <hljs type>Collection</hljs><<hljs generic>User</hljs>>();

$slugs = new <hljs type>Collection</hljs><<hljs generic>string</hljs>>();
```
</div>

<div class="screenshot">

```php
$users = new <hljs type>Collection</hljs>();

// …

foreach ($users as $user) {
    $user->
}
```
</div>

<div class="screenshot">

```php
$users = new <hljs type>Collection</hljs><<hljs generic>User</hljs>>();

// …

foreach ($users as $user) {
    $user->
}
```
</div>

<div class="screenshot">

```php
$users[] = <hljs striped>'wrong'</hljs>;
```
</div>

<div class="screenshot">

```php
function register(<hljs type>Collection</hljs><<hljs generic>User</hljs>> $users): void
{}
```
</div>

