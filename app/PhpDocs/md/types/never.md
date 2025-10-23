 
## Never
 
 <!-- start type -->
<!--
never
--> is a return-only type indicating the function does not terminate. This means that it either calls <!-- start function -->
<!--
exit
-->, throws an exception, or is an infinite loop. Therefore, it cannot be part of a [union type](language.types.type-system.composite.union)] declaration. Available as of PHP 8.1.0. 
 
 <!-- start type -->
<!--
never
--> is, in type theory parlance, the bottom type. Meaning it is the subtype of every other type and can replace any other return type during inheritance. 

