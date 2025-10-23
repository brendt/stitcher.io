
 
## Visibility
 
 The visibility of a property, a method or (as of PHP 7.1.0) a constant can be defined by prefixing the declaration with the keywords `public`, `protected` or `private`. Class members declared public can be accessed everywhere. Members declared protected can be accessed only within the class itself and by inheriting and parent classes. Members declared as private may only be accessed by the class that defines the member. 
 
 
## Property Visibility
 
 Class properties may be defined as public, private, or protected. Properties declared without any explicit visibility keyword are defined as public. 
 
<div class="example">
     
## Property declaration
 

```php
<?php
/**
 * Define MyClass
 */
class MyClass
{
    public $public = 'Public';
    protected $protected = 'Protected';
    private $private = 'Private';

    function printHello()
    {
        echo $this->public;
        echo $this->protected;
        echo $this->private;
    }
}

$obj = new MyClass();
echo $obj->public; // Works
echo $obj->protected; // Fatal Error
echo $obj->private; // Fatal Error
$obj->printHello(); // Shows Public, Protected and Private


/**
 * Define MyClass2
 */
class MyClass2 extends MyClass
{
    // We can redeclare the public and protected properties, but not private
    public $public = 'Public2';
    protected $protected = 'Protected2';

    function printHello()
    {
        echo $this->public;
        echo $this->protected;
        echo $this->private;
    }
}

$obj2 = new MyClass2();
echo $obj2->public; // Works
echo $obj2->protected; // Fatal Error
echo $obj2->private; // Undefined
$obj2->printHello(); // Shows Public2, Protected2, Undefined

?>
```
 
</div>
 
<!-- start sect3 -->
<!--

    Asymmetric Property Visibility
    
     As of PHP 8.4, properties may also have their
     visibility set asymmetrically, with a different scope for
     reading (get) and writing (set).
     Specifically, the set visibility may be
     specified separately, provided it is not more permissive than the
     default visibility.
    
    
     Asymmetric Property visibility
     

<?php
class Book
{
    public function __construct(
        public private(set) string $title,
        public protected(set) string $author,
        protected private(set) int $pubYear,
    ) {}
}

class SpecialBook extends Book
{
    public function update(string $author, int $year): void
    {
        $this->author = $author; // OK
        $this->pubYear = $year; // Fatal Error
    }
}

$b = new Book('How to PHP', 'Peter H. Peterson', 2024);

echo $b->title; // Works
echo $b->author; // Works
echo $b->pubYear; // Fatal Error

$b->title = 'How not to PHP'; // Fatal Error
$b->author = 'Pedro H. Peterson'; // Fatal Error
$b->pubYear = 2023; // Fatal Error
?>

     
    
    There are a few caveats regarding asymmetric visibility:
    
     
      
       Only typed properties may have a separate set visibility.
      
     
     
      
       The set visibility must be the same
       as get or more restrictive. That is,
       public protected(set) and protected protected(set)
       are allowed, but protected public(set) will cause a syntax error.
      
     
     
      
       If a property is public, then the main visibility may be
       omitted.  That is, public private(set) and private(set)
       will have the same result.
      
     
     
      
       A property with private(set) visibility
       is automatically final, and may not be redeclared in a child class.
      
     
     
      
       Obtaining a reference to a property follows set visibility, not get.
       That is because a reference may be used to modify the property value.
      
     
     
      
       Similarly, trying to write to an array property involves both a get and
       set operation internally, and therefore will follow the set
       visibility, as that is always the more restrictive.
      
     
    
    
     
      Spaces are not allowed in the set-visibility declaration.
      private(set) is correct.
      private( set ) is not correct and will result in a parse error.
     
    
    
     When a class extends another, the child class may redefine
     any property that is not final.  When doing so,
     it may widen either the main visibility or the set
     visibility, provided that the new visibility is the same or wider
     than the parent class.  However, be aware that if a private
     property is overridden, it does not actually change the parent's property
     but creates a new property with a different internal name.
    
    
     Asymmetric Property inheritance
     

<?php
class Book
{
    protected string $title;
    public protected(set) string $author;
    protected private(set) int $pubYear;
}

class SpecialBook extends Book
{
    public protected(set) string $title; // OK, as reading is wider and writing the same.
    public string $author; // OK, as reading is the same and writing is wider.
    public protected(set) int $pubYear; // Fatal Error. private(set) properties are final.
}
?>

     
    
   
-->
 
 
 
## Method Visibility
 
 Class methods may be defined as public, private, or protected. Methods declared without any explicit visibility keyword are defined as public. 
 
<div class="example">
     
## Method Declaration
 

```php
<?php
/**
 * Define MyClass
 */
class MyClass
{
    // Declare a public constructor
    public function __construct() { }

    // Declare a public method
    public function MyPublic() { }

    // Declare a protected method
    protected function MyProtected() { }

    // Declare a private method
    private function MyPrivate() { }

    // This is public
    function Foo()
    {
        $this->MyPublic();
        $this->MyProtected();
        $this->MyPrivate();
    }
}

$myclass = new MyClass;
$myclass->MyPublic(); // Works
$myclass->MyProtected(); // Fatal Error
$myclass->MyPrivate(); // Fatal Error
$myclass->Foo(); // Public, Protected and Private work


/**
 * Define MyClass2
 */
class MyClass2 extends MyClass
{
    // This is public
    function Foo2()
    {
        $this->MyPublic();
        $this->MyProtected();
        $this->MyPrivate(); // Fatal Error
    }
}

$myclass2 = new MyClass2;
$myclass2->MyPublic(); // Works
$myclass2->Foo2(); // Public and Protected work, not Private

class Bar 
{
    public function test() {
        $this->testPrivate();
        $this->testPublic();
    }

    public function testPublic() {
        echo "Bar::testPublic\n";
    }
    
    private function testPrivate() {
        echo "Bar::testPrivate\n";
    }
}

class Foo extends Bar 
{
    public function testPublic() {
        echo "Foo::testPublic\n";
    }
    
    private function testPrivate() {
        echo "Foo::testPrivate\n";
    }
}

$myFoo = new Foo();
$myFoo->test(); // Bar::testPrivate 
                // Foo::testPublic
?>
```
 
</div>
 
 
 
## Constant Visibility
 
 As of PHP 7.1.0, class constants may be defined as public, private, or protected. Constants declared without any explicit visibility keyword are defined as public. 
 
<div class="example">
     
## Constant Declaration as of PHP 7.1.0
 

```php
<?php
/**
 * Define MyClass
 */
class MyClass
{
    // Declare a public constant
    public const MY_PUBLIC = 'public';

    // Declare a protected constant
    protected const MY_PROTECTED = 'protected';

    // Declare a private constant
    private const MY_PRIVATE = 'private';

    public function foo()
    {
        echo self::MY_PUBLIC;
        echo self::MY_PROTECTED;
        echo self::MY_PRIVATE;
    }
}

$myclass = new MyClass();
MyClass::MY_PUBLIC; // Works
MyClass::MY_PROTECTED; // Fatal Error
MyClass::MY_PRIVATE; // Fatal Error
$myclass->foo(); // Public, Protected and Private work


/**
 * Define MyClass2
 */
class MyClass2 extends MyClass
{
    // This is public
    function foo2()
    {
        echo self::MY_PUBLIC;
        echo self::MY_PROTECTED;
        echo self::MY_PRIVATE; // Fatal Error
    }
}

$myclass2 = new MyClass2;
echo MyClass2::MY_PUBLIC; // Works
$myclass2->foo2(); // Public and Protected work, not Private
?>
```
 
</div>
 
 
 
## Visibility from other objects
 
 Objects of the same type will have access to each others private and protected members even though they are not the same instances. This is because the implementation specific details are already known when inside those objects. 
 
<div class="example">
     
## Accessing private members of the same object type
 

```php
<?php
class Test
{
    private $foo;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }

    private function bar()
    {
        echo 'Accessed the private method.';
    }

    public function baz(Test $other)
    {
        // We can change the private property:
        $other->foo = 'hello';
        var_dump($other->foo);

        // We can also call the private method:
        $other->bar();
    }
}

$test = new Test('test');

$test->baz(new Test('other'));
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


string(5) "hello"
Accessed the private method.

    
-->
 
</div>
 
 
