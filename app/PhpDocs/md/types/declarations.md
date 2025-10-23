
 
## Type declarations
 
 Type declarations can be added to function arguments, return values, as of PHP 7.4.0, class properties, and as of PHP 8.3.0, class constants. They ensure that the value is of the specified type at call time, otherwise a `TypeError` is thrown. 
 
 Every single type that PHP supports, with the exception of `resource` can be used within a user-land type declaration. This page contains a changelog of availability of the different types and documentation about usage of them in type declarations. 
 
<div class="note">
     
 When a class implements an interface method or reimplements a method which has already been defined by a parent class, it has to be compatible with the aforementioned definition. A method is compatible if it follows the [variance](language.oop5.variance)] rules. 
 
</div>
 
 
## TODO
 
<!-- start informaltable -->
<!--

   
    
     
      TODO
      TODO
     
    
    
     
      8.3.0
      
       Support for class, interface, trait, and enum constant typing has been added.
      
     
     
      8.2.0
      
       Support for DNF types has been added.
      
     
     
      8.2.0
      
       Support for the literal type true has been added.
      
     
     
      8.2.0
      
       The types null and false can now be used standalone.
      
     
     
      8.1.0
      
       Support for intersection types has been added.
      
     
     
      8.1.0
      
       Returning by reference from a void function is now deprecated.
      
     
     
      8.1.0
      
       Support for the return only type never has been added.
      
     
     
      8.0.0
      
       Support for mixed has been added.
      
     
     
      8.0.0
      
       Support for the return only type static has been added.
      
     
     
      8.0.0
      
       Support for union types has been added.
      
     
     
      7.4.0
      
       Support for class properties typing has been added.
      
     
     
      7.2.0
      
       Support for object has been added.
      
     
     
      7.1.0
      
       Support for iterable has been added.
      
     
     
      7.1.0
      
       Support for void has been added.
      
     
     
      7.1.0
      
       Support for nullable types has been added.
      
     
    
   
  
-->
 
 
 
## Atomic Types Usage Notes
 
 Atomic types have straight forward behaviour with some minor caveats which are described in this section. 
 
<!-- start sect3 -->
<!--

   Scalar types
   
    
     Name aliases for scalar types (bool, int,
     float, string) are not supported.
     Instead, they are treated as class or interface names.
     For example, using boolean as a type declaration
     will require the value to be an instanceof the class or interface
     boolean, rather than of type bool:
    
    
     

<?php
    function test(boolean $param) {}
    test(true);
?>

     
     Output of the above example in PHP 8:
     

Warning: "boolean" will be interpreted as a class name. Did you mean "bool"? Write "\boolean" to suppress this warning in /in/9YrUX on line 2

Fatal error: Uncaught TypeError: test(): Argument #1 ($param) must be of type boolean, bool given, called in - on line 3 and defined in -:2
Stack trace:
#0 -(3): test(true)
#1 {main}
  thrown in - on line 2

     
    
   
  
-->
 
<!-- start sect3 -->
<!--

   void
   
    
     Returning by reference from a void function is deprecated as of PHP 8.1.0,
     because such a function is contradictory.
     Previously, it already emitted the following
     E_NOTICE when called:
     Only variable references should be returned by reference.
     
      

<?php
function &test(): void {}
?>

      
     
    
   
  
-->
 
<!-- start sect3 -->
<!--

   Callable types
   
    This type cannot be used as a class property type declaration.
   

   
    
     It is not possible to specify the signature of the function.
    
   
  
-->
 
<!-- start sect3 -->
<!--

   Type declarations on pass-by-reference Parameters

   
    If a pass-by-reference parameter has a type declaration, the type of the
    variable is only checked on function entry, at the
    beginning of the call, but not when the function returns.
    This means that a function can change the type of variable reference.
   
   
    Typed pass-by-reference Parameters
    

<?php
function array_baz(array &$param)
{
    $param = 1;
}
$var = [];
array_baz($var);
var_dump($var);
array_baz($var);
?>

    
    TODO
    

int(1)

Fatal error: Uncaught TypeError: array_baz(): Argument #1 ($param) must be of type array, int given, called in - on line 9 and defined in -:2
Stack trace:
#0 -(9): array_baz(1)
#1 {main}
  thrown in - on line 2

    
   
  
-->
 
 
 
## Composite Types Usage Notes
 
 Composite type declarations are subject to a couple of restrictions and will perform a redundancy check at compile time to prevent simple bugs. 
 
<!-- start caution -->
<!--

   
    Prior to PHP 8.2.0, and the introduction of DNF types,
    it was not possible to combine intersection types with union types.
   
  
-->
 
<!-- start sect3 -->
<!--

   Union types
   
    
     It is not possible to combine the two singleton types false
     and true together in a union type.
     Use bool instead.
    
   

   
    
     Prior to PHP 8.2.0, as false and null
     could not be used as standalone types, a union type comprised of only
     these types was not permitted. This comprises the following types:
     false, false|null,
     and ?false.
    
   

   
    Nullable type syntactic sugar

    
     A single base type declaration can be marked nullable by prefixing the
     type with a question mark (?).
     Thus ?T and T|null are identical.
    

    
     
      This syntax is supported as of PHP 7.1.0, and predates generalized union
      types support.
     
    

    
     
      It is also possible to achieve nullable arguments by making
      null the default value.
      This is not recommended as if the default value is changed in a child
      class a type compatibility violation will be raised as the
      null type will need to be added to the type declaration.
      This behavior is also deprecated since PHP 8.4.
     
     
      Old way to make arguments nullable
      

<?php
class C {}

function f(C $c = null) {
    var_dump($c);
}

f(new C);
f(null);
?>

      
      The above example will output:
      

object(C)#1 (0) {
}
NULL

      
     
    
   
  
-->
 
<!-- start sect3 -->
<!--

   Duplicate and redundant types
   
    To catch simple bugs in composite type declarations, redundant types that
    can be detected without performing class loading will result in a
    compile-time error. This includes:

    
     
      
       Each name-resolved type may only occur once. Types such as
       int|string|INT or
       Countable{{ amp }}Traversable{{ amp }}COUNTABLE
       result in an error.
      
     
     
      
       Using mixed or never results in an error.
      
     
     
      For union types:
      
       
        
         If bool is used, false or true
         cannot be used additionally.
        
       
       
        
         If object is used, class types cannot be used additionally.
        
       
       
        
         If iterable is used, array
         and Traversable cannot be used additionally.
        
       
      
     
     
      For intersection types:
      
       
        
         Using a type which is not a class-type results in an error.
        
       
       
        
         Using either self, parent, or
         static results in an error.
        
       
      
     
     
      For DNF types:
      
       
        
         If a more generic type is used, the more restrictive one is redundant.
        
       
       
        
         Using two identical intersection types.
        
       
      
     
    
   

   
    
     This does not guarantee that the type is “minimal”, because doing so would
     require loading all used class types.
    
   

   
    For example, if A and B are class
    aliases, then A|B remains a legal union type, even
    though it could be reduced to either A or
    B.
    Similarly, if class B extends A {}, then A|B
    is also a legal union type, even though it could be reduced to just
    A.

    
     

<?php
function foo(): int|INT {} // Disallowed
function foo(): bool|false {} // Disallowed
function foo(): int&Traversable {} // Disallowed
function foo(): self&Traversable {} // Disallowed

use A as B;
function foo(): A|B {} // Disallowed ("use" is part of name resolution)
function foo(): A&B {} // Disallowed ("use" is part of name resolution)

class_alias('X', 'Y');
function foo(): X|Y {} // Allowed (redundancy is only known at runtime)
function foo(): X&Y {} // Allowed (redundancy is only known at runtime)
?>

     
    
   
  
-->
 
 
 
## Examples
 
<div class="example">
     
## Basic class type declaration
 

```php
<?php
class C {}
class D extends C {}

// This doesn't extend C.
class E {}

function f(C $c) {
    echo get_class($c)."\n";
}

f(new C);
f(new D);
f(new E);
?>
```
 
Output of the above example in PHP 8:
 
<!-- start screen -->
<!--


C
D

Fatal error: Uncaught TypeError: f(): Argument #1 ($c) must be of type C, E given, called in /in/gLonb on line 14 and defined in /in/gLonb:8
Stack trace:
#0 -(14): f(Object(E))
#1 {main}
  thrown in - on line 8

   
-->
 
</div>
 
<div class="example">
     
## Basic interface type declaration
 

```php
<?php
interface I { public function f(); }
class C implements I { public function f() {} }

// This doesn't implement I.
class E {}

function f(I $i) {
    echo get_class($i)."\n";
}

f(new C);
f(new E);
?>
```
 
Output of the above example in PHP 8:
 
<!-- start screen -->
<!--


C

Fatal error: Uncaught TypeError: f(): Argument #1 ($i) must be of type I, E given, called in - on line 13 and defined in -:8
Stack trace:
#0 -(13): f(Object(E))
#1 {main}
  thrown in - on line 8

   
-->
 
</div>
 
<div class="example">
     
## Basic return type declaration
 

```php
<?php
function sum($a, $b): float {
    return $a + $b;
}

// Note that a float will be returned.
var_dump(sum(1, 2));
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


float(3)

   
-->
 
</div>
 
<div class="example">
     
## Returning an object
 

```php
<?php
class C {}

function getC(): C {
    return new C;
}

var_dump(getC());
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


object(C)#1 (0) {
}

   
-->
 
</div>
 
<div class="example">
     
## Nullable argument type declaration
 

```php
<?php
class C {}

function f(?C $c) {
    var_dump($c);
}

f(new C);
f(null);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--

    
object(C)#1 (0) {
}
NULL

   
-->
 
</div>
 
<div class="example">
     
## Nullable return type declaration
 

```php
<?php
function get_item(): ?string {
    if (isset($_GET['item'])) {
        return $_GET['item'];
    } else {
        return null;
    }
}
?>
```
 
</div>
 
<div class="example">
     
## Class property type declaration
 

```php
<?php
class User {
    public static string $foo = 'foo';

    public int $id;
    public string $username;

    public function __construct(int $id, string $username) {
        $this->id = $id;
        $this->username = $username;
    }
}
?>
```
 
</div>
 
 

 
 
## Strict typing
 
 By default, PHP will coerce values of the wrong type into the expected scalar type declaration if possible. For example, a function that is given an `int` for a parameter that expects a `string` will get a variable of type `string`. 
 
 It is possible to enable strict mode on a per-file basis. In strict mode, only a value corresponding exactly to the type declaration will be accepted, otherwise a `TypeError` will be thrown. The only exception to this rule is that an `int` value will pass a `float` type declaration. 
 
<div class="warning">
     
 Function calls from within internal functions will not be affected by the strict_types declaration. 
 
</div>
 
 To enable strict mode, the [declare](control-structures.declare)] statement is used with the `strict_types` declaration: 
 
<div class="note">
     
 Strict typing applies to function calls made from <!-- start emphasis -->
<!--
within
--> the file with strict typing enabled, not to the functions declared within that file. If a file without strict typing enabled makes a call to a function that was defined in a file with strict typing, the caller's preference (coercive typing) will be respected, and the value will be coerced. 
 
</div>
 
<div class="note">
     
 Strict typing is only defined for scalar type declarations. 
 
</div>
 
<div class="example">
     
## Strict typing for arguments values
 

```php
<?php
declare(strict_types=1);

function sum(int $a, int $b) {
    return $a + $b;
}

var_dump(sum(1, 2));
var_dump(sum(1.5, 2.5));
?>
```
 
Output of the above example in PHP 8:
 
<!-- start screen -->
<!--


int(3)

Fatal error: Uncaught TypeError: sum(): Argument #1 ($a) must be of type int, float given, called in - on line 9 and defined in -:4
Stack trace:
#0 -(9): sum(1.5, 2.5)
#1 {main}
  thrown in - on line 4

   
-->
 
</div>
 
<div class="example">
     
## Coercive typing for argument values
 

```php
<?php
function sum(int $a, int $b) {
    return $a + $b;
}

var_dump(sum(1, 2));

// These will be coerced to integers: note the output below!
var_dump(sum(1.5, 2.5));
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


int(3)
int(3)

   
-->
 
</div>
 
<div class="example">
     
## Strict typing for return values
 

```php
<?php
declare(strict_types=1);

function sum($a, $b): int {
    return $a + $b;
}

var_dump(sum(1, 2));
var_dump(sum(1, 2.5));
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


int(3)

Fatal error: Uncaught TypeError: sum(): Return value must be of type int, float returned in -:5
Stack trace:
#0 -(9): sum(1, 2.5)
#1 {main}
  thrown in - on line 5

   
-->
 
</div>
 

