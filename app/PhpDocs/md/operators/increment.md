 
## Incrementing/Decrementing Operators
 
<!-- start titleabbrev -->
<!--
Increment and Decrement
-->
 
 PHP supports pre- and post-increment and decrement operators. Those unary operators allow to increment or decrement the value by one. 
 
<!-- start table -->
<!--

  Increment/decrement Operators
  
   
    
     Example
     Name
     Effect
    
   
   
    
     ++$a
     Pre-increment
     Increments $a by one, then returns $a.
    
    
     $a++
     Post-increment
     Returns $a, then increments $a by one.
    
    
     --$a
     Pre-decrement
     Decrements $a by one, then returns $a.
    
    
     $a--
     Post-decrement
     Returns $a, then decrements $a by one.
    
   
  
 
-->
 
 <div class="example">
     
## Examples of Increment/Decrement
 

```php
<?php
echo 'Post-increment:', PHP_EOL;
$a = 5;
var_dump($a++);
var_dump($a);

echo 'Pre-increment:', PHP_EOL;
$a = 5;
var_dump(++$a);
var_dump($a);

echo 'Post-decrement:', PHP_EOL;
$a = 5;
var_dump($a--);
var_dump($a);

echo 'Pre-decrement:', PHP_EOL;
$a = 5;
var_dump(--$a);
var_dump($a);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


Post-increment:
int(5)
int(6)
Pre-increment:
int(6)
int(6)
Post-decrement:
int(5)
int(4)
Pre-decrement:
int(4)
int(4)

   
-->
 
</div> <div class="warning">
     
 The increment and decrement operators have no effect on values of type `bool`. A `E_WARNING` is emitted as of PHP 8.3.0, because this will implicitly cast the value to `int` in the future. 
 
 The decrement operator has no effect on values of type `null`. A `E_WARNING` is emitted as of PHP 8.3.0, because this will implicitly cast the value to `int` in the future. 
 
 The decrement operator has no effect on non- [numeric string](language.types.numeric-strings)]. A `E_WARNING` is emitted as of PHP 8.3.0, because a `TypeError` will be thrown in the future. 
 
</div> <div class="note">
     
 Internal objects that support overloading addition and/or subtraction can also be incremented and/or decremented. One such internal object is `GMP`. 
 
</div> 
 
 
## PERL string increment feature
 
<div class="warning">
     
 This feature is soft-deprecated as of PHP 8.3.0. The str_increment function should be used instead. 
 
</div>
 
 It is possible to increment a non- [numeric string](language.types.numeric-strings)] in PHP. The string must be an alphanumerical ASCII string. Which increments letters up to the next letter, when reaching the letter `Z` the increment is carried to the value on the left. For example, `$a = 'Z'; $a++;` turns <!-- start varname -->
<!--
$a
--> into `'AA'`. 
 
<div class="example">
     
## PERL string increment example
 

```php
<?php
echo '== Alphabetic strings ==' . PHP_EOL;
$s = 'W';
for ($n=0; $n<6; $n++) {
    echo ++$s . PHP_EOL;
}
// Alphanumeric strings behave differently
echo '== Alphanumeric strings ==' . PHP_EOL;
$d = 'A8';
for ($n=0; $n<6; $n++) {
    echo ++$d . PHP_EOL;
}
$d = 'A08';
for ($n=0; $n<6; $n++) {
    echo ++$d . PHP_EOL;
}
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


== Alphabetic strings ==
X
Y
Z
AA
AB
AC
== Alphanumeric strings ==
A9
B0
B1
B2
B3
B4
A09
A10
A11
A12
A13
A14

   
-->
 
</div>
 
<div class="warning">
     
 If the alphanumerical string can be interpreted as a [numeric string](language.types.numeric-strings)] it will be cast to an `int` or `float`. This is particularly an issue with strings that look like a floating point numbers written in exponential notation. The `str_increment` function does not suffer from these implicit type cast. 
 
<div class="example">
     
## Alphanumerical string converted to float
 

```php
<?php
$s = "5d9";
var_dump(++$s);
var_dump(++$s);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


string(3) "5e0"
float(6)

    
-->
 
 This is because the value `"5e0"` is interpreted as a `float` and cast to the value `5.0` before being incremented. 
 
</div>
 
</div>
 
