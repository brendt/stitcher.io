
 
## else
 

 
 Often you'd want to execute a statement if a certain condition is met, and a different statement if the condition is not met. This is what `else` is for. `else` extends an `if` statement to execute a statement in case the expression in the `if` statement evaluates to `false`. For example, the following code would display <!-- start computeroutput -->
<!--
a is greater than
  b
--> if <!-- start varname -->
<!--
$a
--> is greater than <!-- start varname -->
<!--
$b
-->, and <!-- start computeroutput -->
<!--
a is NOT greater
  than b
--> otherwise:  

```php
<?php
if ($a > $b) {
  echo "a is greater than b";
} else {
  echo "a is NOT greater than b";
}
?>
```
  The `else` statement is only executed if the `if` expression evaluated to `false`, and if there were any `elseif` expressions - only if they evaluated to `false` as well (see [elseif](control-structures.elseif)]). 
 
<div class="note">
     
## Dangling else
 
 In case of nested `if`-`else` statements, an `else` is always associated with the nearest `if`.  

```php
<?php
$a = false;
$b = true;
if ($a)
    if ($b)
        echo "b";
else
    echo "c";
?>
```
  Despite the indentation (which does not matter for PHP), the `else` is associated with the `if ($b)`, so the example does not produce any output. While relying on this behavior is valid, it is recommended to avoid it by using curly braces to resolve potential ambiguities. 
 
</div>

