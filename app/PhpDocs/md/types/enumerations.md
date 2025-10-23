
 
## Enumerations
 

 
<!-- start sect2 -->
<!--

  Basic Enumerations

  
   Enumerations are a restricting layer on top of classes and class constants,
   intended to provide a way to define a closed set of possible values for a type.
  

  
   

<?php
enum Suit
{
    case Hearts;
    case Diamonds;
    case Clubs;
    case Spades;
}

function do_stuff(Suit $s)
{
    // ...
}

do_stuff(Suit::Spades);
?>

   
  

  
   For a full discussion, see the
   Enumerations chapter.
  

 
-->
 
<!-- start sect2 -->
<!--

  Casting
  
  
   If an enum is converted to an object, it is not
   modified. If an enum is converted to an array,
   an array with a single name key (for Pure enums) or
   an array with both name and value keys
   (for Backed enums) is created.  All other cast types will result in an error.
  
 
-->

