 
## Type Operators
 
<!-- start titleabbrev -->
<!--
Type
-->
 
 `instanceof` is used to determine whether a PHP variable is an instantiated object of a certain [class](language.oop5.basic.class)]: <div class="example">
     
## Using instanceof with classes
 

```php
<?php
class MyClass
{
}

class NotMyClass
{
}
$a = new MyClass;

var_dump($a instanceof MyClass);
var_dump($a instanceof NotMyClass);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


bool(true)
bool(false)

   
-->
 
</div> 
 
 `instanceof` can also be used to determine whether a variable is an instantiated object of a class that inherits from a parent class: <div class="example">
     
## Using instanceof with inherited classes
 

```php
<?php
class ParentClass
{
}

class MyClass extends ParentClass
{
}

$a = new MyClass;

var_dump($a instanceof MyClass);
var_dump($a instanceof ParentClass);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


bool(true)
bool(true)

   
-->
 
</div> 
 
 To check if an object is <!-- start emphasis -->
<!--
not
--> an instanceof a class, the [logical not
   operator](language.operators.logical)] can be used. <div class="example">
     
## Using instanceof to check if object is not an
    instanceof a class
 

```php
<?php
class MyClass
{
}

$a = new MyClass;
var_dump(!($a instanceof stdClass));
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


bool(true)

   
-->
 
</div> 
 
 Lastly, `instanceof` can also be used to determine whether a variable is an instantiated object of a class that implements an [interface](language.oop5.interfaces)]: <div class="example">
     
## Using instanceof with interfaces
 

```php
<?php
interface MyInterface
{
}

class MyClass implements MyInterface
{
}

$a = new MyClass;

var_dump($a instanceof MyClass);
var_dump($a instanceof MyInterface);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


bool(true)
bool(true)

   
-->
 
</div> 
 
 Although `instanceof` is usually used with a literal classname, it can also be used with another object or a string variable: <div class="example">
     
## Using instanceof with other variables
 

```php
<?php
interface MyInterface
{
}

class MyClass implements MyInterface
{
}

$a = new MyClass;
$b = new MyClass;
$c = 'MyClass';
$d = 'NotMyClass';

var_dump($a instanceof $b); // $b is an object of class MyClass
var_dump($a instanceof $c); // $c is a string 'MyClass'
var_dump($a instanceof $d); // $d is a string 'NotMyClass'
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


bool(true)
bool(true)
bool(false)

   
-->
 
</div> 
 
 instanceof does not throw any error if the variable being tested is not an object, it simply returns `false`. Constants, however, were not allowed prior to PHP 7.3.0. <div class="example">
     
## Using instanceof to test other variables
 

```php
<?php
$a = 1;
$b = NULL;
$c = fopen('/tmp/', 'r');
var_dump($a instanceof stdClass); // $a is an integer
var_dump($b instanceof stdClass); // $b is NULL
var_dump($c instanceof stdClass); // $c is a resource
var_dump(FALSE instanceof stdClass);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


bool(false)
bool(false)
bool(false)
PHP Fatal error:  instanceof expects an object instance, constant given

   
-->
 
</div> 
 
 As of PHP 7.3.0, constants are allowed on the left-hand-side of the `instanceof` operator. <div class="example">
     
## Using instanceof to test constants
 

```php
<?php
var_dump(FALSE instanceof stdClass);
?>
```
 
Output of the above example in PHP 7.3:
 
<!-- start screen -->
<!--


bool(false)

   
-->
 
</div> 
 
 As of PHP 8.0.0, `instanceof` can now be used with arbitrary expressions. The expression must be wrapped in parentheses and produce a `string`.  <div class="example">
     
## Using instanceof with an arbitrary expression
 

```php
<?php

class ClassA extends \stdClass {}
class ClassB extends \stdClass {}
class ClassC extends ClassB {}
class ClassD extends ClassA {}

function getSomeClass(): string
{
    return ClassA::class;
}

var_dump(new ClassA instanceof ('std' . 'Class'));
var_dump(new ClassB instanceof ('Class' . 'B'));
var_dump(new ClassC instanceof ('Class' . 'A'));
var_dump(new ClassD instanceof (getSomeClass()));
?>
```
 
Output of the above example in PHP 8:
 
<!-- start screen -->
<!--


bool(true)
bool(true)
bool(false)
bool(true)

   
-->
 
</div> 
 
 The instanceof operator has a functional variant with the is_a function. 
 
 
## See Also
 
 <!-- start simplelist -->
<!--

    get_class
    is_a
   
--> 
 
