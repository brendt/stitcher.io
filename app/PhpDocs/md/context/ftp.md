
<!-- start refentry -->
<!--

 
  FTP context options
  FTP context option listing
 

 
  Description
  
   Context options for ftp:// and ftps://
   transports.
  
 

 
  Options
  
   
    
     
      overwrite
      bool
     
     
      
       Allow overwriting of already existing files on remote server.
       Applies to write mode (uploading) only.
      
      
       Defaults to false.
      
     
    
    
     
      resume_pos
      int
     
     
      
       File offset at which to begin transfer. Applies to read mode (downloading) only.
      
      
       Defaults to 0 (Beginning of File).
      
     
    
    
     
      proxy
      string
     
     
      
       Proxy FTP request via http proxy server. Applies to file read
       operations only. Ex: tcp://squid.example.com:8000.
      
     
    
   
  
 

 
  Notes
  
   Underlying socket stream context options
   
    Additional context options may be supported by the
    underlying transport.
    For ftp:// streams, refer to context
    options for the tcp:// transport.  For
    ftps:// streams, refer to context options
    for the ssl:// transport.
   
  
 

 
  See Also
  
   
    
    
    
   
  
 


-->
