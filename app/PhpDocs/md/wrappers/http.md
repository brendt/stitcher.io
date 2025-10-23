
<!-- start refentry -->
<!--

 
  http://
  https://
  Accessing HTTP(s) URLs
 

 
  Description
  
   Allows read-only access to files/resources via HTTP.
   By default, a HTTP 1.0 GET is used. A Host: header is sent with the request
   to handle name-based virtual hosts.  If you have configured
   a user_agent string using
   your php.ini file or the stream context, it will also be included
   in the request.
  
  
   The stream allows access to the body of
   the resource; the headers are stored in the
   $http_response_header variable.
  
  
   If it's important to know the URL of the resource where
   your document came from (after all redirects have been processed),
   you'll need to process the series of response headers returned by the
   stream.
  
  
   The from directive will be used for the
   From: header if set and not overwritten by the
   .
  
 

  
  Usage
  
   http://example.com
   http://example.com/file.php?var1=val1{{ amp }}var2=val2
   http://user:password@example.com
   https://example.com
   https://example.com/file.php?var1=val1{{ amp }}var2=val2
   https://user:password@example.com
  
  

 
  Options
  
   
    Wrapper Summary
    
     
      
       Attribute
       Supported
      
     
     
      
       Restricted by allow_url_fopen
       Yes
      
      
       Allows Reading
       Yes
      
      
       Allows Writing
       No
      
      
       Allows Appending
       No
      
      
       Allows Simultaneous Reading and Writing
       N/A
      
      
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
  
   Detecting which URL we ended up on after redirects
   

<?php
$url = 'http://www.example.com/redirecting_page.php';

$fp = fopen($url, 'r');

$meta_data = stream_get_meta_data($fp);
foreach ($meta_data['wrapper_data'] as $response) {

    /* Were we redirected? */
    if (strtolower(substr($response, 0, 10)) == 'location: ') {

        /* update $url with where we were redirected to */
        $url = substr($response, 10);
    }

}

?>

   
  
 

 
  Notes
  
   
    HTTPS is only supported when the openssl
    extension is enabled.
   
  
  
   HTTP connections are read-only; writing data or copying
   files to an HTTP resource is not supported.
  
  
   Sending POST and PUT requests, for example,
   can be done with the help of HTTP Contexts.
  
 

 
  See Also
  
   
   $http_response_header
   stream_get_meta_data
  
 


-->
