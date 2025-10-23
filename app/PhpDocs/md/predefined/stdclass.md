
<!-- start reference -->
<!--


 The stdClass class
 stdClass

 

  
   Introduction
   
    A generic empty class with dynamic properties.
   

   
    Objects of this class can be instantiated with
    new operator or created by
    typecasting to object.
    Several PHP functions also create instances of this class, e.g.
    json_decode, mysqli_fetch_object
    or PDOStatement::fetchObject.
   

   
    Despite not implementing
    __get()/__set()
    magic methods, this class allows dynamic properties and does not require the
    #[\AllowDynamicProperties] attribute.
   

   
    This is not a base class as PHP does not have a concept of a universal base
    class. However, it is possible to create a custom class that extends from
    stdClass and as a result inherits the functionality
    of dynamic properties.
   
  

  
   Class synopsis

   
    
     stdClass
    
   

   
    This class has no methods or default properties.
   
  

  
   Examples
   
    Created as a result of typecasting to object
    

<?php
$obj = (object) array('foo' => 'bar');
var_dump($obj);

    
    The above example will output:
    

object(stdClass)#1 (1) {
  ["foo"]=>
  string(3) "bar"
}

    
   
   
    Created as a result of json_decode
    

<?php
$json = '{"foo":"bar"}';
var_dump(json_decode($json));

    
    The above example will output:
    

object(stdClass)#1 (1) {
  ["foo"]=>
  string(3) "bar"
}

    
   
   
    Declaring dynamic properties
    

<?php
$obj = new stdClass();
$obj->foo = 42;
$obj->{1} = 42;
var_dump($obj);

    
    The above example will output:
    

object(stdClass)#1 (2) {
  ["foo"]=>
  int(42)
  ["1"]=>
  int(42)
}

    
   
  

 


-->
