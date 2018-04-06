```php
abstract class Collection implements \ArrayAccess, \Iterator
{
    private $position;

    private $array = [];

    public function __construct() {
        $this->position = 0;
    }

    public function current() {
        return $this->array[$this->position];
    }

    public function offsetGet($offset) {
        return isset($this->array[$offset]) ? $this->array[$offset] : null;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->array[] = $value;
        } else {
            $this->array[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->array[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->array[$offset]);
    }

    public function next() {
        ++$this->position;
    }

    public function key() {
        return $this->position;
    }

    public function valid() {
        return isset($this->array[$this->position]);
    }

    public function rewind() {
        $this->position = 0;
    }
}
```

A concrete implementation of the `Collection` class.

```php
class TypeCollection extends Collection 
{
    public function offsetSet($offset, $value) {
        if (!$value instanceof Type) {
            throw new \InvalidArgumentException("Value must be of type `Type`.");
        }
    
        parent::offsetSet($offset, $value);
    }
    
    public function offsetGet($offset): ?Type {
        return parent::offsetGet($offset);
    }
    
    public function current(): Type {
        return parent::current();
    }
}
```

Using the `TypeCollection` can be done like this.

```php
$collection = new TypeCollection();
$collection[] = new Type();

foreach ($collection as $item) {
    var_dump($item);
}
```
