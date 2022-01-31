


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


