
 
## for
 

 
 `for` loops are the most complex loops in PHP. They behave like their C counterparts. The syntax of a `for` loop is:  

```
for (expr1; expr2; expr3)
    statement
```
  
 
 The first expression (expr1) is evaluated (executed) once unconditionally at the beginning of the loop. 
 
 In the beginning of each iteration, expr2 is evaluated. If it evaluates to true, the loop continues and the nested statement(s) are executed. If it evaluates to false, the execution of the loop ends. 
 
 At the end of each iteration, expr3 is evaluated (executed). 
 
 Each of the expressions can be empty or contain multiple expressions separated by commas. In expr2, all expressions separated by a comma are evaluated but the result is taken from the last part. expr2 being empty means the loop should be run indefinitely (PHP implicitly considers it as true, like C). This may not be as useless as you might think, since often you'd want to end the loop using a conditional break statement instead of using the for truth expression. 
 
 Consider the following examples. All of them display the numbers 1 through 10:  

```php
<?php
/* example 1 */

for ($i = 1; $i <= 10; $i++) {
    echo $i;
}

/* example 2 */

for ($i = 1; ; $i++) {
    if ($i > 10) {
        break;
    }
    echo $i;
}

/* example 3 */

$i = 1;
for (; ; ) {
    if ($i > 10) {
        break;
    }
    echo $i;
    $i++;
}

/* example 4 */

for ($i = 1, $j = 0; $i <= 10; $j += $i, print $i, $i++);
?>
```
  
 
 Of course, the first example appears to be the nicest one (or perhaps the fourth), but you may find that being able to use empty expressions in for loops comes in handy in many occasions. 
 
 PHP also supports the alternate "colon syntax" for `for` loops.  

```
for (expr1; expr2; expr3):
    statement
    ...
endfor;
```
  
 
 It's a common thing to many users to iterate through arrays like in the example below. 
 
  

```php
<?php
/*
 * This is an array with some data we want to modify
 * when running through the for loop.
 */
$people = array(
    array('name' => 'Kalle', 'salt' => 856412),
    array('name' => 'Pierre', 'salt' => 215863)
);

for($i = 0; $i < count($people); ++$i) {
    $people[$i]['salt'] = mt_rand(000000, 999999);
}
?>
```
  
 
 The above code can be slow, because the array size is fetched on every iteration. Since the size never changes, the loop be easily optimized by using an intermediate variable to store the size instead of repeatedly calling count: 
 
  

```php
<?php
$people = array(
    array('name' => 'Kalle', 'salt' => 856412),
    array('name' => 'Pierre', 'salt' => 215863)
);

for($i = 0, $size = count($people); $i < $size; ++$i) {
    $people[$i]['salt'] = mt_rand(000000, 999999);
}
?>
```
  

