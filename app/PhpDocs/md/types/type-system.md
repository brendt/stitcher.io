 
## Type System
 
 PHP uses a nominal type system with a strong behavioral subtyping relation. The subtyping relation is checked at compile time whereas the verification of types is dynamically checked at run time. 
 
 PHP's type system supports various atomic types that can be composed together to create more complex types. Some of these types can be written as [type declarations](language.types.declarations)]. 
 
<!-- start sect2 -->
<!--

  Atomic types
  
   Some atomic types are built-in types which are tightly integrated with the
   language and cannot be reproduced with user defined types.
  

  
   The list of base types is:
   
    
     Built-in types
     
      
       
        Scalar types:
       
       
        
         bool type
        
        
         int type
        
        
         float type
        
        
         string type
        
       
      
      
       array type
      
      
       object type
      
      
       resource type
      
      
       never type
      
      
       void type
      
      
       
        Relative class types:
        self, parent, and static
       
      
      
       
        Singleton types
       
       
        
         false
        
        
         true
        
       
      
      
       
        Unit types
       
       
        
         null
        
       
      
     
    
    
     
      User-defined types (generally referred to as class-types)
     
     
      
       Interfaces
      
      
       Classes
      
      
       Enumerations
      
     
    
    
     callable type
    
   
  

  
   Scalar types
   
    A value is considered scalar if it is of type int,
    float, string or bool.
   
  

  
   User-defined types
   
    It is possible to define custom types with
    interfaces,
    classes and
    enumerations.
    These are considered as user-defined types, or class-types.
    For example, a class called Elephant can be defined,
    then objects of type Elephant can be instantiated,
    and a function can request a parameter of type Elephant.
   
  
 
-->
 
<!-- start sect2 -->
<!--

  Composite types
  
   It is possible to combine multiple atomic types into composite types.
   PHP allows types to be combined in the following ways:
  

  
   
    
     Intersection of class-types (interfaces and class names).
    
   
   
    
     Union of types.
    
   
  

  
   Intersection types
   
    An intersection type accepts values which satisfies multiple
    class-type declarations, rather than a single one.
    Individual types which form the intersection type are joined by the
    & symbol. Therefore, an intersection type comprised
    of the types T, U, and
    V will be written as T&U&V.
   
  

  
   Union types
   
    A union type accepts values of multiple different types,
    rather than a single one.
    Individual types which form the union type are joined by the
    | symbol. Therefore, a union type comprised
    of the types T, U, and
    V will be written as T|U|V.
    If one of the types is an intersection type, it needs to be bracketed
    with parenthesis for it to written in DNF:
    T|(X&Y).
   
  
 
-->
 
<!-- start sect2 -->
<!--

  Type aliases

  
   PHP supports two type aliases: mixed and
   iterable which corresponds to the
   union type
   of object|resource|array|string|float|int|bool|null
   and Traversable|array respectively.
  

  
   
    PHP does not support user-defined type aliases.
   
  
 
-->

