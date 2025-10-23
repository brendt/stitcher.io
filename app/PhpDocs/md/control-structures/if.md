
 
## if
 

 
 The `if` construct is one of the most important features of many languages, PHP included. It allows for conditional execution of code fragments. PHP features an `if` structure that is similar to that of C:  

```
if (expr)
  statement
```
  
 
 As described in the section about expressions, expression is evaluated to its Boolean value. If expression evaluates to true, PHP will execute statement, and if it evaluates to false - it'll ignore it. More information about what values evaluate to false can be found in the 'Converting to boolean' section. 
 
 The following example would display <!-- start computeroutput -->
<!--
a is bigger
  than b
--> if <!-- start varname -->
<!--
$a
--> is bigger than <!-- start varname -->
<!--
$b
-->:  

```php
<?php
if ($a > $b)
  echo "a is bigger than b";
?>
```
  
 
 Often you'd want to have more than one statement to be executed conditionally. Of course, there's no need to wrap each statement with an `if` clause. Instead, you can group several statements into a statement group. For example, this code would display <!-- start computeroutput -->
<!--
a is bigger than b
--> if <!-- start varname -->
<!--
$a
--> is bigger than <!-- start varname -->
<!--
$b
-->, and would then assign the value of <!-- start varname -->
<!--
$a
--> into <!-- start varname -->
<!--
$b
-->:  

```php
<?php
if ($a > $b) {
  echo "a is bigger than b";
  $b = $a;
}
?>
```
  
 
 If statements can be nested infinitely within other if statements, which provides you with complete flexibility for conditional execution of the various parts of your program. 

