
 
## foreach
 

 
 The `foreach` construct provides an easy way to iterate over `array`s and <!-- start interfacename -->
<!--
Traversable
--> objects. `foreach` will issue an error when used with a variable containing a different data type or with an uninitialized variable.  
 foreach can optionally get the key of each element: 
 

```
foreach (iterable_expression as $value) {
    statement_list
}

foreach (iterable_expression as $key => $value) {
    statement_list
}
```
  
 
 The first form traverses the iterable given by iterable_expression. On each iteration, the value of the current element is assigned to $value. 
 
 The second form will additionally assign the current element's key to the $key variable on each iteration. 
 
 Note that foreach does not modify the internal array pointer, which is used by functions such as current and key. 
 
 It is possible to customize object iteration. 
 
<div class="example">
     
## Common foreach usages
 

```php
<?php

/* Example: value only */
$array = [1, 2, 3, 17];

foreach ($array as $value) {
    echo "Current element of \$array: $value.\n";
}

/* Example: key and value */
$array = [
    "one" => 1,
    "two" => 2,
    "three" => 3,
    "seventeen" => 17
];

foreach ($array as $key => $value) {
    echo "Key: $key => Value: $value\n";
}

/* Example: multi-dimensional key-value arrays */
$grid = [];
$grid[0][0] = "a";
$grid[0][1] = "b";
$grid[1][0] = "y";
$grid[1][1] = "z";

foreach ($grid as $y => $row) {
    foreach ($row as $x => $value) {
        echo "Value at position x=$x and y=$y: $value\n";
    }
}

/* Example: dynamic arrays */
foreach (range(1, 5) as $value) {
    echo "$value\n";
}
?>
```
 
</div>
 
<div class="note">
     
 `foreach` does not support the ability to suppress error messages using the [@](language.operators.errorcontrol)]. 
 
</div>
 
 
## Unpacking nested arrays
 

 
 It is possible to iterate over an array of arrays and unpack the nested array into loop variables by using either [array destructuring](language.types.array.syntax.destructuring)] via `[]` or by using the `list` language construct as the value. <div class="note">
     
 Please note that array destructuring via [] is only possible as of PHP 7.1.0 
 
</div> 
 
  
 In both of the following examples $a will be set to the first element of the nested array and $b will contain the second element: 
 

```php
<?php
$array = [
    [1, 2],
    [3, 4],
];

foreach ($array as [$a, $b]) {
    echo "A: $a; B: $b\n";
}

foreach ($array as list($a, $b)) {
    echo "A: $a; B: $b\n";
}
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


A: 1; B: 2
A: 3; B: 4

    
-->
  
 
 When providing fewer variables than there are elements in the array, the remaining elements will be ignored. Similarly, elements can be skipped over by using a comma:  

```php
<?php
$array = [
    [1, 2, 5],
    [3, 4, 6],
];

foreach ($array as [$a, $b]) {
    // Note that there is no $c here.
    echo "$a $b\n";
}

foreach ($array as [, , $c]) {
    // Skipping over $a and $b
    echo "$c\n";
}
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


1 2
3 4
5
6

    
-->
  
 
 A notice will be generated if there aren't enough array elements to fill the `list`:  

```php
<?php
$array = [
    [1, 2],
    [3, 4],
];

foreach ($array as [$a, $b, $c]) {
    echo "A: $a; B: $b; C: $c\n";
}
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


Notice: Undefined offset: 2 in example.php on line 7
A: 1; B: 2; C:

Notice: Undefined offset: 2 in example.php on line 7
A: 3; B: 4; C:

    
-->
  
 
 
 
## foreach and references
 
 It is possible to directly modify array elements within a loop by preceding `$value` with `{{ amp }}`. In that case the value will be assigned by [reference](language.references)].  

```php
<?php
$arr = [1, 2, 3, 4];
foreach ($arr as &$value) {
    $value = $value * 2;
}
// $arr is now [2, 4, 6, 8]
unset($value); // break the reference with the last element
?>
```
  
 
<div class="warning">
     
 Reference to a $value of the last array element remain even after the foreach loop. It is recommended to destroy these using unset. Otherwise, the following behavior will occur: 
 
 

```php
<?php
$arr = [1, 2, 3, 4];
foreach ($arr as &$value) {
    $value = $value * 2;
}
// $arr is now [2, 4, 6, 8]

// without an unset($value), $value is still a reference to the last item: $arr[3]

foreach ($arr as $key => $value) {
    // $arr[3] will be updated with each value from $arr...
    echo "{$key} => {$value} ";
    print_r($arr);
}
// ...until ultimately the second-to-last value is copied onto the last value
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


0 => 2 Array ( [0] => 2, [1] => 4, [2] => 6, [3] => 2 )
1 => 4 Array ( [0] => 2, [1] => 4, [2] => 6, [3] => 4 )
2 => 6 Array ( [0] => 2, [1] => 4, [2] => 6, [3] => 6 )
3 => 6 Array ( [0] => 2, [1] => 4, [2] => 6, [3] => 6 )

    
-->
 
 
</div>
 
<div class="example">
     
## Iterate a constant array's values by reference
 

```php
<?php
foreach ([1, 2, 3, 4] as &$value) {
    $value = $value * 2;
}
?>
```
 
</div>
 
 
 
## See Also
 
<!-- start simplelist -->
<!--

   array
   Traversable
   iterable
   list
  
-->
 

