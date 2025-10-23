 
## Type Juggling
 
 PHP does not require explicit type definition in variable declaration. In this case, the type of a variable is determined by the value it stores. That is to say, if a string is assigned to variable $var, then $var is of type string. If afterwards an int value is assigned to $var, it will be of type int. 
 
 PHP may attempt to convert the type of a value to another automatically in certain contexts. The different contexts which exist are: <ul> 
<li> 
Numeric
 </li>
 
<li> 
String
 </li>
 
<li> 
Logical
 </li>
 
<li> 
Integral and string
 </li>
 
<li> 
Comparative
 </li>
 
<li> 
Function
 </li>
 </ul> 
 
<div class="note">
     
 When a value needs to be interpreted as a different type, the value itself does not change types. 
 
</div>
 
 To force a variable to be evaluated as a certain type, see the section on Type casting. To change the type of a variable, see the settype function. 
 
 
## Numeric contexts
 
 This is the context when using an arithmetical operator. 
 
 In this context if either operand is a float (or not interpretable as an int), both operands are interpreted as floats, and the result will be a float. Otherwise, the operands will be interpreted as ints, and the result will also be an int. As of PHP 8.0.0, if one of the operands cannot be interpreted a TypeError is thrown. 
 
 
 
## String contexts
 
 This is the context when using echo, print, string interpolation, or the string concatenation operator. 
 
 In this context the value will be interpreted as string. If the value cannot be interpreted a TypeError is thrown. Prior to PHP 7.4.0, an E_RECOVERABLE_ERROR was raised. 
 
 
 
## Logical contexts
 
 This is the context when using conditional statements, the ternary operator, or a logical operator. 
 
 In this context the value will be interpreted as bool. 
 
 
 
## Integral and string contexts
 
 This is the context when using bitwise operators. 
 
 In this context if all operands are of type string the result will also be a string. Otherwise, the operands will be interpreted as ints, and the result will also be an int. As of PHP 8.0.0, if one of the operands cannot be interpreted a TypeError is thrown. 
 
 
 
## Comparative contexts
 
 This is the context when using a comparison operator. 
 
 The type conversions which occur in this context are explained in the Comparison with Various Types table. 
 
 
 
## Function contexts
 
 This is the context when a value is passed to a typed parameter, property, or returned from a function which declares a return type. 
 
 In this context the value must be a value of the type. Two exceptions exist, the first one is: if the value is of type `int` and the declared type is `float`, then the integer is converted to a floating point number. The second one is: if the declared type is a <!-- start emphasis -->
<!--
scalar
-->  type, the value is convertable to a scalar type, and the coercive typing mode is active (the default), the value may be converted to an accepted scalar value. See below for a description of this behaviour. 
 
<div class="warning">
     
 Internal functions automatically coerce null to scalar types, this behaviour is DEPRECATED as of PHP 8.1.0. 
 
</div>
 
<!-- start sect3 -->
<!--

   Coercive typing with simple type declarations
   
    
     
      bool type declaration: value is interpreted as bool.
     
    
    
     
      int type declaration: value is interpreted as int
      if the conversion is well-defined. For example the string is
      numeric.
     
    
    
     
      float type declaration: value is interpreted as float
      if the conversion is well-defined. For example the string is
      numeric.
     
    
    
     
      string type declaration: value is interpreted as string.
     
    
   
  
-->
 
<!-- start sect3 -->
<!--

   Coercive typing with union types
   
    When strict_types is not enabled, scalar type declarations
    are subject to limited implicit type coercions.
    If the exact type of the value is not part of the union, then the target type
    is chosen in the following order of preference:

    
     
      
       int
      
     
     
      
       float
      
     
     
      
       string
      
     
     
      
       bool
      
     
    

    If the type exists in the union and the value can be coerced to the
    type under PHP's existing type-checking semantics, then the type is chosen.
    Otherwise, the next type is tried.
   

   
    
     As an exception, if the value is a string and both int and float are part
     of the union, the preferred type is determined by the existing
     numeric string
     semantics.
     For example, for "42" int is chosen,
     while for "42.0" float is chosen.
    
   

   
    
     Types that are not part of the above preference list are not eligible
     targets for implicit coercion. In particular no implicit coercions to
     the null, false, and true
     types occur.
    
   

   
    Example of types being coerced into a type part of the union
    

<?php
// int|string
42    --> 42          // exact type
"42"  --> "42"        // exact type
new ObjectWithToString --> "Result of __toString()"
                      // object never compatible with int, fall back to string
42.0  --> 42          // float compatible with int
42.1  --> 42          // float compatible with int
1e100 --> "1.0E+100"  // float too large for int type, fall back to string
INF   --> "INF"       // float too large for int type, fall back to string
true  --> 1           // bool compatible with int
[]    --> TypeError   // array not compatible with int or string

// int|float|bool
"45"    --> 45        // int numeric string
"45.0"  --> 45.0      // float numeric string

"45X"   --> true      // not numeric string, fall back to bool
""      --> false     // not numeric string, fall back to bool
"X"     --> true      // not numeric string, fall back to bool
[]      --> TypeError // array not compatible with int, float or bool
?>

    
   
  
-->
 
 
 
## Type Casting
 
 Type casting converts the value to a chosen type by writing the type within parentheses before the value to convert. 
 
<div class="example">
     
## Type Casting
 

```php
<?php
$foo = 10;          // $foo is an integer
$bar = (bool) $foo; // $bar is a boolean

var_dump($bar);
?>
```
 
</div>
 
 The casts allowed are: 
 
<!-- start simplelist -->
<!--

   (int) - cast to int
   (bool) - cast to bool
   (float) - cast to float
   (string) - cast to string
   (array) - cast to array
   (object) - cast to object
   (unset) - cast to NULL
  
-->
 
<div class="note">
     
 `(integer)` is an alias of the `(int)` cast. `(boolean)` is an alias of the `(bool)` cast. `(binary)` is an alias of the `(string)` cast. `(double)` and `(real)` are aliases of the `(float)` cast. These casts do not use the canonical type name and are not recommended. 
 
</div>
 
<div class="warning">
     
 The (real) cast alias has been deprecated as of PHP 7.4.0 and removed as of PHP 8.0.0. 
 
</div>
 
<div class="warning">
     
 The (unset) cast has been deprecated as of PHP 7.2.0. Note that the (unset) cast is the same as assigning the value NULL to the variable or call. The (unset) cast is removed as of PHP 8.0.0. 
 
</div>
 
<!-- start caution -->
<!--

   
    The (binary) cast and b prefix exists
    for forward support. Currently (binary) and
    (string) are identical, however this may change and
    should not be relied upon.
   
  
-->
 
<div class="note">
     
 Whitespaces are ignored within the parentheses of a cast. Therefore, the following two casts are equivalent:  

```php
<?php
$foo = (int) $bar;
$foo = ( int ) $bar;
?>
```
  
 
</div>
 
 
 Casting literal strings and variables to binary strings: 
 

```php
<?php
$binary = (binary) $string;
$binary = b"binary string";
?>
```
 
 

 
 Instead of casting a variable to a string, it is also possible to enclose the variable in double quotes. 
 
<div class="example">
     
## Different Casting Mechanisms
 

```php
<?php
$foo = 10;            // $foo is an integer
$str = "$foo";        // $str is a string
$fst = (string) $foo; // $fst is also a string

// This prints out that "they are the same"
if ($fst === $str) {
    echo "they are the same", PHP_EOL;
}
?>
```
 
</div>
 
 It may not be obvious exactly what will happen when casting between certain types. For more information, see these sections: <!-- start simplelist -->
<!--

    Converting to boolean
    Converting to integer
    Converting to float
    Converting to string
    Converting to array
    Converting to object
    Converting to resource
    Converting to NULL
    The type comparison tables
   
--> 
 
<div class="note">
     
 Because PHP supports indexing into strings via offsets using the same syntax as array indexing, the following example holds true for all PHP versions: 
 
<div class="example">
     
## Using Array Offset with a String
 

```php
<?php
$a    = 'car'; // $a is a string
$a[0] = 'b';   // $a is still a string
echo $a;       // bar
?>
```
 
</div>
 
 See the section titled String access by character for more information. 
 
</div>
 

