
<!-- start refentry -->
<!--

 
  ogg://
  Audio streams
 

 
  Description
  
   Files opened for reading via the ogg:// wrapper
   are treated as compressed audio encoded using the OGG/Vorbis codec.
   Similarly, files opened for writing or appending via the
   ogg:// wrapper are written as compressed audio data.
   stream_get_meta_data, when used on an OGG/Vorbis
   file opened for reading will return various details about the stream
   including the vendor tag, any included
   comments, the number of
   channels, the sampling rate,
   and the encoding rate range described by:
   bitrate_lower, bitrate_upper,
   bitrate_nominal, and bitrate_window.
  

  ogg:// (PECL)
  
   This wrapper is not enabled by default
   
    In order to use the ogg:// wrapper,
    the
    OGG/Vorbis
    extension available from TODO must be installed.
   
  
 

  
  Usage
  
   ogg://soundfile.ogg
   ogg:///path/to/soundfile.ogg
   ogg://http://www.example.com/path/to/soundstream.ogg
  
  

 
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
      
     
    
   
  
  
  
   
    Context options
    
     
      
       Name
       Usage
       Default
       Mode
      
     
     
      
       pcm_mode
       
        PCM encoding to apply while reading, one of:
        OGGVORBIS_PCM_U8, OGGVORBIS_PCM_S8,
        OGGVORBIS_PCM_U16_BE, OGGVORBIS_PCM_S16_BE,
        OGGVORBIS_PCM_U16_LE, and OGGVORBIS_PCM_S16_LE.
        (8 vs 16 bit, signed or unsigned, big or little endian)
       
       OGGVORBIS_PCM_S16_LE
       Read
      
      
       rate
       
        Sampling rate of input data, expressed in Hz
       
       44100
       Write/Append
      
      
       bitrate
       
        When given as an integer, the fixed bitrate at which to encode. (16000 to 131072)
        When given as a float, the variable bitrate quality to use. (-1.0 to 1.0)
       
       128000
       Write/Append
      
      
       channels
       
        The number of audio channels to encode, typically 1 (Mono), or 2 (Stereo).
        May range as high as 16.
       
       2
       Write/Append
      
      
       comments
       
        An array of string values to encode into the track header.
       
       
       Write/Append
      
     
    
   
  
  


-->
