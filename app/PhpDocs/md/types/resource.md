
 
## Resources
 
 A <!-- start type -->
<!--
resource
--> is a special variable, holding a reference to an external resource. Resources are created and used by special functions. See the [appendix](resource)] for a listing of all these functions and the corresponding <!-- start type -->
<!--
resource
--> types. 
 
 See also the <!-- start function -->
<!--
get_resource_type
--> function. 
 
<!-- start sect2 -->
<!--

  Converting to resource
  
  
   As resource variables hold special handles to opened files,
   database connections, image canvas areas and the like, converting to a
   resource makes no sense.
  
 
-->
 
<!-- start sect2 -->
<!--

  Freeing resources
  
  
   Thanks to the reference-counting system being part of Zend Engine,
   a resource with no more references to it is detected
   automatically, and it is freed by the garbage collector. For this reason, it
   is rarely necessary to free the memory manually.
  

  
   
    Persistent database links are an exception to this rule. They are
    not destroyed by the garbage collector. See the
    persistent
    connections section for more information.
   
  
  
 
-->

