
 
## Overloading
 
 Overloading in PHP provides means to dynamically <!-- start quote -->
<!--
create
--> properties and methods. These dynamic entities are processed via magic methods one can establish in a class for various action types. 
 
 The overloading methods are invoked when interacting with properties or methods that have not been declared or are not [visible](language.oop5.visibility)] in the current scope. The rest of this section will use the terms <!-- start quote -->
<!--
inaccessible properties
--> and <!-- start quote -->
<!--
inaccessible
   methods
--> to refer to this combination of declaration and visibility. 
 
 All overloading methods must be defined as `public`. 
 
<div class="note">
     
 None of the arguments of these magic methods can be [passed by
    reference](functions.arguments.by-reference)]. 
 
</div>
 
<div class="note">
     
 PHP's interpretation of <!-- start quote -->
<!--
overloading
--> is different than most object-oriented languages. Overloading traditionally provides the ability to have multiple methods with the same name but different quantities and types of arguments. 
 
</div>
 
 
## Property overloading
 
<!-- start methodsynopsis -->
<!--

    public void__set
    stringname
    mixedvalue
   
-->
 
<!-- start methodsynopsis -->
<!--

    public mixed__get
    stringname
   
-->
 
<!-- start methodsynopsis -->
<!--

    public bool__isset
    stringname
   
-->
 
<!-- start methodsynopsis -->
<!--

    public void__unset
    stringname
   
-->
 
 [__set()](object.set)] is run when writing data to inaccessible (protected or private) or non-existing properties. 
 
 [__get()](object.get)] is utilized for reading data from inaccessible (protected or private) or non-existing properties. 
 
 [__isset()](object.isset)] is triggered by calling `isset` or `empty` on inaccessible (protected or private) or non-existing properties. 
 
 [__unset()](object.unset)] is invoked when `unset` is used on inaccessible (protected or private) or non-existing properties. 
 
 The <!-- start varname -->
<!--
$name
--> argument is the name of the property being interacted with. The [__set()](object.set)] method's <!-- start varname -->
<!--
$value
--> argument specifies the value the <!-- start varname -->
<!--
$name
-->'ed property should be set to. 
 
 Property overloading only works in object context. These magic methods will not be triggered in static context. Therefore these methods should not be declared [static](language.oop5.static)]. A warning is issued if one of the magic overloading methods is declared `static`. 
 
<div class="note">
     
 The return value of [__set()](object.set)] is ignored because of the way PHP processes the assignment operator. Similarly, [__get()](object.get)] is never called when chaining assignments together like this: ` $a = $obj->b = 8; ` 
 
</div>
 
<div class="note">
     
 PHP will not call an overloaded method from within the same overloaded method. That means, for example, writing `return $this->foo` inside of [__get()](object.get)] will return `null` and raise an `E_WARNING` if there is no `foo` property defined, rather than calling [__get()](object.get)] a second time. However, overload methods may invoke other overload methods implicitly (such as [__set()](object.set)] triggering [__get()](object.get)]). 
 
</div>
 
<div class="example">
     
## 
     Overloading properties via the __get(),
     __set(), __isset()
     and __unset() methods
    
 

```php
<?php
class PropertyTest
{
    /**  Location for overloaded data.  */
    private $data = array();

    /**  Overloading not used on declared properties.  */
    public $declared = 1;

    /**  Overloading only used on this when accessed outside the class.  */
    private $hidden = 2;

    public function __set($name, $value)
    {
        echo "Setting '$name' to '$value'\n";
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        echo "Getting '$name'\n";
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    public function __isset($name)
    {
        echo "Is '$name' set?\n";
        return isset($this->data[$name]);
    }

    public function __unset($name)
    {
        echo "Unsetting '$name'\n";
        unset($this->data[$name]);
    }

    /**  Not a magic method, just here for example.  */
    public function getHidden()
    {
        return $this->hidden;
    }
}


$obj = new PropertyTest;

$obj->a = 1;
echo $obj->a . "\n\n";

var_dump(isset($obj->a));
unset($obj->a);
var_dump(isset($obj->a));
echo "\n";

echo $obj->declared . "\n\n";

echo "Let's experiment with the private property named 'hidden':\n";
echo "Privates are visible inside the class, so __get() not used...\n";
echo $obj->getHidden() . "\n";
echo "Privates not visible outside of class, so __get() is used...\n";
echo $obj->hidden . "\n";
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


Setting 'a' to '1'
Getting 'a'
1

Is 'a' set?
bool(true)
Unsetting 'a'
Is 'a' set?
bool(false)

1

Let's experiment with the private property named 'hidden':
Privates are visible inside the class, so __get() not used...
2
Privates not visible outside of class, so __get() is used...
Getting 'hidden'


Notice:  Undefined property via __get(): hidden in <file> on line 70 in <file> on line 29

    
-->
 
</div>
 
 
 
## Method overloading
 
<!-- start methodsynopsis -->
<!--

    public mixed__call
    stringname
    arrayarguments
   
-->
 
<!-- start methodsynopsis -->
<!--

    public static mixed__callStatic
    stringname
    arrayarguments
   
-->
 
 [__call()](object.call)] is triggered when invoking inaccessible methods in an object context. 
 
 [__callStatic()](object.callstatic)] is triggered when invoking inaccessible methods in a static context. 
 
 The <!-- start varname -->
<!--
$name
--> argument is the name of the method being called. The <!-- start varname -->
<!--
$arguments
--> argument is an enumerated array containing the parameters passed to the <!-- start varname -->
<!--
$name
-->'ed method. 
 
<div class="example">
     
## 
     Overloading methods via the __call()
     and __callStatic() methods
    
 

```php
<?php
class MethodTest
{
    public function __call($name, $arguments)
    {
        // Note: value of $name is case sensitive.
        echo "Calling object method '$name' "
             . implode(', ', $arguments). "\n";
    }

    public static function __callStatic($name, $arguments)
    {
        // Note: value of $name is case sensitive.
        echo "Calling static method '$name' "
             . implode(', ', $arguments). "\n";
    }
}

$obj = new MethodTest;
$obj->runTest('in object context');

MethodTest::runTest('in static context');
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


Calling object method 'runTest' in object context
Calling static method 'runTest' in static context

    
-->
 
</div>
 
 
