
<!-- start reference -->
<!--

 Error
 Error
 
 
 

  
   Introduction
   
    Error is the base class for all
    internal PHP errors.
   
  

 
  
   Class synopsis
 

   
    
     Error
    

    
     implements
     Throwable
    

    {{ Properties }}
    
     protected
     string
     message
     ""
    
    
     private
     string
     string
     ""
    
    
     protected
     int
     code
    
    
     protected
     string
     file
     ""
    
    
     protected
     int
     line
    
    
     private
     array
     trace
     []
    
    
     private
     Throwablenull
     previous
     null
    

    TODO
    
     
    
    
     
    
   
 

 
  
  
    
     Properties
     
      
       message
       
        The error message
       
      
      
       code
       
        The error code
       
      
      
       file
       
        The filename where the error happened
       
      
      
       line
       
        The line where the error happened
       
      
      
       previous
       
        The previously thrown exception
       
      
      
       string
       
        The string representation of the stack trace
       
      
      
       trace
       
        The stack trace as an array
       
      
     
    
  

 

{{ language.predefined.error.construct }}
{{ language.predefined.error.getmessage }}
{{ language.predefined.error.getprevious }}
{{ language.predefined.error.getcode }}
{{ language.predefined.error.getfile }}
{{ language.predefined.error.getline }}
{{ language.predefined.error.gettrace }}
{{ language.predefined.error.gettraceasstring }}
{{ language.predefined.error.tostring }}
{{ language.predefined.error.clone }}

-->
