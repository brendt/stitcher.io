
 
## Magic Methods
 
 Magic methods are special methods which override PHP's default's action when certain actions are performed on an object. 
 
<!-- start caution -->
<!--

   
    All methods names starting with __ are reserved by PHP.
    Therefore, it is not recommended to use such method names unless overriding
    PHP's behavior.
   
  
-->
 
 The following method names are considered magical:  [__construct()](object.construct)], [__destruct()](object.destruct)], [__call()](object.call)], [__callStatic()](object.callstatic)], [__get()](object.get)], [__set()](object.set)], [__isset()](object.isset)], [__unset()](object.unset)], [__sleep()](object.sleep)], [__wakeup()](object.wakeup)], [__serialize()](object.serialize)], [__unserialize()](object.unserialize)], [__toString()](object.tostring)], [__invoke()](object.invoke)], [__set_state()](object.set-state)], [__clone()](object.clone)], and [__debugInfo()](object.debuginfo)]. 
 
<div class="warning">
     

 
 All magic methods, with the exception of __construct(), __destruct(), and __clone(), must be declared as public, otherwise an E_WARNING is emitted. Prior to PHP 8.0.0, no diagnostic was emitted for the magic methods __sleep(), __wakeup(), __serialize(), __unserialize(), and __set_state(). 
 
</div>
 
<div class="warning">
     
 If type declarations are used in the definition of a magic method, they must be identical to the signature described in this document. Otherwise, a fatal error is emitted. Prior to PHP 8.0.0, no diagnostic was emitted. However, [__construct()](object.construct)] and [__destruct()](object.destruct)] must not declare a return type; otherwise a fatal error is emitted. 
 
</div>
 
 
## 
    __sleep() and
    __wakeup()
   
 
<!-- start methodsynopsis -->
<!--

    public array__sleep
    
   
-->
 
<!-- start methodsynopsis -->
<!--

    public void__wakeup
    
   
-->
 
 `serialize` checks if the class has a function with the magic name [__sleep()](object.sleep)]. If so, that function is executed prior to any serialization. It can clean up the object and is supposed to return an array with the names of all variables of that object that should be serialized. If the method doesn't return anything then `null` is serialized and `E_NOTICE` is issued. 
 
<div class="note">
     
 It is not possible for [__sleep()](object.sleep)] to return names of private properties in parent classes. Doing this will result in an `E_NOTICE` level error. Use [__serialize()](object.serialize)] instead. 
 
</div>
 
<div class="note">
     
 As of PHP 8.0.0, returning a value which is not an array from [__sleep()](object.sleep)] generates a warning. Previously, it generated a notice. 
 
</div>
 
 The intended use of [__sleep()](object.sleep)] is to commit pending data or perform similar cleanup tasks. Also, the function is useful if a very large object doesn't need to be saved completely. 
 
 Conversely, `unserialize` checks for the presence of a function with the magic name [__wakeup()](object.wakeup)]. If present, this function can reconstruct any resources that the object may have. 
 
 The intended use of [__wakeup()](object.wakeup)] is to reestablish any database connections that may have been lost during serialization and perform other reinitialization tasks. 
 
<div class="example">
     
## Sleep and wakeup
 

```php
<?php
class Connection
{
    protected $link;
    private $dsn, $username, $password;
    
    public function __construct($dsn, $username, $password)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->connect();
    }
    
    private function connect()
    {
        $this->link = new PDO($this->dsn, $this->username, $this->password);
    }
    
    public function __sleep()
    {
        return array('dsn', 'username', 'password');
    }
    
    public function __wakeup()
    {
        $this->connect();
    }
}?>
```
 
</div>
 
 
 
## 
    __serialize() and
    __unserialize()
   
 
<!-- start methodsynopsis -->
<!--

    public array__serialize
    
   
-->
 
<!-- start methodsynopsis -->
<!--

    public void__unserialize
    arraydata
   
-->
 
 `serialize` checks if the class has a function with the magic name [__serialize()](object.serialize)]. If so, that function is executed prior to any serialization. It must construct and return an associative array of key/value pairs that represent the serialized form of the object. If no array is returned a `TypeError` will be thrown. 
 
<div class="note">
     
 If both [__serialize()](object.serialize)] and [__sleep()](object.sleep)] are defined in the same object, only [__serialize()](object.serialize)] will be called. [__sleep()](object.sleep)] will be ignored. If the object implements the [Serializable](class.serializable)] interface, the interface's `serialize()` method will be ignored and [__serialize()](object.serialize)] used instead. 
 
</div>
 
 The intended use of [__serialize()](object.serialize)] is to define a serialization-friendly arbitrary representation of the object. Elements of the array may correspond to properties of the object but that is not required. 
 
 Conversely, `unserialize` checks for the presence of a function with the magic name [__unserialize()](object.unserialize)]. If present, this function will be passed the restored array that was returned from [__serialize()](object.serialize)]. It may then restore the properties of the object from that array as appropriate. 
 
<div class="note">
     
 If both [__unserialize()](object.unserialize)] and [__wakeup()](object.wakeup)] are defined in the same object, only [__unserialize()](object.unserialize)] will be called. [__wakeup()](object.wakeup)] will be ignored. 
 
</div>
 
<div class="note">
     
 This feature is available as of PHP 7.4.0. 
 
</div>
 
<div class="example">
     
## Serialize and unserialize
 

```php
<?php
class Connection
{
    protected $link;
    private $dsn, $username, $password;

    public function __construct($dsn, $username, $password)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->connect();
    }

    private function connect()
    {
        $this->link = new PDO($this->dsn, $this->username, $this->password);
    }

    public function __serialize(): array
    {
        return [
          'dsn' => $this->dsn,
          'user' => $this->username,
          'pass' => $this->password,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->dsn = $data['dsn'];
        $this->username = $data['user'];
        $this->password = $data['pass'];

        $this->connect();
    }
}?>
```
 
</div>
 
 
 
## __toString()
 
<!-- start methodsynopsis -->
<!--

    public string__toString
    
   
-->
 
 The [__toString()](object.tostring)] method allows a class to decide how it will react when it is treated like a string. For example, what `echo $obj;` will print. 
 
<div class="warning">
     
 As of PHP 8.0.0, the return value follows standard PHP type semantics, meaning it will be coerced into a `string` if possible and if [strict typing](language.types.declarations.strict)] is disabled. 
 
 A <!-- start interfacename -->
<!--
Stringable
--> object will <!-- start emphasis -->
<!--
not
--> be accepted by a `string` type declaration if [strict typing](language.types.declarations.strict)] is enabled. If such behaviour is wanted the type declaration must accept <!-- start interfacename -->
<!--
Stringable
--> and `string` via a union type. 
 
 As of PHP 8.0.0, any class that contains a [__toString()](object.tostring)] method will also implicitly implement the <!-- start interfacename -->
<!--
Stringable
--> interface, and will thus pass type checks for that interface. Explicitly implementing the interface anyway is recommended. 
 
 In PHP 7.4, the returned value <!-- start emphasis -->
<!--
must
--> be a `string`, otherwise an `Error` is thrown. 
 
 Prior to PHP 7.4.0, the returned value <!-- start emphasis -->
<!--
must
--> be a `string`, otherwise a fatal `E_RECOVERABLE_ERROR` is emitted. 
 
</div>
 
<div class="warning">
     
 It was not possible to throw an exception from within a __toString() method prior to PHP 7.4.0. Doing so will result in a fatal error. 
 
</div>
 
<div class="example">
     
## Simple example
 

```php
<?php
// Declare a simple class
class TestClass
{
    public $foo;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }

    public function __toString()
    {
        return $this->foo;
    }
}

$class = new TestClass('Hello');
echo $class;
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


Hello

    
-->
 
</div>
 
 
 
## __invoke()
 
<!-- start methodsynopsis -->
<!--

    mixed__invoke
    values
   
-->
 
 The [__invoke()](object.invoke)] method is called when a script tries to call an object as a function. 
 
<div class="example">
     
## Using __invoke()
 

```php
<?php
class CallableClass
{
    public function __invoke($x)
    {
        var_dump($x);
    }
}
$obj = new CallableClass;
$obj(5);
var_dump(is_callable($obj));
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


int(5)
bool(true)

    
-->
 
</div>
 
<div class="example">
     
## Using __invoke()
 

```php
<?php
class Sort
{
    private $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function __invoke(array $a, array $b): int
    {
        return $a[$this->key] <=> $b[$this->key];
    }
}

$customers = [
    ['id' => 1, 'first_name' => 'John', 'last_name' => 'Do'],
    ['id' => 3, 'first_name' => 'Alice', 'last_name' => 'Gustav'],
    ['id' => 2, 'first_name' => 'Bob', 'last_name' => 'Filipe']
];

// sort customers by first name
usort($customers, new Sort('first_name'));
print_r($customers);

// sort customers by last name
usort($customers, new Sort('last_name'));
print_r($customers);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


Array
(
    [0] => Array
        (
            [id] => 3
            [first_name] => Alice
            [last_name] => Gustav
        )

    [1] => Array
        (
            [id] => 2
            [first_name] => Bob
            [last_name] => Filipe
        )

    [2] => Array
        (
            [id] => 1
            [first_name] => John
            [last_name] => Do
        )

)
Array
(
    [0] => Array
        (
            [id] => 1
            [first_name] => John
            [last_name] => Do
        )

    [1] => Array
        (
            [id] => 2
            [first_name] => Bob
            [last_name] => Filipe
        )

    [2] => Array
        (
            [id] => 3
            [first_name] => Alice
            [last_name] => Gustav
        )

)

    
-->
 
</div>
 
 
 
## __set_state()
 
<!-- start methodsynopsis -->
<!--

    static object__set_state
    arrayproperties
   
-->
 
 This [static](language.oop5.static)] method is called for classes exported by `var_export`. 
 
 The only parameter of this method is an array containing exported properties in the form `['property' => value, ...]`. 
 
<div class="example">
     
## Using __set_state()
 

```php
<?php

class A
{
    public $var1;
    public $var2;

    public static function __set_state($an_array)
    {
        $obj = new A;
        $obj->var1 = $an_array['var1'];
        $obj->var2 = $an_array['var2'];
        return $obj;
    }
}

$a = new A;
$a->var1 = 5;
$a->var2 = 'foo';

$b = var_export($a, true);
var_dump($b);
eval('$c = ' . $b . ';');
var_dump($c);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


string(60) "A::__set_state(array(
   'var1' => 5,
   'var2' => 'foo',
))"
object(A)#2 (2) {
  ["var1"]=>
  int(5)
  ["var2"]=>
  string(3) "foo"
}

    
-->
 
</div>
 
<div class="note">
     
 When exporting an object, var_export does not check whether __set_state() is implemented by the object's class, so re-importing objects will result in an Error exception, if __set_state() is not implemented. Particularly, this affects some internal classes. 
 
 It is the responsibility of the programmer to verify that only objects will be re-imported, whose class implements __set_state(). 
 
</div>
 
 
 
## __debugInfo()
 
<!-- start methodsynopsis -->
<!--

    array__debugInfo
    
   
-->
 
 This method is called by `var_dump` when dumping an object to get the properties that should be shown. If the method isn't defined on an object, then all public, protected and private properties will be shown. 
 
<div class="example">
     
## Using __debugInfo()
 

```php
<?php
class C {
    private $prop;

    public function __construct($val) {
        $this->prop = $val;
    }

    public function __debugInfo() {
        return [
            'propSquared' => $this->prop ** 2,
        ];
    }
}

var_dump(new C(42));
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


object(C)#1 (1) {
  ["propSquared"]=>
  int(1764)
}

    
-->
 
</div>
 
 
