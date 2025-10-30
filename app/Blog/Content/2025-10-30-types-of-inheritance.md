"Inheritance" is one of the pillars of object-oriented programming, yet there are many different views on how to use it effectively. In this post I want to discuss these different view points, where I think it goes wrong when trying to mesh different types of inheritance together, and how PHP could potentially solve these problems.

Let's start with the classic example: `{php}Dog extends Pet`. 

```php
abstract class Pet
{
    // …
    
    public function getName(): string 
    {
        return $this->name;
    }
    
    abstract public function makeNoise(): string;
}

class Dog extends Pet
{
    public function makeNoise(): string
    {
        return 'woof';
    }
}

class Cat extends Pet
{
    public function makeNoise(): string
    {
        return 'mew';
    }
}
```

The most classic of examples, and it shows two characteristics of inheritance:

- Code reuse: `{php}getName()` is shared throughout all children
- Polymorphism: any instance of `{php}Pet` will be able to `{php}makeNoise()`, but which noise you'll get is determined by the concrete type