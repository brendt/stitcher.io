
 
## Callbacks / Callables
 
 Callbacks can be denoted by the `callable` type declaration. 
 
 Some functions like `call_user_func` or `usort` accept user-defined callback functions as a parameter. Callback functions can not only be simple functions, but also `object` methods, including static class methods. 
 
 
## Passing
 
 A PHP function is passed by either its name as a `string` or by a [first-class callable](functions.first_class_callable_syntax)]. Any built-in or user-defined function can be used, except language constructs such as: `array`, `echo`, `empty`, `eval`, `exit`, `isset`, `list`, `print` or `unset`. 
 
 A method of an instantiated `object` is passed as an `array` containing an `object` at index 0 and the method name at index 1. Accessing protected and private methods from within a class is allowed. 
 
 Static class methods can also be passed without instantiating an `object` of that class by either, passing the class name instead of an `object` at index 0, or passing `'ClassName::methodName'`. 
 
 Apart from common user-defined function, [anonymous functions](functions.anonymous)] and [arrow functions](functions.arrow)] can also be passed to a callback parameter. 
 
<div class="note">
     
 As of PHP 8.1.0, anonymous functions can also be created using the [first class callable syntax](functions.first_class_callable_syntax)]. 
 
</div>
 
 Generally, any object implementing [__invoke()](object.invoke)] can also be passed to a callback parameter. 
 
 <div class="example">
     
## 
     Callback function examples
    
 

```php
<?php

// An example callback function
function my_callback_function() {
    echo 'hello world!', PHP_EOL;
}

// An example callback method
class MyClass {
    static function myCallbackMethod() {
        echo 'Hello World!', PHP_EOL;
    }
}

// Type 1: Simple callback
call_user_func('my_callback_function');

// Type 2: Static class method call
call_user_func(array('MyClass', 'myCallbackMethod'));

// Type 3: Object method call
$obj = new MyClass();
call_user_func(array($obj, 'myCallbackMethod'));

// Type 4: Static class method call
call_user_func('MyClass::myCallbackMethod');

// Type 5: Relative static class method call
class A {
    public static function who() {
        echo 'A', PHP_EOL;
    }
}

class B extends A {
    public static function who() {
        echo 'B', PHP_EOL;
    }
}

call_user_func(array('B', 'parent::who')); // A, deprecated as of PHP 8.2.0

// Type 6: Objects implementing __invoke can be used as callables
class C {
    public function __invoke($name) {
        echo 'Hello ', $name, PHP_EOL;
    }
}

$c = new C();
call_user_func($c, 'PHP!');
?>
```
 
</div> 
 
 <div class="example">
     
## 
     Callback example using a Closure
    
 

```php
<?php
// Our closure
$double = function($a) {
    return $a * 2;
};

// This is our range of numbers
$numbers = range(1, 5);

// Use the closure as a callback here to
// double the size of each element in our
// range
$new_numbers = array_map($double, $numbers);

print implode(' ', $new_numbers);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


2 4 6 8 10

    
-->
 
</div> 
 {{ note.func-callback-exceptions }} 

