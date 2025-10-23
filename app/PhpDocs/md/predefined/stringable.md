
<!-- start reference -->
<!--


 The Stringable interface
 Stringable

 


  
   Introduction
   
    The Stringable interface denotes a class as
    having a __toString() method.  Unlike most interfaces,
    Stringable is implicitly present on any class that
    has the magic __toString() method defined, although it
    can and should be declared explicitly.
   
   
    Its primary value is to allow functions to type check against the union
    type string|Stringable to accept either a string primitive
    or an object that can be cast to a string.
   
  


  
   Interface synopsis


   
    
     Stringable
    

    TODO
    
     
    
   


  

  
   Stringable Examples
   
    
     Basic Stringable Usage
     This uses constructor property promotion.
     

<?php
class IPv4Address implements Stringable {
    public function __construct(
        private string $oct1,
        private string $oct2,
        private string $oct3,
        private string $oct4,
    ) {}

    public function __toString(): string {
        return "$this->oct1.$this->oct2.$this->oct3.$this->oct4";
    }
}

function showStuff(string|Stringable $value) {
    // For a Stringable, this will implicitliy call __toString().
    print $value;
}

$ip = new IPv4Address('123', '234', '42', '9');

showStuff($ip);
?>

     
     TODO
     

123.234.42.9

     
    
   
  

 

 {{ language.predefined.stringable.tostring }}

-->
