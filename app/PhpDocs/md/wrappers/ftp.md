
<!-- start refentry -->
<!--

 
  ftp://
  ftps://
  Accessing FTP(s) URLs
 

 
  Description
  
   Allows read access to existing files and creation of new files
   via FTP.  If the server does not support passive mode ftp, the
   connection will fail.
  
  
   You can open files for either reading or writing, but not both
   simultaneously.  If the remote file already exists on the ftp
   server and you attempt to open it for writing but have not specified
   the context option overwrite, the connection
   will fail.  If you need to overwrite existing files over ftp,
   specify the overwrite option in the context
   and open the file for writing.  Alternatively, you can
   use the FTP extension.
  
  
   If you have set the from directive
   in php.ini, then this value will be sent as the anonymous FTP
   password.
  
 

  
  Usage
  
   ftp://example.com/pub/file.txt
   ftp://user:password@example.com/pub/file.txt
   ftps://example.com/pub/file.txt
   ftps://user:password@example.com/pub/file.txt
  
  

 
  Options
  
   
    Wrapper Summary
    
     
      
       Attribute
       Supported
      
     
     
      
       Restricted by allow_url_fopen
       Yes
      
      
       Allows Reading
       Yes
      
      
       Allows Writing
       Yes (new files/existing files with overwrite)
      
      
       Allows Appending
       Yes
      
      
       Allows Simultaneous Reading and Writing
       No
      
      
       Supports stat
       
        filesize, filemtime,
        filetype, file_exists,
        is_file, and is_dir
        elements only.
       
      
      
       Supports unlink
       Yes
      
      
       Supports rename
       Yes
      
      
       Supports mkdir
       Yes
      
      
       Supports rmdir
       Yes
      
     
    
   
  
  

 
  Notes
  
   
    FTPS is only supported when the openssl
    extension is enabled.
   
   
    If the server does not support SSL, then the connection falls back
    to regular unencrypted ftp.
   
  
  
   Appending
   
    Files may be appended via the ftp:// URL wrapper.
   
  
 

 
  See Also
  
   
  
 


-->
