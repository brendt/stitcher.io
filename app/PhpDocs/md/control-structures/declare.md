
 
## declare
 

 
 The `declare` construct is used to set execution directives for a block of code. The syntax of `declare` is similar to the syntax of other flow control constructs:  

```
declare (directive)
    statement
```
  
 
 The `directive` section allows the behavior of the `declare` block to be set. Currently only three directives are recognized: <!-- start simplelist -->
<!--

   ticks
   encoding
   strict_types
  
--> 
 
 As directives are handled as the file is being compiled, only literals may be given as directive values. Variables and constants cannot be used. To illustrate:  

```php
<?php
// This is valid:
declare(ticks=1);

// This is invalid:
const TICK_VALUE = 1;
declare(ticks=TICK_VALUE);
?>
```
  
 
 The `statement` part of the `declare` block will be executed - how it is executed and what side effects occur during execution may depend on the directive set in the `directive` block. 
 
 The `declare` construct can also be used in the global scope, affecting all code following it (however if the file with `declare` was included then it does not affect the parent file).  

```php
<?php
// these are the same:

// you can use this:
declare(ticks=1) {
    // entire script here
}

// or you can use this:
declare(ticks=1);
// entire script here
?>
```
  
 
<!-- start sect2 -->
<!--

  Ticks
  A tick is an event that occurs for every
  N low-level tickable statements executed
  by the parser within the declare block.
  The value for N is specified
  using ticks=N
  within the declare block's
  directive section.
 
 
  Not all statements are tickable. Typically, condition
  expressions and argument expressions are not tickable.
 
 
  The event(s) that occur on each tick are specified using the
  register_tick_function. See the example
  below for more details. Note that more than one event can occur
  for each tick.
 
 
  
   Tick usage example
   

<?php

declare(ticks=1);

// A function called on each tick event
function tick_handler()
{
    echo "tick_handler() called\n";
}

register_tick_function('tick_handler'); // causes a tick event

$a = 1; // causes a tick event

if ($a > 0) {
    $a += 2; // causes a tick event
    print $a; // causes a tick event
}

?>

   
  
 
 
  See also register_tick_function and
  unregister_tick_function.
 
 
-->
 
<!-- start sect2 -->
<!--

  Encoding
  
    A script's encoding can be specified per-script using the encoding directive.
  
   Declaring an encoding for the script
    

<?php
declare(encoding='ISO-8859-1');
// code here
?>

    
   
  

  
   
    When combined with namespaces, the only legal syntax for declare
    is declare(encoding='...'); where ...
    is the encoding value.  declare(encoding='...') {}
    will result in a parse error when combined with namespaces.
   
  
  
   See also zend.script_encoding.
  
 
-->

