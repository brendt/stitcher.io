
<!-- start refentry -->
<!--

 
  ssh2://
  Secure Shell 2
 

 
  Description
  
   ssh2.shell://
   ssh2.exec://
   ssh2.tunnel://
   ssh2.sftp://
   ssh2.scp://
   (PECL)
  

  
   This wrapper is not enabled by default
   
    In order to use the ssh2.*:// wrappers,
    the
    SSH2
    extension available from TODO must be installed.
   
  

  
   In addition to accepting traditional URI login details, the ssh2 wrappers
   will also reuse open connections by passing the connection resource in the
   host portion of the URL.
  
 

  
  Usage
  
   ssh2.shell://user:pass@example.com:22/xterm
   ssh2.exec://user:pass@example.com:22/usr/local/bin/somecmd
   ssh2.tunnel://user:pass@example.com:22/192.168.0.1:14
   ssh2.sftp://user:pass@example.com:22/path/to/filename
  
  

 
  Options
  
   
    Wrapper Summary
    
     
      
       Attribute
       ssh2.shell
       ssh2.exec
       ssh2.tunnel
       ssh2.sftp
       ssh2.scp
      
     
     
      
       Restricted by allow_url_fopen
       Yes
       Yes
       Yes
       Yes
       Yes
      
      
       Allows Reading
       Yes
       Yes
       Yes
       Yes
       Yes
      
      
       Allows Writing
       Yes
       Yes
       Yes
       Yes
       No
      
      
       Allows Appending
       No
       No
       No
       Yes (When supported by server)
       No
      
      
       Allows Simultaneous Reading and Writing
       Yes
       Yes
       Yes
       Yes
       No
      
      
       Supports stat
       No
       No
       No
       Yes
       No
      
      
       Supports unlink
       No
       No
       No
       Yes
       No
      
      
       Supports rename
       No
       No
       No
       Yes
       No
      
      
       Supports mkdir
       No
       No
       No
       Yes
       No
      
      
       Supports rmdir
       No
       No
       No
       Yes
       No
      
     
    
   
  


  
  
   
    Context options
    
     
      
       Name
       Usage
       Default
      
     
     
      
       session
       Preconnected ssh2 resource to be reused
       
      
      
       sftp
       Preallocated sftp resource to be reused
       
      
      
       methods
       Key exchange, hostkey, cipher, compression, and MAC methods to use
       
      
      
       callbacks
       
       
      
      
       username
       Username to connect as
       
      
      
       password
       Password to use with password authentication
       
      
      
       pubkey_file
       Name of public key file to use for authentication
       
      
      
       privkey_file
       Name of private key file to use for authentication
       
      
      
       env
       Associate array of environment variables to set
       
      
      
       term
       Terminal emulation type to request when allocating a pty
       
      
      
       term_width
       Width of terminal requested when allocating a pty
       
      
      
       term_height
       Height of terminal requested when allocating a pty
       
      
      
       term_units
       Units to use with term_width and term_height
       SSH2_TERM_UNIT_CHARS
      
     
    
   
  
  

 
  Examples
  
   Opening a stream from an active connection
   

<?php
$session = ssh2_connect('example.com', 22);
ssh2_auth_pubkey_file($session, 'username', '/home/username/.ssh/id_rsa.pub',
                                            '/home/username/.ssh/id_rsa', 'secret');
$stream = fopen("ssh2.tunnel://$session/remote.example.com:1234", 'r');
?>

   
  
  
   This $session variable must be kept available!
   
    In order to use the ssh2.*://$session wrappers,
    the $session resource variable must be kept.
    The code below will not have the desired effect:
   
   

<?php
$session = ssh2_connect('example.com', 22);
ssh2_auth_pubkey_file($session, 'username', '/home/username/.ssh/id_rsa.pub',
                                            '/home/username/.ssh/id_rsa', 'secret');
$connection_string = "ssh2.sftp://$session/";
unset($session);
$stream = fopen($connection_string . "path/to/file", 'r');
?>

   
   
    unset() closes the session, because $connection_string does not
    hold a reference to the $session variable, just a string cast
    derived from it. This also happens when the unset is implicit
    because of leaving scope (like in a function).
   
  

 


-->
