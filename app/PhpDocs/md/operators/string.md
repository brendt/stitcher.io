 
## String Operators
 
<!-- start titleabbrev -->
<!--
String
-->
 
 There are two string operators. The first is the concatenation operator ('.'), which returns the concatenation of its right and left arguments. The second is the concatenating assignment operator ('.='), which appends the argument on the right side to the argument on the left side. Please read Assignment Operators for more information. 
 
 <div class="example">
     
## String Concatenating
 

```php
<?php
$a = "Hello ";
$b = $a . "World!"; // now $b contains "Hello World!"
var_dump($b);

$a = "Hello ";
$a .= "World!";     // now $a contains "Hello World!"
var_dump($a);
?>
```
 
</div> 
 
 
## See Also
 
 <!-- start simplelist -->
<!--

    String type
    String functions
   
--> 
 
