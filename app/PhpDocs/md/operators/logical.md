 
## Logical Operators
 
<!-- start titleabbrev -->
<!--
Logic
-->
 
<!-- start table -->
<!--

  Logical Operators
  
   
    
     Example
     Name
     Result
    
   
   
    
     $a and $b
     And
     true if both $a and $b are true.
    
    
     $a or $b
     Or
     true if either $a or $b is true.
    
    
     $a xor $b
     Xor
     true if either $a or $b is true, but not both.
    
    
     ! $a
     Not
     true if $a is not true.
    
    
     $a {{ amp }}{{ amp }} $b
     And
     true if both $a and $b are true.
    
    
     $a || $b
     Or
     true if either $a or $b is true.
    
   
  
 
-->
 
 The reason for the two different variations of "and" and "or" operators is that they operate at different precedences. (See Operator Precedence.) 
 
<div class="example">
     
## Logical operators illustrated
 

```php
<?php

// --------------------
// foo() will never get called as those operators are short-circuit

$a = (false && foo());
$b = (true  || foo());
$c = (false and foo());
$d = (true  or  foo());

// --------------------
// "||" has a greater precedence than "or"

// The result of the expression (false || true) is assigned to $e
// Acts like: ($e = (false || true))
$e = false || true;

// The constant false is assigned to $f before the "or" operation occurs
// Acts like: (($f = false) or true)
$f = false or true;

var_dump($e, $f);

// --------------------
// "&&" has a greater precedence than "and"

// The result of the expression (true && false) is assigned to $g
// Acts like: ($g = (true && false))
$g = true && false;

// The constant true is assigned to $h before the "and" operation occurs
// Acts like: (($h = true) and false)
$h = true and false;

var_dump($g, $h);
?>
```
 TODO 
<!-- start screen -->
<!--


bool(true)
bool(false)
bool(false)
bool(true)

  
-->
 
</div>
