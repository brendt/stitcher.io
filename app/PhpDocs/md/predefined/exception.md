
<!-- start reference -->
<!--

 Exception
 Exception
 
 
 

  
   Introduction
   
    Exception is the base class for
    all user exceptions.
   
  

 
  
   Class synopsis
 

   
    
     Exception
    

    
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
     
      The exception message
     
    
    
     code
     
      The exception code
     
    
    
     file
     
      The filename where the exception was created
     
    
    
     line
     
      The line where the exception was created
     
    
    
     previous
     
      The previously thrown exception
     
    
    
     string
     
      The string representation of the stack trace
     
    
    
     trace
     
      The stack trace as an array
     
    
   
  

 
 
 
 {{ language.predefined.exception.construct }}
 {{ language.predefined.exception.getmessage }}
 {{ language.predefined.exception.getprevious }}
 {{ language.predefined.exception.getcode }}
 {{ language.predefined.exception.getfile }}
 {{ language.predefined.exception.getline }}
 {{ language.predefined.exception.gettrace }}
 {{ language.predefined.exception.gettraceasstring }}
 {{ language.predefined.exception.tostring }}
 {{ language.predefined.exception.clone }}
 

-->
