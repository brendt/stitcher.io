
<!-- start refentry -->
<!--

 
  data://
  Data (RFC 2397)
 

 
  Description
  
   
   The data: (RFC
   2397) stream wrapper.
  
 

  
  Usage
  
   data://text/plain;base64,
  
  

 
  Options
  
   
    Wrapper Summary
    
     
      
       Attribute
       Supported
      
     
     
      
       Restricted by allow_url_fopen
       Yes
      
      
       Restricted by allow_url_include
       Yes
      
      
       Allows Reading
       Yes
      
      
       Allows Writing
       No
      
      
       Allows Appending
       No
      
      
       Allows Simultaneous Reading and Writing
       No
      
      
       Supports stat
       No
      
      
       Supports unlink
       No
      
      
       Supports rename
       No
      
      
       Supports mkdir
       No
      
      
       Supports rmdir
       No
      
     
    
   
  
  

 
  Examples
  
   Print data:// contents
   

<?php
// prints "I love PHP"
echo file_get_contents('data://text/plain;base64,SSBsb3ZlIFBIUAo=');
?>

   
  

  
   Fetch the media type
   

<?php
$fp   = fopen('data://text/plain;base64,', 'r');
$meta = stream_get_meta_data($fp);

// prints "text/plain"
echo $meta['mediatype'];
?>

   
  
 


-->
