
<!-- start reference -->
<!--


 The Iterator interface
 Iterator

 


  
   Introduction
   
    Interface for external iterators or objects that can be iterated
    themselves internally.
   
  


  
   Interface synopsis


   
    
     Iterator
    

    
     extends
     Traversable
    

    TODO
    
     
    
   


  
  
  
   Predefined iterators
   
    PHP already provides a number of iterators for many day to day tasks.
    See SPL iterators for a list.
   
  

  
   Examples
   
    Basic usage
    
     This example demonstrates in which order methods are called when
     using foreach with an iterator.
    
    

<?php
class myIterator implements Iterator {
    private $position = 0;
    private $array = array(
        "firstelement",
        "secondelement",
        "lastelement",
    );  

    public function __construct() {
        $this->position = 0;
    }

    public function rewind(): void {
        var_dump(__METHOD__);
        $this->position = 0;
    }

    #[\ReturnTypeWillChange]
    public function current() {
        var_dump(__METHOD__);
        return $this->array[$this->position];
    }

    #[\ReturnTypeWillChange]
    public function key() {
        var_dump(__METHOD__);
        return $this->position;
    }

    public function next(): void {
        var_dump(__METHOD__);
        ++$this->position;
    }

    public function valid(): bool {
        var_dump(__METHOD__);
        return isset($this->array[$this->position]);
    }
}

$it = new myIterator;

foreach($it as $key => $value) {
    var_dump($key, $value);
    echo "\n";
}
?>

    
    TODO
    

string(18) "myIterator::rewind"
string(17) "myIterator::valid"
string(19) "myIterator::current"
string(15) "myIterator::key"
int(0)
string(12) "firstelement"

string(16) "myIterator::next"
string(17) "myIterator::valid"
string(19) "myIterator::current"
string(15) "myIterator::key"
int(1)
string(13) "secondelement"

string(16) "myIterator::next"
string(17) "myIterator::valid"
string(19) "myIterator::current"
string(15) "myIterator::key"
int(2)
string(11) "lastelement"

string(16) "myIterator::next"
string(17) "myIterator::valid"

    
   
  

  
  See Also
   See also object iteration.
  

 

 {{ language.predefined.iterator.current }}
 {{ language.predefined.iterator.key }}
 {{ language.predefined.iterator.next }}
 {{ language.predefined.iterator.rewind }}
 {{ language.predefined.iterator.valid }}


-->
