
<!-- start reference -->
<!--

 ErrorException
 ErrorException
 
 
 

  
   Introduction
   
    An Error Exception.
   
  

 
  
   Class synopsis
 

   
    
     ErrorException
    

    
     extends
     Exception
    

    {{ Properties }}
    
     protected
     int
     severity
     E_ERROR
    

    {{ InheritedProperties }}
    
     
    

    TODO
    
     
    
    
     
    

    {{ InheritedMethods }}
    
     
    
   

 
  
 

  
   Properties
   
    
     severity
     
      The severity of the exception
     
    
   
  


  
   Examples
   
    
     Use set_error_handler to change error messages into ErrorException
     
 
<?php

set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting.
        return;
    }

    if ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED) {
        // Do not throw an Exception for deprecation warnings as new or unexpected
        // deprecations would break the application.
        return;
    }

    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Unserializing broken data triggers a warning which will be turned into an
// ErrorException by the error handler.
unserialize('broken data');

?>

     
     TODO
     

Fatal error: Uncaught ErrorException: unserialize(): Error at offset 0 of 11 bytes in test.php:16
Stack trace:
#0 [internal function]: {closure}(2, 'unserialize(): ...', 'test.php', 16)
#1 test.php(16): unserialize('broken data')
#2 {main}
  thrown in test.php on line 16

     
    
   
  
 
 
 
 {{ language.predefined.errorexception.construct }}
 {{ language.predefined.errorexception.getseverity }}
 

-->
