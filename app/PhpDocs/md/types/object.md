
 
## Objects
 
 
## Object Initialization
 
 To create a new `object`, use the `new` statement to instantiate a class: 
 
<div class="example">
     
## Object Construction
 

```php
<?php
class foo
{
    function do_foo()
    {
        echo "Doing foo.";
    }
}

$bar = new foo;
$bar->do_foo();
?>
```
 
</div>
 
 For a full discussion, see the Classes and Objects chapter. 
 
 
 
## Converting to object
 
 If an `object` is converted to an `object`, it is not modified. If a value of any other type is converted to an `object`, a new instance of the `stdClass` built-in class is created. If the value was `null`, the new instance will be empty. An `array` converts to an `object` with properties named by keys and corresponding values. Note that in this case before PHP 7.2.0 numeric keys have been inaccessible unless iterated. 
 
<div class="example">
     
## Casting to an Object
 

```php
<?php
$obj = (object) array('1' => 'foo');
var_dump(isset($obj->{'1'})); // outputs 'bool(true)'

// Deprecated as of PHP 8.1
var_dump(key($obj)); // outputs 'string(1) "1"'
?>
```
 
</div>
 
 For any other value, a member variable named `scalar` will contain the value. 
 
<div class="example">
     
## (object) cast
 

```php
<?php
$obj = (object) 'ciao';
echo $obj->scalar;  // outputs 'ciao'
?>
```
 
</div>
 

