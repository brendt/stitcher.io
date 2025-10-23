 
## Execution Operators
 
<!-- start titleabbrev -->
<!--
Execution
-->
 
 PHP supports one execution operator: backticks (````). Note that these are not single-quotes! PHP will attempt to execute the contents of the backticks as a shell command; the output will be returned (i.e., it won't simply be dumped to output; it can be assigned to a variable). Use of the backtick operator is identical to `shell_exec`. 
 
 <div class="example">
     
## Backtick Operator
 

```php
<?php
$output = `ls -al`;
echo "<pre>$output</pre>";
?>
```
 
</div> 
 
<div class="note">
     
 The backtick operator is disabled when `shell_exec` is disabled. 
 
</div>
 
<div class="note">
     
 Unlike some other languages, backticks have no special meaning within double-quoted strings. 
 
</div>
 
 
## See Also
 
 <!-- start simplelist -->
<!--

    Program Execution functions
    popen
    proc_open
    Using PHP from the commandline
   
--> 
 
