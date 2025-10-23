
 
## Object Iteration
 
 PHP provides a way for objects to be defined so it is possible to iterate through a list of items, with, for example a [foreach](control-structures.foreach)] statement. By default, all [visible](language.oop5.visibility)] properties will be used for the iteration. 
 
<div class="example">
     
## Simple Object Iteration
 

```php
<?php
class MyClass
{
    public $var1 = 'value 1';
    public $var2 = 'value 2';
    public $var3 = 'value 3';

    protected $protected = 'protected var';
    private   $private   = 'private var';

    function iterateVisible() {
       echo "MyClass::iterateVisible:\n";
       foreach ($this as $key => $value) {
           print "$key => $value\n";
       }
    }
}

$class = new MyClass();

foreach($class as $key => $value) {
    print "$key => $value\n";
}
echo "\n";


$class->iterateVisible();

?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


var1 => value 1
var2 => value 2
var3 => value 3

MyClass::iterateVisible:
var1 => value 1
var2 => value 2
var3 => value 3
protected => protected var
private => private var

   
-->
 
</div>
 
 As the output shows, the [foreach](control-structures.foreach)] iterated through all of the [visible](language.oop5.visibility)] properties that could be accessed. 
 
<!-- start simplesect -->
<!--

  See Also
  
   
    Generators
    Iterator
    IteratorAggregate 
    SPL Iterators
   
  
 
-->
 
