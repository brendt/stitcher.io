
<!-- start refentry -->
<!--

 
  zlib://
  bzip2://
  zip://
  Compression Streams
 

 
  Description
  compress.zlib:// and compress.bzip2://

  
   zlib: works like gzopen, except that the
   stream can be used with fread and the other
   filesystem functions.  This is deprecated due
   to ambiguities with filenames containing ':' characters; use
   compress.zlib:// instead.
  

  
   compress.zlib:// and
   compress.bzip2:// are equivalent to
   gzopen and bzopen
   respectively, and operate even on systems that do not support
   fopencookie.
  

  
   ZIP extension registers zip: wrapper. As of
   PHP 7.2.0 and libzip 1.2.0+, support for the passwords for encrypted archives were added, allowing
   passwords to be supplied by stream contexts. Passwords can be set using the 'password'
   stream context option.
  
 

  
  Usage
  
   compress.zlib://file.gz
   compress.bzip2://file.bz2
   zip://archive.zip#dir/file.txt
  
  

 
  Options
  
   
    Wrapper Summary
    
     
      
       Attribute
       Supported
      
     
     
      
       Restricted by allow_url_fopen
       No
      
      
       Allows Reading
       Yes
      
      
       Allows Writing
       Yes (except zip://)
      
      
       Allows Appending
       Yes (except zip://)
      
      
       Allows Simultaneous Reading and Writing
       No
      
      
       Supports stat
       
        No, use the normal file:// wrapper
        to stat compressed files.
       
      
      
       Supports unlink
       
        No, use the normal file:// wrapper
        to unlink compressed files.
       
      
      
       Supports rename
       No
      
      
       Supports mkdir
       No
      
      
       Supports rmdir
       No
      
     
    
   
  
  

 
  See Also
  
   
  
 

-->
