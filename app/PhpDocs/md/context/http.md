
<!-- start refentry -->
<!--

 
  HTTP context options
  HTTP context option listing
 

 
  Description
  
   Context options for http:// and https://
   transports.
  
 

 
  Options
  
   
    
     
      method
      string
     
     
      
       GET, POST, or
       any other HTTP method supported by the remote server.
      
      
       Defaults to GET.
      
     
    
    
     
      header
      array or string
     
     
      
       Additional headers to be sent during request. Values
       in this option will override other values (such as
       User-agent:, Host:,
       and Authentication:),
       even when following Location: redirects.
       Thus it is not recommended to set a Host: header,
       if follow_location is enabled.
      
      
       String value should be Key: value pairs delimited by
       \r\n, e.g.
       "Content-Type: application/json\r\nConnection: close".
       Array value should be a list of Key: value pairs, e.g.
       ["Content-Type: application/json", "Connection: close"].
      
     
    
    
     
      user_agent
      string
     
     
      
       Value to send with User-Agent: header. This value will
       only be used if user-agent is not specified
       in the header context option above.
      
      
       By default the
       user_agent
       php.ini setting is used.
      
     
    
    
     
      content
      string
     
     
      
       Additional data to be sent after the headers. Typically used
       with POST or PUT requests.
      
     
    
    
     
      proxy
      string
     
     
      
       URI specifying address of proxy server (e.g.
       tcp://proxy.example.com:5100).
      
     
    
    
     
      request_fulluri
      bool
     
     
      
       When set to true, the entire URI will be used when
       constructing the request (e.g.
       GET http://www.example.com/path/to/file.html HTTP/1.0).
       While this is a non-standard request format, some
       proxy servers require it.
      
      
       Defaults to false.
      
     
    
    
     
      follow_location
      int
     
     
      
       Follow Location header redirects. Set to
       0 to disable.
      
      
       Defaults to 1.
      
     
    
    
     
      max_redirects
      int
     
     
      
       The max number of redirects to follow. Value 1 or
       less means that no redirects are followed.
      
      
       Defaults to 20.
      
     
    
    
     
      protocol_version
      float
     
     
      
       HTTP protocol version.
      
      
       Defaults to 1.1 as of PHP 8.0.0;
       prior to that version the default was 1.0.
      
     
    
    
     
      timeout
      float
     
     
      
       Read timeout in seconds, specified by a float
       (e.g. 10.5).
      
      
       By default the
       default_socket_timeout
       php.ini setting is used.
      
     
    
    
     
      ignore_errors
      bool
     
     
      
       Fetch the content even on failure status codes.
      
      
       Defaults to false.
      
     
    
   
  
 

 
  Examples
  
   
    Fetch a page and send POST data
    

<?php

$postdata = http_build_query(
    [
        'var1' => 'some content',
        'var2' => 'doh',
    ]
);

$opts = [
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => $postdata,
    ]
];

$context = stream_context_create($opts);

$result = file_get_contents('http://example.com/submit.php', false, $context);

?>

    
   
  
  
   
    Ignore redirects but fetch headers and content
    

<?php

$url = "http://www.example.org/header.php";

$opts = [
    'http' => [
        'method'        => 'GET',
        'max_redirects' => '0',
        'ignore_errors' => '1',
    ]
];

$context = stream_context_create($opts);
$stream = fopen($url, 'r', false, $context);

// header information as well as meta data
// about the stream
var_dump(stream_get_meta_data($stream));

// actual data at $url
var_dump(stream_get_contents($stream));
fclose($stream);
?>

    
   
  
 

 
  Notes
  
   Underlying socket stream context options
   
    Additional context options may be supported by the
    underlying transport
    For http:// streams, refer to context
    options for the tcp:// transport.  For
    https:// streams, refer to context options
    for the ssl:// transport.
   
  
  
   HTTP status line
   
    When this stream wrapper follows a redirect, the
    wrapper_data returned by
    stream_get_meta_data might not necessarily contain
    the HTTP status line that actually applies to the content data at index
    0.
   
   

array (
  'wrapper_data' =>
  array (
    0 => 'HTTP/1.0 301 Moved Permanently',
    1 => 'Cache-Control: no-cache',
    2 => 'Connection: close',
    3 => 'Location: http://example.com/foo.jpg',
    4 => 'HTTP/1.1 200 OK',
    ...

   
   
    The first request returned a 301 (permanent redirect),
    so the stream wrapper automatically followed the redirect to get a
    200 response (index = 4).
   
  
 

 
  See Also
  
   
    
    
    
   
  
 


-->
