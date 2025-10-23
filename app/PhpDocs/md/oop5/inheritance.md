
 
## Object Inheritance
 
 Inheritance is a well-established programming principle, and PHP makes use of this principle in its object model. This principle will affect the way many classes and objects relate to one another. 
 
 For example, when extending a class, the subclass inherits all of the public and protected methods, properties and constants from the parent class. Unless a class overrides those methods, they will retain their original functionality. 
 
 This is useful for defining and abstracting functionality, and permits the implementation of additional functionality in similar objects without the need to reimplement all of the shared functionality. 
 
 Private methods of a parent class are not accessible to a child class. As a result, child classes may reimplement a private method themselves without regard for normal inheritance rules. Prior to PHP 8.0.0, however, `final` and `static` restrictions were applied to private methods. As of PHP 8.0.0, the only private method restriction that is enforced is `private final` constructors, as that is a common way to "disable" the constructor when using static factory methods instead. 
 
 The [visibility](language.oop5.visibility)] of methods, properties and constants can be relaxed, e.g. a `protected` method can be marked as `public`, but they cannot be restricted, e.g. marking a `public` property as `private`. An exception are constructors, whose visibility can be restricted, e.g. a `public` constructor can be marked as `private` in a child class. 
 
<div class="note">
     
 Unless autoloading is used, the classes must be defined before they are used. If a class extends another, then the parent class must be declared before the child class structure. This rule applies to classes that inherit other classes and interfaces. 
 
</div>
 
<div class="note">
     
 It is not allowed to override a read-write property with a [readonly property](language.oop5.properties.readonly-properties)] or vice versa.  

```php
<?php

class A {
    public int $prop;
}
class B extends A {
    // Illegal: read-write -> readonly
    public readonly int $prop;
}
?>
```
  
 
</div>
 
<!-- start example -->
<!--

  Inheritance Example
  

<?php

class Foo
{
    public function printItem($string)
    {
        echo 'Foo: ' . $string . PHP_EOL;
    }
    
    public function printPHP()
    {
        echo 'PHP is great.' . PHP_EOL;
    }
}

class Bar extends Foo
{
    public function printItem($string)
    {
        echo 'Bar: ' . $string . PHP_EOL;
    }
}

$foo = new Foo();
$bar = new Bar();
$foo->printItem('baz'); // Output: 'Foo: baz'
$foo->printPHP();       // Output: 'PHP is great' 
$bar->printItem('baz'); // Output: 'Bar: baz'
$bar->printPHP();       // Output: 'PHP is great'

?>

  
 
-->
 
<!-- start sect2 -->
<!--

   Return Type Compatibility with Internal Classes
 
   
    Prior to PHP 8.1, most internal classes or methods didn't declare their return types,
    and any return type was allowed when extending them.
   
 
   
    As of PHP 8.1.0, most internal methods started to "tentatively" declare their return type,
    in that case the return type of methods should be compatible with the parent being extended;
    otherwise, a deprecation notice is emitted.
    Note that lack of an explicit return declaration is also considered a signature mismatch,
    and thus results in the deprecation notice.
   
 
   
    If the return type cannot be declared for an overriding method due to PHP cross-version compatibility concerns,
    a ReturnTypeWillChange attribute can be added to silence the deprecation notice.
   
 
   
    The overriding method does not declare any return type
    

<?php
class MyDateTime extends DateTime
{
    public function modify(string $modifier) { return false; }
}
 
// "Deprecated: Return type of MyDateTime::modify(string $modifier) should either be compatible with DateTime::modify(string $modifier): DateTime|false, or the #[\ReturnTypeWillChange] attribute should be used to temporarily suppress the notice" as of PHP 8.1.0
?> 

    
   
 
   
    The overriding method declares a wrong return type
    

<?php
class MyDateTime extends DateTime
{
    public function modify(string $modifier): ?DateTime { return null; }
}
 
// "Deprecated: Return type of MyDateTime::modify(string $modifier): ?DateTime should either be compatible with DateTime::modify(string $modifier): DateTime|false, or the #[\ReturnTypeWillChange] attribute should be used to temporarily suppress the notice" as of PHP 8.1.0
?> 

    
   
 
   
    The overriding method declares a wrong return type without a deprecation notice
    

<?php
class MyDateTime extends DateTime
{
    /**
     * @return DateTime|false
     */
    #[\ReturnTypeWillChange]
    public function modify(string $modifier) { return false; }
}
 
// No notice is triggered 
?> 

    
   
 
  
-->
 
