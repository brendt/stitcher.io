
 
## Class Abstraction
 
 PHP has abstract classes, methods, and properties. Classes defined as abstract cannot be instantiated, and any class that contains at least one abstract method or property must also be abstract. Methods defined as abstract simply declare the method's signature and whether it is public or protected; they cannot define the implementation. Properties defined as abstract may declare a requirement for `get` or `set` behavior, and may provide an implementation for one, but not both, operations. 
 
 When inheriting from an abstract class, all methods marked abstract in the parent's class declaration must be defined by the child class, and follow the usual [inheritance](language.oop5.inheritance)] and [signature compatibility](language.oop.lsp)] rules. 
 
 As of PHP 8.4, an abstract class may declare an abstract property, either public or protected. A protected abstract property may be satisfied by a property that is readable/writeable from either protected or public scope. 
 
 An abstract property may be satisfied either by a standard property or by a property with defined hooks, corresponding to the required operation. 
 
<div class="example">
     
## Abstract method example
 

```php
<?php

abstract class AbstractClass
{
    // Force extending class to define this method
    abstract protected function getValue();
    abstract protected function prefixValue($prefix);

    // Common method
    public function printOut()
    {
        print $this->getValue() . "\n";
    }
}

class ConcreteClass1 extends AbstractClass
{
    protected function getValue()
    {
        return "ConcreteClass1";
    }

    public function prefixValue($prefix)
    {
        return "{$prefix}ConcreteClass1";
    }
}

class ConcreteClass2 extends AbstractClass
{
    public function getValue()
    {
        return "ConcreteClass2";
    }

    public function prefixValue($prefix)
    {
        return "{$prefix}ConcreteClass2";
    }
}

$class1 = new ConcreteClass1();
$class1->printOut();
echo $class1->prefixValue('FOO_'), "\n";

$class2 = new ConcreteClass2();
$class2->printOut();
echo $class2->prefixValue('FOO_'), "\n";

?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


ConcreteClass1
FOO_ConcreteClass1
ConcreteClass2
FOO_ConcreteClass2

   
-->
 
</div>
 
<div class="example">
     
## Abstract method example
 

```php
<?php

abstract class AbstractClass
{
    // An abstract method only needs to define the required arguments
    abstract protected function prefixName($name);
}

class ConcreteClass extends AbstractClass
{
    // A child class may define optional parameters which are not present in the parent's signature
    public function prefixName($name, $separator = ".")
    {
        if ($name == "Pacman") {
            $prefix = "Mr";
        } elseif ($name == "Pacwoman") {
            $prefix = "Mrs";
        } else {
            $prefix = "";
        }

        return "{$prefix}{$separator} {$name}";
    }
}

$class = new ConcreteClass();
echo $class->prefixName("Pacman"), "\n";
echo $class->prefixName("Pacwoman"), "\n";

?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


Mr. Pacman
Mrs. Pacwoman

   
-->
 
</div>
 
<div class="example">
     
## Abstract property example
 

```php
<?php

abstract class A
{
    // Extending classes must have a publicly-gettable property
    abstract public string $readable {
        get;
    }

    // Extending classes must have a protected- or public-writeable property
    abstract protected string $writeable {
        set;
    }

    // Extending classes must have a protected or public symmetric property
    abstract protected string $both {
        get;
        set;
    }
}

class C extends A
{
    // This satisfies the requirement and also makes it settable, which is valid
    public string $readable;

    // This would NOT satisfy the requirement, as it is not publicly readable
    protected string $readable;

    // This satisfies the requirement exactly, so is sufficient.
    // It may only be written to, and only from protected scope
    protected string $writeable {
        set => $value;
    }

    // This expands the visibility from protected to public, which is fine
    public string $both;
}

?>
```
 
</div>
 
 An abstract property on an abstract class may provide implementations for any hook, but must have either get or set declared but not defined (as in the example above). 
 
<div class="example">
     
## Abstract property with hooks example
 

```php
<?php

abstract class A
{
    // This provides a default (but overridable) set implementation,
    // and requires child classes to provide a get implementation
    abstract public string $foo {
        get;

        set {
            $this->foo = $value;
        }
    }
}

?>
```
 
</div>
 
