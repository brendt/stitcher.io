
<!-- start reference -->
<!--


 The WeakReference class
 WeakReference

 


  
   Introduction
   
    Weak references allow the programmer to retain a reference to an object which does not prevent
    the object from being destroyed. They are useful for implementing cache like structures.
    If the original object has been destroyed, null will be returned
    when calling the WeakReference::get method.
    The original object will be destroyed when the
    refcount for it drops to zero;
    creating weak references does not increase the refcount of the object being referenced.
   
   
    WeakReferences cannot be serialized.
   
  


  
   Class synopsis


   
    
     final
     WeakReference
    

    TODO
    
     
    
    
     
    
   


  

  
   WeakReference Examples
   
    
     Basic WeakReference Usage
     

<?php

$obj = new stdClass();
$weakref = WeakReference::create($obj);

var_dump($weakref->get());

unset($obj);

var_dump($weakref->get());

?>

     
     TODO
     

object(stdClass)#1 (0) {
}
NULL

     
    
   
  

  
   TODO
   
    
     
      
       TODO
       TODO
      
     
     
      
       8.4.0
       
        The output of WeakReference::__debugInfo now includes
        the referenced object, or NULL if the reference is no longer
        valid.
       
      
     
    
   
  

 

 {{ language.predefined.weakreference.construct }}
 {{ language.predefined.weakreference.create }}
 {{ language.predefined.weakreference.get }}


-->
