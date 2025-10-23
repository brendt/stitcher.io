
 
## Covariance and Contravariance
 
 In PHP 7.2.0, partial contravariance was introduced by removing type restrictions on parameters in a child method. As of PHP 7.4.0, full covariance and contravariance support was added. 
 
 Covariance allows a child's method to return a more specific type than the return type of its parent's method. Contravariance allows a parameter type to be less specific in a child method, than that of its parent. 
 
 A type declaration is considered more specific in the following case: <ul> 
<li> 
 A type is removed from a union type 
 </li>
 
<li> 
 A type is added to an intersection type 
 </li>
 
<li> 
 A class type is changed to a child class type 
 </li>
 
<li> 
 iterable is changed to array or Traversable 
 </li>
 </ul> A type class is considered less specific if the opposite is true. 
 
 
## Covariance
 
 To illustrate how covariance works, a simple abstract parent class, <!-- start varname -->
<!--
Animal
--> is created. <!-- start varname -->
<!--
Animal
--> will be extended by children classes, <!-- start varname -->
<!--
Cat
-->, and <!-- start varname -->
<!--
Dog
-->. 
 
 

```php
<?php

abstract class Animal
{
    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    abstract public function speak();
}

class Dog extends Animal
{
    public function speak()
    {
        echo $this->name . " barks";
    }
}

class Cat extends Animal 
{
    public function speak()
    {
        echo $this->name . " meows";
    }
}
```
 
 
 Note that there aren't any methods which return values in this example. A few factories will be added which return a new object of class type <!-- start varname -->
<!--
Animal
-->, <!-- start varname -->
<!--
Cat
-->, or <!-- start varname -->
<!--
Dog
-->. 
 
 

```php
<?php

interface AnimalShelter
{
    public function adopt(string $name): Animal;
}

class CatShelter implements AnimalShelter
{
    public function adopt(string $name): Cat // instead of returning class type Animal, it can return class type Cat
    {
        return new Cat($name);
    }
}

class DogShelter implements AnimalShelter
{
    public function adopt(string $name): Dog // instead of returning class type Animal, it can return class type Dog
    {
        return new Dog($name);
    }
}

$kitty = (new CatShelter)->adopt("Ricky");
$kitty->speak();
echo "\n";

$doggy = (new DogShelter)->adopt("Mavrick");
$doggy->speak();
```
 
The above example will output:
 
<!-- start screen -->
<!--


Ricky meows
Mavrick barks

   
-->
 
 
 
 
## Contravariance
 
 Continuing with the previous example with the classes <!-- start varname -->
<!--
Animal
-->, <!-- start varname -->
<!--
Cat
-->, and <!-- start varname -->
<!--
Dog
-->, a class called <!-- start varname -->
<!--
Food
--> and <!-- start varname -->
<!--
AnimalFood
--> will be included, and a method <!-- start varname -->
<!--
eat(AnimalFood $food)
--> is added to the <!-- start varname -->
<!--
Animal
--> abstract class. 
 
 

```php
<?php

class Food {}

class AnimalFood extends Food {}

abstract class Animal
{
    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function eat(AnimalFood $food)
    {
        echo $this->name . " eats " . get_class($food);
    }
}
```
 
 
 In order to see the behavior of contravariance, the <!-- start varname -->
<!--
eat
--> method is overridden in the <!-- start varname -->
<!--
Dog
--> class to allow any <!-- start varname -->
<!--
Food
--> type object. The <!-- start varname -->
<!--
Cat
--> class remains unchanged. 
 
 

```php
<?php

class Dog extends Animal
{
    public function eat(Food $food) {
        echo $this->name . " eats " . get_class($food);
    }
}
```
 
 
 The next example will show the behavior of contravariance. 
 
 

```php
<?php

$kitty = (new CatShelter)->adopt("Ricky");
$catFood = new AnimalFood();
$kitty->eat($catFood);
echo "\n";

$doggy = (new DogShelter)->adopt("Mavrick");
$banana = new Food();
$doggy->eat($banana);
```
 
The above example will output:
 
<!-- start screen -->
<!--


Ricky eats AnimalFood
Mavrick eats Food

   
-->
 
 But what happens if <!-- start varname -->
<!--
$kitty
--> tries to <!-- start methodname -->
<!--
eat
--> the <!-- start varname -->
<!--
$banana
-->? 
 

```php
$kitty->eat($banana);
```
 
The above example will output:
 
<!-- start screen -->
<!--


Fatal error: Uncaught TypeError: Argument 1 passed to Animal::eat() must be an instance of AnimalFood, instance of Food given

   
-->
 
 
 
 
## Property variance
 
 By default, properties are neither covariant nor contravariant, hence invariant. That is, their type may not change in a child class at all. The reason for that is "get" operations must be covariant, and "set" operations must be contravariant. The only way for a property to satisfy both requirements is to be invariant. 
 
 As of PHP 8.4.0, with the addition of abstract properties (on an interface or abstract class) and virtual properties, it is possible to declare a property that has only a get or set operation. As a result, abstract properties or virtual properties that have only a "get" operation required may be covariant. Similarly, an abstract property or virtual property that has only a "set" operation required may be contravariant. 
 
 Once a property has both a get and set operation, however, it is no longer covariant or contravariant for further extension. That is, it is now invariant. 
 
<div class="example">
     
## Property type variance
 

```php
<?php
class Animal {}
class Dog extends Animal {}
class Poodle extends Dog {}

interface PetOwner
{
    // Only a get operation is required, so this may be covariant.
    public Animal $pet { get; }
}

class DogOwner implements PetOwner
{
    // This may be a more restrictive type since the "get" side
    // still returns an Animal.  However, as a native property
    // children of this class may not change the type anymore.
    public Dog $pet;
}

class PoodleOwner extends DogOwner
{
    // This is NOT ALLOWED, because DogOwner::$pet has both
    // get and set operations defined and required.
    public Poodle $pet;
}
?>
```
 
</div>
 

