
 
## Object Interfaces
 
 Object interfaces allow you to create code which specifies which methods and properties a class must implement, without having to define how these methods or properties are implemented. Interfaces share a namespace with classes, traits, and enumerations, so they may not use the same name. 
 
 Interfaces are defined in the same way as a class, but with the `interface` keyword replacing the `class` keyword and without any of the methods having their contents defined. 
 
 All methods declared in an interface must be public; this is the nature of an interface. 
 
 In practice, interfaces serve two complementary purposes: 
 
<!-- start simplelist -->
<!--

   
    To allow developers to create objects of different classes that may be used interchangeably
    because they implement the same interface or interfaces.  A common example is multiple database access services,
    multiple payment gateways, or different caching strategies.  Different implementations may
    be swapped out without requiring any changes to the code that uses them.
   
   
    To allow a function or method to accept and operate on a parameter that conforms to an
    interface, while not caring what else the object may do or how it is implemented. These interfaces
    are often named like Iterable, Cacheable, Renderable,
    or so on to describe the significance of the behavior.
   
  
-->
 
 Interfaces may define [magic methods](language.oop5.magic)] to require implementing classes to implement those methods. 
 
<div class="note">
     
 Although they are supported, including [constructors](language.oop5.decon.constructor)] in interfaces is strongly discouraged. Doing so significantly reduces the flexibility of the object implementing the interface. Additionally, constructors are not enforced by inheritance rules, which can cause inconsistent and unexpected behavior. 
 
</div>
 
 
## implements
 
 To implement an interface, the `implements` operator is used. All methods in the interface must be implemented within a class; failure to do so will result in a fatal error. Classes may implement more than one interface if desired by separating each interface with a comma. 
 
<div class="warning">
     
 A class that implements an interface may use a different name for its parameters than the interface. However, as of PHP 8.0 the language supports [named arguments](functions.named-arguments)], which means callers may rely on the parameter name in the interface. For that reason, it is strongly recommended that developers use the same parameter names as the interface being implemented. 
 
</div>
 
<div class="note">
     
 Interfaces can be extended like classes using the [extends](language.oop5.inheritance)] operator. 
 
</div>
 
<div class="note">
     
 The class implementing the interface must declare all methods in the interface with a [compatible signature](language.oop.lsp)]. A class can implement multiple interfaces which declare a method with the same name. In this case, the implementation must follow the [signature compatibility rules](language.oop.lsp)] for all the interfaces. So [covariance and contravariance](language.oop5.variance)] can be applied. 
 
</div>
 
 

 
 
## Constants
 
 It's possible for interfaces to have constants. Interface constants work exactly like [class constants](language.oop5.constants)]. Prior to PHP 8.1.0, they cannot be overridden by a class/interface that inherits them. 
 
 
 
## Properties
 
 As of PHP 8.4.0, interfaces may also declare properties. If they do, the declaration must specify if the property is to be readable, writeable, or both. The interface declaration applies only to public read and write access. 
 
 A class may satisfy an interface property in multiple ways. It may define a public property. It may define a public virtual property that implements only the corresponding hook. Or a read property may be satisfied by a readonly property. However, an interface property that is settable may not be readonly. 
 
<div class="example">
     
## Interface properties example
 

```php
<?php
interface I
{
    // An implementing class MUST have a publicly-readable property,
    // but whether or not it's publicly settable is unrestricted.
    public string $readable { get; }

    // An implementing class MUST have a publicly-writeable property,
    // but whether or not it's publicly readable is unrestricted.
    public string $writeable { set; }

    // An implementing class MUST have a property that is both publicly
    // readable and publicly writeable.
    public string $both { get; set; }
}

// This class implements all three properties as traditional, un-hooked
// properties. That's entirely valid.
class C1 implements I
{
    public string $readable;

    public string $writeable;

    public string $both;
}

// This class implements all three properties using just the hooks
// that are requested.  This is also entirely valid.
class C2 implements I
{
    private string $written = '';
    private string $all = '';

    // Uses only a get hook to create a virtual property.
    // This satisfies the "public get" requirement.
    // It is not writeable, but that is not required by the interface.
    public string $readable { get => strtoupper($this->writeable); }

    // The interface only requires the property be settable,
    // but also including get operations is entirely valid.
    // This example creates a virtual property, which is fine.
    public string $writeable {
        get => $this->written;
        set {
            $this->written = $value;
        }
    }

    // This property requires both read and write be possible,
    // so we need to either implement both, or allow it to have
    // the default behavior.
    public string $both {
        get => $this->all;
        set {
            $this->all = strtoupper($value);
        }
    }
}
?>
```
 
</div>
 
 
 
## Examples
 
<div class="example">
     
## Interface example
 

```php
<?php

// Declare the interface 'Template'
interface Template
{
    public function setVariable($name, $var);
    public function getHtml($template);
}

// Implement the interface
// This will work
class WorkingTemplate implements Template
{
    private $vars = [];
  
    public function setVariable($name, $var)
    {
        $this->vars[$name] = $var;
    }
  
    public function getHtml($template)
    {
        foreach($this->vars as $name => $value) {
            $template = str_replace('{' . $name . '}', $value, $template);
        }
 
        return $template;
    }
}

// This will not work
// Fatal error: Class BadTemplate contains 1 abstract methods
// and must therefore be declared abstract (Template::getHtml)
class BadTemplate implements Template
{
    private $vars = [];
  
    public function setVariable($name, $var)
    {
        $this->vars[$name] = $var;
    }
}
?>
```
 
</div>
 
<div class="example">
     
## Extendable Interfaces
 

```php
<?php
interface A
{
    public function foo();
}

interface B extends A
{
    public function baz(Baz $baz);
}

// This will work
class C implements B
{
    public function foo()
    {
    }

    public function baz(Baz $baz)
    {
    }
}

// This will not work and result in a fatal error
class D implements B
{
    public function foo()
    {
    }

    public function baz(Foo $foo)
    {
    }
}
?>
```
 
</div>
 
<div class="example">
     
## Variance compatibility with multiple interfaces
 

```php
<?php
class Foo {}
class Bar extends Foo {}

interface A {
    public function myfunc(Foo $arg): Foo;
}

interface B {
    public function myfunc(Bar $arg): Bar;
}

class MyClass implements A, B
{
    public function myfunc(Foo $arg): Bar
    {
        return new Bar();
    }
}
?>
```
 
</div>
 
<div class="example">
     
## Multiple interface inheritance
 

```php
<?php
interface A
{
    public function foo();
}

interface B
{
    public function bar();
}

interface C extends A, B
{
    public function baz();
}

class D implements C
{
    public function foo()
    {
    }

    public function bar()
    {
    }

    public function baz()
    {
    }
}
?>
```
 
</div>
 
<div class="example">
     
## Interfaces with constants
 

```php
<?php
interface A
{
    const B = 'Interface constant';
}

// Prints: Interface constant
echo A::B;


class B implements A
{
    const B = 'Class constant';
}

// Prints: Class constant
// Prior to PHP 8.1.0, this will however not work because it was not
// allowed to override constants.
echo B::B;
?>
```
 
</div>
 
<div class="example">
     
## Interfaces with abstract classes
 

```php
<?php
interface A
{
    public function foo(string $s): string;

    public function bar(int $i): int;
}

// An abstract class may implement only a portion of an interface.
// Classes that extend the abstract class must implement the rest.
abstract class B implements A
{
    public function foo(string $s): string
    {
        return $s . PHP_EOL;
    }
}

class C extends B
{
    public function bar(int $i): int
    {
        return $i * 2;
    }
}
?>
```
 
</div>
 
<div class="example">
     
## Extending and implementing simultaneously
 

```php
<?php

class One
{
    /* ... */
}

interface Usable
{
    /* ... */
}

interface Updatable
{
    /* ... */
}

// The keyword order here is important. 'extends' must come first.
class Two extends One implements Usable, Updatable
{
    /* ... */
}
?>
```
 
</div>
 
 An interface, together with type declarations, provides a good way to make sure that a particular object contains particular methods. See [instanceof](language.operators.type)] operator and [type declarations](language.types.declarations)]. 
 
 
