
<!-- start refentry -->
<!--

 
  Zip context options
  Zip context option listing
 

 
  Description
  
   Zip context options are available for zip wrappers.
  
 

 
  Options
  
   
    
     password
     
      
       Used to specify password used for encrypted archive.
      
     
    
   
  
 

 
  TODO
  
   
    
     
      
       TODO
       TODO
      
     
     
      
       7.2.0, PECL zip 1.14.0
       
        Added password.
       
      
     
    
   
  
 

 
  Examples
  
   
    Basic password usage example
    

<?php
// Read encrypted archive
$opts = array(
    'zip' => array(
        'password' => 'secret',
    ),
);
// create the context...
$context = stream_context_create($opts);

// ...and use it to fetch the data
echo file_get_contents('zip://test.zip#test.txt', false, $context);

?>

    
   
  
 


-->
