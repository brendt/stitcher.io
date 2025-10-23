
 
## while
 

 
 `while` loops are the simplest type of loop in PHP. They behave just like their C counterparts. The basic form of a `while` statement is:  

```
while (expr)
    statement
```
  
 
 The meaning of a while statement is simple. It tells PHP to execute the nested statement(s) repeatedly, as long as the while expression evaluates to true. The value of the expression is checked each time at the beginning of the loop, so even if this value changes during the execution of the nested statement(s), execution will not stop until the end of the iteration (each time PHP runs the statements in the loop is one iteration). If the while expression evaluates to false from the very beginning, the nested statement(s) won't even be run once. 
 
 Like with the `if` statement, you can group multiple statements within the same `while` loop by surrounding a group of statements with curly braces, or by using the alternate syntax:  

```
while (expr):
    statement
    ...
endwhile;
```
  
 
 The following examples are identical, and both print the numbers 1 through 10:  

```php
<?php
/* example 1 */

$i = 1;
while ($i <= 10) {
    echo $i++;  /* the printed value would be
                   $i before the increment
                   (post-increment) */
}

/* example 2 */

$i = 1;
while ($i <= 10):
    echo $i;
    $i++;
endwhile;
?>
```
  

