
 
## Scope Resolution Operator (::)
 
 The Scope Resolution Operator (also called Paamayim Nekudotayim) or in simpler terms, the double colon, is a token that allows access to a [constant](language.oop5.constants)], [static](language.oop5.static)] property, or [static](language.oop5.static)] method of a class or one of its parents. Moreover, static properties or methods can be overriden via [late static binding](language.oop5.late-static-bindings)]. 
 
 When referencing these items from outside the class definition, use the name of the class. 
 
 It's possible to reference the class using a variable. The variable's value can not be a keyword (e.g. `self`, `parent` and `static`). 
 
 Paamayim Nekudotayim would, at first, seem like a strange choice for naming a double-colon. However, while writing the Zend Engine 0.5 (which powers PHP 3), that's what the Zend team decided to call it. It actually does mean double-colon - in Hebrew! 
 
<!-- start example -->
<!--

  :: from outside the class definition
  

<?php
class MyClass {
    const CONST_VALUE = 'A constant value';
}

$classname = 'MyClass';
echo $classname::CONST_VALUE;

echo MyClass::CONST_VALUE;
?>

  
 
-->
 
 Three special keywords <!-- start varname -->
<!--
self
-->, <!-- start varname -->
<!--
parent
--> and <!-- start varname -->
<!--
static
--> are used to access properties or methods from inside the class definition. 
 
<!-- start example -->
<!--

  :: from inside the class definition
  

<?php
class MyClass {
    const CONST_VALUE = 'A constant value';
}

class OtherClass extends MyClass
{
    public static $my_static = 'static var';

    public static function doubleColon() {
        echo parent::CONST_VALUE . "\n";
        echo self::$my_static . "\n";
    }
}

$classname = 'OtherClass';
$classname::doubleColon();

OtherClass::doubleColon();
?>

  
 
-->
 
 When an extending class overrides the parent's definition of a method, PHP will not call the parent's method. It's up to the extended class on whether or not the parent's method is called. This also applies to [Constructors and Destructors](language.oop5.decon)], [Overloading](language.oop5.overloading)], and [Magic](language.oop5.magic)] method definitions. 
 
<!-- start example -->
<!--

  Calling a parent's method
  

<?php
class MyClass
{
    protected function myFunc() {
        echo "MyClass::myFunc()\n";
    }
}

class OtherClass extends MyClass
{
    // Override parent's definition
    public function myFunc()
    {
        // But still call the parent function
        parent::myFunc();
        echo "OtherClass::myFunc()\n";
    }
}

$class = new OtherClass();
$class->myFunc();
?>

  
 
-->
 
 See also [some examples of
  static call trickery](language.oop5.basic.class.this)]. 

