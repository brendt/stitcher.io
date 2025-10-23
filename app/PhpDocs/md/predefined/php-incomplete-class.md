
<!-- start reference -->
<!--


 The __PHP_Incomplete_Class class
 __PHP_Incomplete_Class

 

  
   Introduction
   
    Created by unserialize
    when trying to unserialize an undefined class
    or a class that is not listed in the allowed_classes
    of unserialize's options array.
   

   
    Prior to PHP 7.2.0, using is_object on the
    __PHP_Incomplete_Class class would return false.
    As of PHP 7.2.0, true will be returned.
   
  

  
   Class synopsis

   
    
     final
     __PHP_Incomplete_Class
    
   

   
    This class has no default properties or methods.
    When created by unserialize,
    in addition to all unserialized properties and values
    the object will have a __PHP_Incomplete_Class_Name property
    which will contain the name of the unserialized class.
   
  

  
   TODO
   
    
     
      
       TODO
       TODO
      
     
     
      
       8.0.0
       
        This class is now final.
       
      
     
    
   
  

  
   Examples
   
    Created by unserialize
    

<?php

class MyClass
{
    public string $property = "myValue";
}

$myObject = new MyClass;

$foo = serialize($myObject);

// unserializes all objects into __PHP_Incomplete_Class objects
$disallowed = unserialize($foo, ["allowed_classes" => false]);

var_dump($disallowed);

// unserializes all objects into __PHP_Incomplete_Class objects except those of MyClass2 and MyClass3
$disallowed2 = unserialize($foo, ["allowed_classes" => ["MyClass2", "MyClass3"]]);

var_dump($disallowed2);

// unserializes undefined class into __PHP_Incomplete_Class object
$undefinedClass = unserialize('O:16:"MyUndefinedClass":0:{}');

var_dump($undefinedClass);

    
    The above example will output:
    


object(__PHP_Incomplete_Class)#2 (2) {
  ["__PHP_Incomplete_Class_Name"]=>
  string(7) "MyClass"
  ["property"]=>
  string(7) "myValue"
}
object(__PHP_Incomplete_Class)#3 (2) {
  ["__PHP_Incomplete_Class_Name"]=>
  string(7) "MyClass"
  ["property"]=>
  string(7) "myValue"
}
object(__PHP_Incomplete_Class)#4 (1) {
  ["__PHP_Incomplete_Class_Name"]=>
  string(16) "MyUndefinedClass"
}


    
   
  

 


-->
