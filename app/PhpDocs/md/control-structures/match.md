
 
## match
 

 
 The `match` expression branches evaluation based on an identity check of a value. Similarly to a `switch` statement, a `match` expression has a subject expression that is compared against multiple alternatives. Unlike `switch`, it will evaluate to a value much like ternary expressions. Unlike `switch`, the comparison is an identity check (`===`) rather than a weak equality check (`==`). Match expressions are available as of PHP 8.0.0. 
 
<div class="example">
     
## Structure of a match expression
 

```php
<?php
$return_value = match (subject_expression) {
    single_conditional_expression => return_expression,
    conditional_expression1, conditional_expression2 => return_expression,
};
?>
```
 
<div class="example">
     
## Basic match usage
 

```php
<?php
$food = 'cake';

$return_value = match ($food) {
    'apple' => 'This food is an apple',
    'bar' => 'This food is a bar',
    'cake' => 'This food is a cake',
};

var_dump($return_value);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


string(19) "This food is a cake"

   
-->
 
</div>
 
<div class="example">
     
## Example of using match with comparison operators
 

```php
<?php
$age = 18;

$output = match (true) {
    $age < 2 => "Baby",
    $age < 13 => "Child",
    $age <= 19 => "Teenager",
    $age >= 40 => "Old adult",
    $age > 19 => "Young adult",
};

var_dump($output);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


string(8) "Teenager"

   
-->
 
</div>
 
<div class="note">
     
 The result of a match expression does not need to be used. 
 
</div>
 
<div class="note">
     
 When a match expression is used as a standalone expression it must be terminated by a semicolon ;. 
 
</div>
 
</div>
 
 The `match` expression is similar to a `switch` statement but has some key differences: <ul> 
<li> 
 A match arm compares values strictly (===) instead of loosely as the switch statement does. 
 </li>
 
<li> 
 A match expression returns a value. 
 </li>
 
<li> 
 match arms do not fall-through to later cases the way switch statements do. 
 </li>
 
<li> 
 A match expression must be exhaustive. 
 </li>
 </ul> 
 
 As `switch` statements, `match` expressions are executed match arm by match arm. In the beginning, no code is executed. The conditional expressions are only evaluated if all previous conditional expressions failed to match the subject expression. Only the return expression corresponding to the matching conditional expression will be evaluated. For example:  

```php
<?php
$result = match ($x) {
    foo() => 'value',
    $this->bar() => 'value', // $this->bar() isn't called if foo() === $x
    $this->baz => beep(), // beep() isn't called unless $x === $this->baz
    // etc.
};
?>
```
  
 
 `match` expression arms may contain multiple expressions separated by a comma. That is a logical OR, and is a short-hand for multiple match arms with the same right-hand side. 
 
  

```php
<?php
$result = match ($x) {
    // This match arm:
    $a, $b, $c => 5,
    // Is equivalent to these three match arms:
    $a => 5,
    $b => 5,
    $c => 5,
};
?>
```
  
 
 A special case is the `default` pattern. This pattern matches anything that wasn't previously matched. For example:  

```php
<?php
$expressionResult = match ($condition) {
    1, 2 => foo(),
    3, 4 => bar(),
    default => baz(),
};
?>
```
  <div class="note">
     
 Multiple default patterns will raise a E_FATAL_ERROR error. 
 
</div> 
 
 A `match` expression must be exhaustive. If the subject expression is not handled by any match arm an `UnhandledMatchError` is thrown. 
 
<div class="example">
     
## Example of an unhandled match expression
 

```php
<?php
$condition = 5;

try {
    match ($condition) {
        1, 2 => foo(),
        3, 4 => bar(),
    };
} catch (\UnhandledMatchError $e) {
    var_dump($e);
}
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


object(UnhandledMatchError)#1 (7) {
  ["message":protected]=>
  string(33) "Unhandled match value of type int"
  ["string":"Error":private]=>
  string(0) ""
  ["code":protected]=>
  int(0)
  ["file":protected]=>
  string(9) "/in/ICgGK"
  ["line":protected]=>
  int(6)
  ["trace":"Error":private]=>
  array(0) {
  }
  ["previous":"Error":private]=>
  NULL
}

  
-->
 
</div>
 
 
## Using match expressions to handle non identity checks
 
 It is possible to use a `match` expression to handle non-identity conditional cases by using `true` as the subject expression. 
 
<div class="example">
     
## Using a generalized match expressions to branch on integer ranges
 

```php
<?php

$age = 23;

$result = match (true) {
    $age >= 65 => 'senior',
    $age >= 25 => 'adult',
    $age >= 18 => 'young adult',
    default => 'kid',
};

var_dump($result);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


string(11) "young adult"

   
-->
 
</div>
 
<div class="example">
     
## Using a generalized match expressions to branch on string content
 

```php
<?php

$text = 'Bienvenue chez nous';

$result = match (true) {
    str_contains($text, 'Welcome'), str_contains($text, 'Hello') => 'en',
    str_contains($text, 'Bienvenue'), str_contains($text, 'Bonjour') => 'fr',
    // ...
};

var_dump($result);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


string(2) "fr"

   
-->
 
</div>
 

