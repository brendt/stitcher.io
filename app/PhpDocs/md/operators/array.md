 
## Array Operators
 
<!-- start titleabbrev -->
<!--
Array
-->
 
<!-- start table -->
<!--

  Array Operators
  
   
    
     Example
     Name
     Result
    
   
   
    
     $a + $b
     Union
     Union of $a and $b.
    
    
     $a == $b
     Equality
     true if $a and $b have the same key/value pairs.
    
    
     $a === $b
     Identity
     true if $a and $b have the same key/value pairs in the same
      order and of the same types.
    
    
     $a != $b
     Inequality
     true if $a is not equal to $b.
    
    
     $a {{ lt }}{{ gt }} $b
     Inequality
     true if $a is not equal to $b.
    
    
     $a !== $b
     Non-identity
     true if $a is not identical to $b.
    
   
  
 
-->
 
 The `+` operator returns the right-hand array appended to the left-hand array; for keys that exist in both arrays, the elements from the left-hand array will be used, and the matching elements from the right-hand array will be ignored. 
 
 <div class="example">
     
## Array Append Operator
 

```php
<?php
$a = array("a" => "apple", "b" => "banana");
$b = array("a" => "pear", "b" => "strawberry", "c" => "cherry");

$c = $a + $b; // Union of $a and $b
echo "Union of \$a and \$b: \n";
var_dump($c);

$c = $b + $a; // Union of $b and $a
echo "Union of \$b and \$a: \n";
var_dump($c);

$a += $b; // Union of $a += $b is $a and $b
echo "Union of \$a += \$b: \n";
var_dump($a);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


Union of $a and $b:
array(3) {
  ["a"]=>
  string(5) "apple"
  ["b"]=>
  string(6) "banana"
  ["c"]=>
  string(6) "cherry"
}
Union of $b and $a:
array(3) {
  ["a"]=>
  string(4) "pear"
  ["b"]=>
  string(10) "strawberry"
  ["c"]=>
  string(6) "cherry"
}
Union of $a += $b:
array(3) {
  ["a"]=>
  string(5) "apple"
  ["b"]=>
  string(6) "banana"
  ["c"]=>
  string(6) "cherry"
}

   
-->
 
</div> 
 
 Elements of arrays are equal for the comparison if they have the same key and value. 
 
 <div class="example">
     
## Comparing arrays
 

```php
<?php
$a = array("apple", "banana");
$b = array(1 => "banana", "0" => "apple");

var_dump($a == $b); // bool(true)
var_dump($a === $b); // bool(false)
?>
```
 
</div> 
 
 
## See Also
 
 <!-- start simplelist -->
<!--

    Array type
    Array functions
   
--> 
 
