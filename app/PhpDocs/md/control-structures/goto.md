
 
## goto
 

 
 <!-- start mediaobject -->
<!--

   What's the worse thing that could happen if you use goto?
   
    
   
   
    
     Image courtesy of xkcd
    
   
  
--> 
 
 The `goto` operator can be used to jump to another section in the program. The target point is specified by a <!-- start emphasis -->
<!--
case-sensitive
--> label followed by a colon, and the instruction is given as `goto` followed by the desired target label. This is not a full unrestricted `goto`. The target label must be within the same file and context, meaning that you cannot jump out of a function or method, nor can you jump into one. You also cannot jump into any sort of loop or switch structure. You may jump out of these, and a common use is to use a `goto` in place of a multi-level `break`. 
 
 <div class="example">
     
## goto example
 

```php
<?php

goto a;
echo 'Foo';

a:
echo 'Bar';

?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


Bar

   
-->
 
</div> 
 
 <div class="example">
     
## goto loop example
 

```php
<?php

for ($i = 0, $j = 50; $i < 100; $i++) {
    while ($j--) {
        if ($j == 17) {
            goto end;
        }
    }
}
echo "i = $i";
end:
echo 'j hit 17';

?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


j hit 17

   
-->
 
</div> 
 
 <div class="example">
     
## This will not work
 

```php
<?php

goto loop;
for ($i = 0, $j = 50; $i < 100; $i++) {
    while ($j--) {
        loop:
    }
}
echo "$i = $i";

?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


Fatal error: 'goto' into loop or switch statement is disallowed in
script on line 2

   
-->
 
</div> 

