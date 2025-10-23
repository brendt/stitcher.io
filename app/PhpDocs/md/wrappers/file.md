
<!-- start refentry -->
<!--

 
  file://
  Accessing local filesystem
 

 
  Description
  
   file:// is the default wrapper used with PHP and
   represents the local filesystem.
   When a relative path is specified (a path which does not begin with
   /, \, \\, or a
   Windows drive letter) the path provided will be applied against the current
   working directory. In many cases this is the directory in which the script
   resides unless it has been changed. Using the CLI
   SAPI, this defaults to the directory from which the
   script was called.
  
  
   With some functions, such as fopen and
   file_get_contents, include_path
   may be optionally searched for relative paths as well.
  
 

  
  Usage
  
   /path/to/file.ext
   relative/path/to/file.ext
   fileInCwd.ext
   C:/path/to/winfile.ext
   C:\path\to\winfile.ext
   \\smbserver\share\path\to\winfile.ext
   file:///path/to/file.ext
  
  

 
  Options
  
   
    Wrapper Summary
    
     
      
       Attribute
       Supported
      
     
     
      
       Restricted by allow_url_fopen
       No
      
      
       Allows Reading
       Yes
      
      
       Allows Writing
       Yes
      
      
       Allows Appending
       Yes
      
      
       Allows Simultaneous Reading and Writing
       Yes
      
      
       Supports stat
       Yes
      
      
       Supports unlink
       Yes
      
      
       Supports rename
       Yes
      
      
       Supports mkdir
       Yes
      
      
       Supports rmdir
       Yes
      
     
    
   
  
  


-->
