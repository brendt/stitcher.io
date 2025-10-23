 
## Comparison Operators
 
<!-- start titleabbrev -->
<!--
Comparison
-->
 
 Comparison operators, as their name implies, allow you to compare two values. You may also be interested in viewing the type comparison tables, as they show examples of various type related comparisons. 
 
<!-- start table -->
<!--

  Comparison Operators
  
   
    
     Example
     Name
     Result
    
   
   
    
     $a == $b
     Equal
     true if $a is equal to $b after type juggling.
    
    
     $a === $b
     Identical
     
      true if $a is equal to $b, and they are of the same
      type.
     
    
    
     $a != $b
     Not equal
     true if $a is not equal to $b after type juggling.
    
    
     $a {{ lt }}{{ gt }} $b
     Not equal
     true if $a is not equal to $b after type juggling.
    
    
     $a !== $b
     Not identical
     
      true if $a is not equal to $b, or they are not of the same
      type.
     
    
    
     $a {{ lt }} $b
     Less than
     true if $a is strictly less than $b.
    
    
     $a {{ gt }} $b
     Greater than
     true if $a is strictly greater than $b.
    
    
     $a {{ lt }}= $b
     Less than or equal to
     true if $a is less than or equal to $b.
    
    
     $a {{ gt }}= $b
     Greater than or equal to
     true if $a is greater than or equal to $b.
    
    
     $a {{ lt }}={{ gt }} $b
     Spaceship
     
      An int less than, equal to, or greater than zero when
      $a is less than, equal to, or greater than
      $b, respectively.
     
    
   
  
 
-->
 
 If both operands are [numeric strings](language.types.numeric-strings)], or one operand is a number and the other one is a [numeric string](language.types.numeric-strings)], then the comparison is done numerically. These rules also apply to the [switch](control-structures.switch)] statement. The type conversion does not take place when the comparison is `===` or `!==` as this involves comparing the type as well as the value. 
 
<div class="warning">
     
 Prior to PHP 8.0.0, if a `string` is compared to a number or a numeric string then the `string` was converted to a number before performing the comparison. This can lead to surprising results as can be seen with the following example:  

```php
<?php
var_dump(0 == "a");
var_dump("1" == "01");
var_dump("10" == "1e1");
var_dump(100 == "1e2");

switch ("a") {
case 0:
    echo "0";
    break;
case "a":
    echo "a";
    break;
}
?>
```
 
Output of the above example in PHP 7:
 
<!-- start screen -->
<!--


bool(true)
bool(true)
bool(true)
bool(true)
0

    
-->
 
Output of the above example in PHP 8:
 
<!-- start screen -->
<!--


bool(false)
bool(true)
bool(true)
bool(true)
a

    
-->
  
 
</div>
 
 <div class="example">
     
## Comparison Operators
 

```php
<?php
// Integers
echo 1 <=> 1, ' '; // 0
echo 1 <=> 2, ' '; // -1
echo 2 <=> 1, ' '; // 1

// Floats
echo 1.5 <=> 1.5, ' '; // 0
echo 1.5 <=> 2.5, ' '; // -1
echo 2.5 <=> 1.5, ' '; // 1

// Strings
echo "a" <=> "a", ' '; // 0
echo "a" <=> "b", ' '; // -1
echo "b" <=> "a", ' '; // 1

echo "a" <=> "aa", ' ';  // -1
echo "zz" <=> "aa", ' '; // 1

// Arrays
echo [] <=> [], ' ';               // 0
echo [1, 2, 3] <=> [1, 2, 3], ' '; // 0
echo [1, 2, 3] <=> [], ' ';        // 1
echo [1, 2, 3] <=> [1, 2, 1], ' '; // 1
echo [1, 2, 3] <=> [1, 2, 4], ' '; // -1

// Objects
$a = (object) ["a" => "b"];
$b = (object) ["a" => "b"];
echo $a <=> $b, ' '; // 0

$a = (object) ["a" => "b"];
$b = (object) ["a" => "c"];
echo $a <=> $b, ' '; // -1

$a = (object) ["a" => "c"];
$b = (object) ["a" => "b"];
echo $a <=> $b, ' '; // 1

// not only values are compared; keys must match
$a = (object) ["a" => "b"];
$b = (object) ["b" => "b"];
echo $a <=> $b, ' '; // 1

?>
```
 
</div> 
 
 For various types, comparison is done according to the following table (in order). 
 
<!-- start table -->
<!--

  Comparison with Various Types
  
   
    
     Type of Operand 1
     Type of Operand 2
     Result
    
   
   
    
     null or string
     string
     Convert null to "", numerical or lexical comparison
    
    
     bool or null
     anything
     Convert both sides to bool, false {{ lt }} true
    
    
     object
     object
     Built-in classes can define its own comparison, different classes
      are incomparable, same class see Object Comparison
     
    
    
     string, resource, int or float
     string, resource, int or float
     Translate strings and resources to numbers, usual math
    
    
     array
     array
     Array with fewer members is smaller, if key from operand 1 is not
      found in operand 2 then arrays are incomparable, otherwise - compare
      value by value (see following example)
    
    
     object
     anything
     object is always greater
    
    
     array
     anything
     array is always greater
    
   
  
 
-->
 
 <div class="example">
     
## Boolean/null comparison
 

```php
<?php
// Bool and null are compared as bool always
var_dump(1 == TRUE);  // TRUE - same as (bool) 1 == TRUE
var_dump(0 == FALSE); // TRUE - same as (bool) 0 == FALSE
var_dump(100 < TRUE); // FALSE - same as (bool) 100 < TRUE
var_dump(-10 < FALSE);// FALSE - same as (bool) -10 < FALSE
var_dump(min(-100, -10, NULL, 10, 100)); // NULL - (bool) NULL < (bool) -100 is FALSE < TRUE
?>
```
 
</div> 
 
 <div class="example">
     
## Transcription of standard array comparison
 

```php
<?php
// Arrays are compared like this with standard comparison operators as well as the spaceship operator.
function standard_array_compare($op1, $op2)
{
    if (count($op1) < count($op2)) {
        return -1; // $op1 < $op2
    } elseif (count($op1) > count($op2)) {
        return 1; // $op1 > $op2
    }
    foreach ($op1 as $key => $val) {
        if (!array_key_exists($key, $op2)) {
            return 1;
        } elseif ($val < $op2[$key]) {
            return -1;
        } elseif ($val > $op2[$key]) {
            return 1;
        }
    }
    return 0; // $op1 == $op2
}
?>
```
 
</div> 
 
<div class="warning">
     
## Comparison of floating point numbers
 
 Because of the way `float`s are represented internally, you should not test two `float`s for equality. 
 
 See the documentation for `float` for more information. 
 
</div>
 
<div class="note">
     
 Be aware that PHP's type juggling is not always obvious when comparing values of different types, particularly comparing ints to bools or ints to strings. It is therefore generally advisable to use === and !== comparisons rather than == and != in most cases. 
 
</div>
 
 
## Incomparable Values
 
 While identity comparison (=== and !==) can be applied to arbitrary values, the other comparison operators should only be applied to comparable values. The result of comparing incomparable values is undefined, and should not be relied upon. 
 
 
 
## See Also
 
 <!-- start simplelist -->
<!--

    strcasecmp
    strcmp
    Array operators
    Types
   
--> 
 
 
 
## Ternary Operator
 
 Another conditional operator is the "?:" (or ternary) operator. <div class="example">
     
## Assigning a default value
 

```php
<?php
// Example usage for: Ternary Operator
$action = (empty($_POST['action'])) ? 'default' : $_POST['action'];

// The above is identical to this if/else statement
if (empty($_POST['action'])) {
    $action = 'default';
} else {
    $action = $_POST['action'];
}
?>
```
 
</div> The expression `(expr1) ? (expr2) : (expr3)` evaluates to <!-- start replaceable -->
<!--
expr2
--> if <!-- start replaceable -->
<!--
expr1
--> evaluates to `true`, and <!-- start replaceable -->
<!--
expr3
--> if <!-- start replaceable -->
<!--
expr1
--> evaluates to `false`. 
 
 It is possible to leave out the middle part of the ternary operator. Expression `expr1 ?: expr3` evaluates to the result of <!-- start replaceable -->
<!--
expr1
--> if <!-- start replaceable -->
<!--
expr1
--> evaluates to `true`, and <!-- start replaceable -->
<!--
expr3
--> otherwise. <!-- start replaceable -->
<!--
expr1
--> is only evaluated once in this case. 
 
<div class="note">
     
 Please note that the ternary operator is an expression, and that it doesn't evaluate to a variable, but to the result of an expression. This is important to know if you want to return a variable by reference. The statement return $var == 42 ? $a : $b; in a return-by-reference function will therefore not work and a warning is issued. 
 
</div>
 
<div class="note">
     
 It is recommended to avoid "stacking" ternary expressions. PHP's behaviour when using more than one unparenthesized ternary operator within a single expression is non-obvious compared to other languages. Indeed prior to PHP 8.0.0, ternary expressions were evaluated left-associative, instead of right-associative like most other programming languages. Relying on left-associativity is deprecated as of PHP 7.4.0. As of PHP 8.0.0, the ternary operator is non-associative. <div class="example">
     
## Non-obvious Ternary Behaviour
 

```php
<?php
// on first glance, the following appears to output 'true'
echo (true ? 'true' : false ? 't' : 'f');

// however, the actual output of the above is 't' prior to PHP 8.0.0
// this is because ternary expressions are left-associative

// the following is a more obvious version of the same code as above
echo ((true ? 'true' : false) ? 't' : 'f');

// here, one can see that the first expression is evaluated to 'true', which
// in turn evaluates to (bool) true, thus returning the true branch of the
// second ternary expression.
?>
```
 
</div> 
 
</div>
 
<div class="note">
     
 Chaining of short-ternaries (`?:`), however, is stable and behaves reasonably. It will evaluate to the first argument that evaluates to a non-falsy value. Note that undefined values will still raise a warning. <div class="example">
     
## Short-ternary chaining
 

```php
<?php
echo 0 ?: 1 ?: 2 ?: 3, PHP_EOL; //1
echo 0 ?: 0 ?: 2 ?: 3, PHP_EOL; //2
echo 0 ?: 0 ?: 0 ?: 3, PHP_EOL; //3
?>
```
 
</div> 
 
</div>
 
 
 
## Null Coalescing Operator
 
 Another useful shorthand operator is the "??" (or null coalescing) operator. <div class="example">
     
## Assigning a default value
 

```php
<?php
// Example usage for: Null Coalesce Operator
$action = $_POST['action'] ?? 'default';

// The above is identical to this if/else statement
if (isset($_POST['action'])) {
    $action = $_POST['action'];
} else {
    $action = 'default';
}
?>
```
 
</div> The expression `(expr1) ?? (expr2)` evaluates to <!-- start replaceable -->
<!--
expr2
--> if <!-- start replaceable -->
<!--
expr1
--> is `null`, and <!-- start replaceable -->
<!--
expr1
--> otherwise. 
 
 In particular, this operator does not emit a notice or warning if the left-hand side value does not exist, just like `isset`. This is especially useful on array keys. 
 
<div class="note">
     
 Please note that the null coalescing operator is an expression, and that it doesn't evaluate to a variable, but to the result of an expression. This is important to know if you want to return a variable by reference. The statement return $foo ?? $bar; in a return-by-reference function will therefore not work and a warning is issued. 
 
</div>
 
<div class="note">
     
 The null coalescing operator has low precedence. That means if mixing it with other operators (such as string concatenation or arithmetic operators) parentheses will likely be required. 
 

```php
<?php
// Raises a warning that $name is undefined.
print 'Mr. ' . $name ?? 'Anonymous';

// Prints "Mr. Anonymous"
print 'Mr. ' . ($name ?? 'Anonymous');
?>
```
 
</div>
 
<div class="note">
     
 Please note that the null coalescing operator allows for simple nesting: <div class="example">
     
## Nesting null coalescing operator
 

```php
<?php

$foo = null;
$bar = null;
$baz = 1;
$qux = 2;

echo $foo ?? $bar ?? $baz ?? $qux; // outputs 1

?>
```
 
</div> 
 
</div>
 
