
 
## Static Keyword
 
<!-- start tip -->
<!--

   
    This page describes the use of the static keyword to
    define static methods and properties. static can also
    be used to
    define static variables,
    define static anonymous functions
    and for
    late static bindings.
    Please refer to those pages for information on those meanings of
    static.
   
  
-->
 
 Declaring class properties or methods as static makes them accessible without needing an instantiation of the class. These can also be accessed statically within an instantiated class object. 
 
 
## Static methods
 
 Because static methods are callable without an instance of the object created, the pseudo-variable <!-- start varname -->
<!--
$this
--> is not available inside methods declared as static. 
 
<div class="warning">
     
 Calling non-static methods statically throws an `Error`. 
 
 Prior to PHP 8.0.0, calling non-static methods statically was deprecated, and generated an `E_DEPRECATED` warning. 
 
</div>
 
<div class="example">
     
## Static method example
 

```php
<?php
class Foo {
    public static function aStaticMethod() {
        // ...
    }
}

Foo::aStaticMethod();
$classname = 'Foo';
$classname::aStaticMethod();
?>
```
 
</div>
 
 
 
## Static properties
 
 Static properties are accessed using the [Scope Resolution Operator](language.oop5.paamayim-nekudotayim)] (`::`) and cannot be accessed through the object operator (`-{{ gt }}`). 
 
 It's possible to reference the class using a variable. The variable's value cannot be a keyword (e.g. `self`, `parent` and `static`). 
 
<div class="example">
     
## Static property example
 

```php
<?php
class Foo
{
    public static $my_static = 'foo';

    public function staticValue() {
        return self::$my_static;
    }
}

class Bar extends Foo
{
    public function fooStatic() {
        return parent::$my_static;
    }
}


print Foo::$my_static . "\n";

$foo = new Foo();
print $foo->staticValue() . "\n";
print $foo->my_static . "\n";      // Undefined "Property" my_static 

print $foo::$my_static . "\n";
$classname = 'Foo';
print $classname::$my_static . "\n";

print Bar::$my_static . "\n";
$bar = new Bar();
print $bar->fooStatic() . "\n";
?>
```
 
Output of the above example in PHP 8 is similar to:
 
<!-- start screen -->
<!--


foo
foo

Notice: Accessing static property Foo::$my_static as non static in /in/V0Rvv on line 23

Warning: Undefined property: Foo::$my_static in /in/V0Rvv on line 23

foo
foo
foo
foo

    
-->
 
</div>
 
 
