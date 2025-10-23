
 
## Class Constants
 
 It is possible to define [constants](language.constants)] on a per-class basis remaining the same and unchangeable. The default visibility of class constants is `public`. 
 
<div class="note">
     
 Class constants can be redefined by a child class. As of PHP 8.1.0, class constants cannot be redefined by a child class if it is defined as [final](language.oop5.final)]. 
 
</div>
 
 It's also possible for interfaces to have constants. Look at the [interface documentation](language.oop5.interfaces)] for examples. 
 
 It's possible to reference the class using a variable. The variable's value can not be a keyword (e.g. `self`, `parent` and `static`). 
 
 Note that class constants are allocated once per class, and not for each class instance. 
 
 As of PHP 8.3.0, class constants can have a scalar type such as `bool`, `int`, `float`, `string`, or even `array`. When using `array`, the contents can only be other scalar types. 
 
<div class="example">
     
## Defining and using a constant
 

```php
<?php
class MyClass
{
    const CONSTANT = 'constant value';

    function showConstant() {
        echo  self::CONSTANT . "\n";
    }
}

echo MyClass::CONSTANT . "\n";

$classname = "MyClass";
echo $classname::CONSTANT . "\n";

$class = new MyClass();
$class->showConstant();

echo $class::CONSTANT."\n";
?>
```
 
</div>
 
 The special `::class` constant allows for fully qualified class name resolution at compile time, this is useful for namespaced classes: 
 
<div class="example">
     
## Namespaced ::class example
 

```php
<?php
namespace foo {
    class bar {
    }

    echo bar::class; // foo\bar
}
?>
```
 
</div>
 
<div class="example">
     
## Class constant expression example
 

```php
<?php
const ONE = 1;
class foo {
    const TWO = ONE * 2;
    const THREE = ONE + self::TWO;
    const SENTENCE = 'The value of THREE is '.self::THREE;
}
?>
```
 
</div>
 
<div class="example">
     
## Class constant visibility modifiers, as of PHP 7.1.0
 

```php
<?php
class Foo {
    public const BAR = 'bar';
    private const BAZ = 'baz';
}
echo Foo::BAR, PHP_EOL;
echo Foo::BAZ, PHP_EOL;
?>
```
 
Output of the above example in PHP 7.1:
 
<!-- start screen -->
<!--


bar

Fatal error: Uncaught Error: Cannot access private const Foo::BAZ in …

   
-->
 
</div>
 
<div class="note">
     
 As of PHP 7.1.0 visibility modifiers are allowed for class constants. 
 
</div>
 
<div class="example">
     
## Class constant visibility variance check, as of PHP 8.3.0
 

```php
<?php

interface MyInterface
{
    public const VALUE = 42;
}

class MyClass implements MyInterface
{
    protected const VALUE = 42;
}
?>
```
 
Output of the above example in PHP 8.3:
 
<!-- start screen -->
<!--


Fatal error: Access level to MyClass::VALUE must be public (as in interface MyInterface) …

  
-->
 
</div>
 
<div class="note">
     
 As of PHP 8.3.0 visibility variance is checked more strictly. Prior to this version, the visibility of a class constant could be different from the visibility of the constant in the implemented interface. 
 
</div>
 
<div class="example">
     
## Fetch class constant syntax, as of PHP 8.3.0
 

```php
<?php
class Foo {
    public const BAR = 'bar';
    private const BAZ = 'baz';
}

$name = 'BAR';
echo Foo::{$name}, PHP_EOL; // bar
?>
```
 
</div>
 
<div class="note">
     
 As of PHP 8.3.0, class constants can be fetched dynamically using a variable. 
 
</div>
 
<div class="example">
     
## Assigning types to class constants, as of PHP 8.3.0
 

```php
<?php

class MyClass {
    public const bool MY_BOOL = true;
    public const int MY_INT = 1;
    public const float MY_FLOAT = 1.01;
    public const string MY_STRING = 'one';
    public const array MY_ARRAY = [self::MY_BOOL, self::MY_INT, self::MY_FLOAT, self::MY_STRING];
}

var_dump(MyClass::MY_BOOL);
var_dump(MyClass::MY_INT);
var_dump(MyClass::MY_FLOAT);
var_dump(MyClass::MY_STRING);
var_dump(MyClass::MY_ARRAY);
?>
```
 
Output of the above example in PHP 8.3:
 
<!-- start screen -->
<!--


bool(true)
int(1)
float(1.01)
string(3) "one"
array(4) {
  [0]=>
  bool(true)
  [1]=>
  int(1)
  [2]=>
  float(1.01)
  [3]=>
  string(3) "one"
}
   
  
-->
 
</div>

