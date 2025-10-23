 
## Arithmetic Operators
 
<!-- start titleabbrev -->
<!--
Arithmetic
-->
 
 Remember basic arithmetic from school? These work just like those. 
 
<!-- start table -->
<!--

  Arithmetic Operators
  
   
    
     Example
     Name
     Result
    
   
   
    
     +$a
     Identity
     
      Conversion of $a to int or
      float as appropriate.
     
    
    
     -$a
     Negation
     Opposite of $a.
    
    
     $a + $b
     Addition
     Sum of $a and $b.
    
    
     $a - $b
     Subtraction
     Difference of $a and $b.
    
    
     $a * $b
     Multiplication
     Product of $a and $b.
    
    
     $a / $b
     Division
     Quotient of $a and $b.
    
    
     $a % $b
     Modulo
     Remainder of $a divided by $b.
    
    
     $a ** $b
     Exponentiation
     Result of raising $a to the $b'th power.
    
   
  
 
-->
 
 The division operator / returns a float value unless the two operands are int (or numeric strings which are type juggled to int) and the numerator is a multiple of the divisor, in which case an integer value will be returned. For integer division, see intdiv. 
 
 Operands of modulo are converted to int before processing. For floating-point modulo, see fmod. 
 
 The result of the modulo operator `%` has the same sign as the dividend — that is, the result of `$a % $b` will have the same sign as <!-- start varname -->
<!--
$a
-->. For example: <div class="example">
     
## The Modulo Operator
 

```php
<?php
var_dump(5 % 3);
var_dump(5 % -3);
var_dump(-5 % 3);
var_dump(-5 % -3);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


int(2)
int(2)
int(-2)
int(-2)

   
-->
 
</div> 
 
 
## See Also
 
 <!-- start simplelist -->
<!--

    Math functions
   
--> 
 
