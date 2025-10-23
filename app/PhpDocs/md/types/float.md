
 
## Floating point numbers
 
 Floating point numbers (also known as "floats", "doubles", or "real numbers") can be specified using any of the following syntaxes: 
 
 

```php
<?php
$a = 1.234;
$b = 1.2e3;
$c = 7E-10;
$d = 1_234.567; // as of PHP 7.4.0
?>
```
 
 
 Formally as of PHP 7.4.0 (previously, underscores have not been allowed): 
 
 

```
LNUM          [0-9]+(_[0-9]+)*
DNUM          ({LNUM}?"."{LNUM}) | ({LNUM}"."{LNUM}?)
EXPONENT_DNUM (({LNUM} | {DNUM}) [eE][+-]? {LNUM})
```
 
 
 The size of a float is platform-dependent, although a maximum of approximately 1.8e308 with a precision of roughly 14 decimal digits is a common value (the 64 bit IEEE format). 
 
<div class="warning">
     
## Floating point precision
 
 Floating point numbers have limited precision. Although it depends on the system, PHP typically uses the IEEE 754 double precision format, which will give a maximum relative error due to rounding in the order of 1.11e-16. Non elementary arithmetic operations may give larger errors, and, of course, error propagation must be considered when several operations are compounded. 
 
 Additionally, rational numbers that are exactly representable as floating point numbers in base 10, like `0.1` or `0.7`, do not have an exact representation as floating point numbers in base 2, which is used internally, no matter the size of the mantissa. Hence, they cannot be converted into their internal binary counterparts without a small loss of precision. This can lead to confusing results: for example, `floor((0.1+0.7)*10)` will usually return `7` instead of the expected `8`, since the internal representation will be something like `7.9999999999999991118...`. 
 
 So never trust floating number results to the last digit, and do not compare floating point numbers directly for equality. If higher precision is necessary, the [arbitrary precision math functions](ref.bc)] and [gmp](ref.gmp)] functions are available. 
 
 For a "simple" explanation, see the [floating point guide](TODO)] that's also titled "Why don’t my numbers add up?" 
 
</div>
 
 
## Converting to float
 
<!-- start sect3 -->
<!--

   From strings

   
    If the string is
    numeric
    or leading numeric then it will resolve to the
    corresponding float value, otherwise it is converted to zero
    (0).
   
  
-->
 
<!-- start sect3 -->
<!--

   From other types

   
    For values of other types, the conversion is performed by converting the
    value to int first and then to float. See
    Converting to integer
    for more information.
   

   
    
     As certain types have undefined behavior when converting to
     int, this is also the case when converting to
     float.
    
   
  
-->
 
 
 
## Comparing floats
 
 As noted in the warning above, testing floating point values for equality is problematic, due to the way that they are represented internally. However, there are ways to make comparisons of floating point values that work around these limitations. 
 
 To test floating point values for equality, an upper bound on the relative error due to rounding is used. This value is known as the machine epsilon, or unit roundoff, and is the smallest acceptable difference in calculations. 
 
 <!-- start varname -->
<!--
$a
--> and <!-- start varname -->
<!--
$b
--> are equal to 5 digits of precision. 
 
<div class="example">
     
## Comparing Floats
 

```php
<?php
$a = 1.23456789;
$b = 1.23456780;
$epsilon = 0.00001;

if (abs($a - $b) < $epsilon) {
    echo "true";
}
?>
```
 
</div>
 
 
 
## NaN
 
 Some numeric operations can result in a value represented by the constant `NAN`. This result represents an undefined or unrepresentable value in floating-point calculations. Any loose or strict comparisons of this value against any other value, including itself, but except `true`, will have a result of `false`. 
 
 Because `NAN` represents any number of different values, `NAN` should not be compared to other values, including itself, and instead should be checked for using `is_nan`. 
 

