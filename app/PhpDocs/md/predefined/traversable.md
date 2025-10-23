
<!-- start reference -->
<!--


 The Traversable interface
 Traversable

 


  
   Introduction
   
    Interface to detect if a class is traversable using foreach.
   
   
    Abstract base interface that cannot be implemented alone. Instead, it must
    be implemented by either IteratorAggregate or
    Iterator.
   
  


  
   Interface synopsis


   
    
     Traversable
    
   


   
    This interface has no methods, its only purpose is to be the base
    interface for all traversable classes.
   

  

  
   TODO
   
    
     
      
       TODO
       TODO
      
     
     
      
       7.4.0
       
        The Traversable interface can now be implemented
        by abstract classes. Extending classes must implement
        Iterator or
        IteratorAggregate.
       
      
     
    
   
  

  
   Notes
   
    
     Internal (built-in) classes that implement this interface can be used in
     a foreach construct and do not need to implement
     IteratorAggregate or
     Iterator.
    
   
   
    
     Prior to PHP 7.4.0, this internal engine interface couldn't be implemented
     in PHP scripts. Either IteratorAggregate
     or Iterator must be used instead.
    
   
  

 


-->
