
<!-- start reference -->
<!--


 The WeakMap class
 WeakMap

 

  
  
   Introduction
   
    A WeakMap is map (or dictionary) that accepts objects as keys.  However, unlike the
    otherwise similar SplObjectStorage, an object in a key of WeakMap
    does not contribute toward the object's reference count.  That is, if at any point the only remaining reference
    to an object is the key of a WeakMap, the object will be garbage collected and removed
    from the WeakMap.  Its primary use case is for building caches of data derived from
    an object that do not need to live longer than the object.
   
   
    WeakMap implements ArrayAccess,
    Traversable (via IteratorAggregate),
    and Countable, so in most cases it can be used in the same fashion as an associative array.
   
  
  

  
   Class synopsis

   
   
    
     final
     WeakMap
    

    
     implements
     ArrayAccess
    

    
     Countable
    

    
     IteratorAggregate
    

    TODO
    
     
    
   
   

  
  
  
   Examples
   
    
     Weakmap usage example
     
      
<?php
$wm = new WeakMap();

$o = new stdClass;

class A {
    public function __destruct() {
        echo "Dead!\n";
    }
}

$wm[$o] = new A;

var_dump(count($wm));
echo "Unsetting...\n";
unset($o);
echo "Done\n";
var_dump(count($wm));

     
     The above example will output:
     
      
int(1)
Unsetting...
Dead!
Done
int(0)

     
    
   
  
  

 

 {{ language.predefined.weakmap.count }}
 {{ language.predefined.weakmap.getiterator }}
 {{ language.predefined.weakmap.offsetexists }}
 {{ language.predefined.weakmap.offsetget }}
 {{ language.predefined.weakmap.offsetset }}
 {{ language.predefined.weakmap.offsetunset }}


-->
