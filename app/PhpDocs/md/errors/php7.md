
 
## Errors in PHP 7
 
 PHP 7 changes how most errors are reported by PHP. Instead of reporting errors through the traditional error reporting mechanism used by PHP 5, most errors are now reported by throwing <!-- start classname -->
<!--
Error
--> exceptions. 
 
 As with normal exceptions, these <!-- start classname -->
<!--
Error
--> exceptions will bubble up until they reach the first matching [catch](language.exceptions.catch)] block. If there are no matching blocks, then any default exception handler installed with <!-- start function -->
<!--
set_exception_handler
--> will be called, and if there is no default exception handler, then the exception will be converted to a fatal error and will be handled like a traditional error. 
 
 As the <!-- start classname -->
<!--
Error
--> hierarchy does not inherit from <!-- start classname -->
<!--
Exception
-->, code that uses <!-- start code -->
<!--
catch (Exception $e) { ... }
--> blocks to handle uncaught exceptions in PHP 5 will find that these <!-- start classname -->
<!--
Error
-->s are not caught by these blocks. Either a <!-- start code -->
<!--
catch (Error $e) { ... }
--> block or a <!-- start function -->
<!--
set_exception_handler
--> handler is required. 
 
<!-- start sect2 -->
<!--

  Error hierarchy

  
   
    Throwable
    
     
      Error
      
       
        ArithmeticError
        
         
          DivisionByZeroError
         
        
       
       
        AssertionError
       
       
        CompileError
        
         
          ParseError
         
        
       
       
        TypeError
        
         
          ArgumentCountError
         
        
       
       
        ValueError
       
       
        UnhandledMatchError
       
       
        FiberError
       
       
        RequestParseBodyException
       
      
     
     
      Exception
      
       
        ...
       
      
     
    
   
  
 
-->

