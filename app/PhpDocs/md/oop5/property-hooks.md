
 
## Property Hooks
 
 Property hooks, also known as "property accessors" in some other languages, are a way to intercept and override the read and write behavior of a property. This functionality serves two purposes: 
 
<!-- start orderedlist -->
<!--

  
   
    It allows for properties to be used directly, without get- and set- methods,
    while leaving the option open to add additional behavior in the future.
    That renders most boilerplate get/set methods unnecessary,
    even without using hooks.
   
  
  
   
    It allows for properties that describe an object without needing to store
    a value directly.
   
  
 
-->
 
 There are two hooks available on non-static properties: get and set. They allow overriding the read and write behavior of a property, respectively. Hooks are available for both typed and untyped properties. 
 
 A property may be "backed" or "virtual". A backed property is one that actually stores a value. Any property that has no hooks is backed. A virtual property is one that has hooks and those hooks do not interact with the property itself. In this case, the hooks are effectively the same as methods, and the object does not use any space to store a value for that property. 
 
 Property hooks are incompatible with readonly properties. If there is a need to restrict access to a get or set operation in addition to altering its behavior, use asymmetric property visibility. 
 
<div class="note">
     
## Version Information
 
 Property hooks were introduced in PHP 8.4. 
 
</div>
 
 
## Basic Hook Syntax
 
 The general syntax for declaring a hook is as follows. 
 
<div class="example">
     
## Property hooks (full version)
 

```php
<?php
class Example
{
    private bool $modified = false;

    public string $foo = 'default value' {
        get {
            if ($this->modified) {
                return $this->foo . ' (modified)';
            }
            return $this->foo;
        }
        set(string $value) {
            $this->foo = strtolower($value);
            $this->modified = true;
        }
    }
}

$example = new Example();
$example->foo = 'changed';
print $example->foo;
?>
```
 
</div>
 
 The $foo property ends in {}, rather than a semicolon. That indicates the presence of hooks. Both a get and set hook are defined, although it is allowed to define only one or the other. Both hooks have a body, denoted by {}, that may contain arbitrary code. 
 
 The set hook additionally allows specifying the type and name of an incoming value, using the same syntax as a method. The type must be either the same as the type of the property, or contravariant (wider) to it. For instance, a property of type string could have a set hook that accepts stringStringable, but not one that only accepts array. 
 
 At least one of the hooks references $this->foo, the property itself. That means the property will be "backed". When calling $example->foo = 'changed', the provided string will be first cast to lowercase, then saved to the backing value. When reading from the property, the previously saved value may conditionally be appended with additional text. 
 
 There are a number of shorthand syntax variants as well to handle common cases. 
 
 If the get hook is a single expression, then the {} may be omitted and replaced with an arrow expression. 
 
<div class="example">
     
## Property get expression
 
 This example is equivalent to the previous. 
 

```php
<?php
class Example
{
    private bool $modified = false;

    public string $foo = 'default value' {
        get => $this->foo . ($this->modified ? ' (modified)' : '');

        set(string $value) {
            $this->foo = strtolower($value);
            $this->modified = true;
        }
    }
}
?>
```
 
</div>
 
 If the set hook's parameter type is the same as the property type (which is typical), it may be omitted. In that case, the value to set is automatically given the name $value. 
 
<div class="example">
     
## Property set defaults
 
 This example is equivalent to the previous. 
 

```php
<?php
class Example
{
    private bool $modified = false;

    public string $foo = 'default value' {
        get => $this->foo . ($this->modified ? ' (modified)' : '');

        set {
            $this->foo = strtolower($value);
            $this->modified = true;
        }
    }
}
?>
```
 
</div>
 
 If the set hook is only setting a modified version of the passed in value, then it may also be simplified to an arrow expression. The value the expression evaluates to will be set on the backing value. 
 
<div class="example">
     
## Property set expression
 

```php
<?php
class Example
{
    public string $foo = 'default value' {
        get => $this->foo . ($this->modified ? ' (modified)' : '');
        set => strtolower($value);
    }
}
?>
```
 
</div>
 
 This example is not quite equivalent to the previous, as it does not also modify $this->modified. If multiple statements are needed in the set hook body, use the braces version. 
 
 A property may implement zero, one, or both hooks as the situation requires. All shorthand versions are mutually-independent. That is, using a short-get with a long-set, or a short-set with an explicit type, or so on is all valid. 
 
 On a backed property, omitting a get or set hook means the default read or write behavior will be used. 
 
<div class="note">
     
 Hooks can be defined when using constructor property promotion. However, when doing so, values provided to the constructor must match the type associated with the property, regardless of what the set hook might allow. 
 
 Consider the following: 
 

```php
<?php
class Example
{
    public function __construct(
        public private(set) DateTimeInterface $created {
            set (string|DateTimeInterface $value) {
                if (is_string($value)) {
                    $value = new DateTimeImmutable($value);
                }
                $this->created = $value;
            }
        },
    ) {
    }
}
```
 
 Internally, the engine decomposes this to the following: 
 

```php
<?php
class Example
{
    public private(set) DateTimeInterface $created {
        set (string|DateTimeInterface $value) {
            if (is_string($value)) {
                $value = new DateTimeImmutable($value);
            }
            $this->created = $value;
        }
    }

    public function __construct(
        DateTimeInterface $created,
    ) {
        $this->created = $created;
    }
}
```
 
 Any attempts to set the property outside the constructor will allow either string or DateTimeInterface values, but the constructor will only allow DateTimeInterface. This is because the defined type for the property (DateTimeInterface) is used as the parameter type within the constructor signature, regardless of what the set hook allows. 
 
 If this kind of behavior is needed from the constructor, constructor property promotion cannot be used. 
 
</div>
 
 
 
## Virtual properties
 
 Virtual properties are properties that have no backing value. A property is virtual if neither its get nor set hook references the property itself using exact syntax. That is, a property named $foo whose hook contains $this->foo will be backed. But the following is not a backed property, and will error: 
 
<div class="example">
     
## Invalid virtual property
 

```php
<?php
class Example
{
    public string $foo {
        get {
            $temp = __PROPERTY__;
            return $this->$temp; // Doesn't refer to $this->foo, so it doesn't count.
        }
    }
}
?>
```
 
</div>
 
 For virtual properties, if a hook is omitted then that operation does not exist and trying to use it will produce an error. Virtual properties take up no memory space in an object. Virtual properties are suited for "derived" properties, such as those that are the combination of two other properties. 
 
<div class="example">
     
## Virtual property
 

```php
<?php
class Rectangle
{
    // A virtual property.
    public int $area {
        get => $this->h * $this->w;
    }

    public function __construct(public int $h, public int $w) {}
}

$s = new Rectangle(4, 5);
print $s->area; // prints 20
$s->area = 30; // Error, as there is no set operation defined.
?>
```
 
</div>
 
 Defining both a get and set hook on a virtual property is also allowed. 
 
 
 
## Scoping
 
 All hooks operate in the scope of the object being modified. That means they have access to all public, private, or protected methods of the object, as well as any public, private, or protected properties, including properties that may have their own property hooks. Accessing another property from within a hook does not bypass the hooks defined on that property. 
 
 The most notable implication of this is that non-trivial hooks may sub-call to an arbitrarily complex method if they wish. 
 
<div class="example">
     
## Calling a method from a hook
 

```php
<?php
class Person {
    public string $phone {
        set => $this->sanitizePhone($value);
    }

    private function sanitizePhone(string $value): string {
        $value = ltrim($value, '+');
        $value = ltrim($value, '1');

        if (!preg_match('/\d\d\d\-\d\d\d\-\d\d\d\d/', $value)) {
            throw new \InvalidArgumentException();
        }
        return $value;
    }
}
?>
```
 
</div>
 
 
 
## References
 
 Because the presence of hooks intercept the read and write process for properties, they cause issues when acquiring a reference to a property or with indirect modification, such as $this->arrayProp['key'] = 'value';. That is because any attempted modification of the value by reference would bypass a set hook, if one is defined. 
 
 In the rare case that getting a reference to a property that has hooks defined is necessary, the get hook may be prefixed with {{ amp }} to cause it to return by reference. Defining both get and {{ amp }}get on the same property is a syntax error. 
 
 Defining both {{ amp }}get and set hooks on a backed property is not allowed. As noted above, writing to the value returned by reference would bypass the set hook. On virtual properties, there is no necessary common value shared between the two hooks, so defining both is allowed. 
 
 Writing to an index of an array property also involves an implicit reference. For that reason, writing to a backed array property with hooks defined is allowed if and only if it defines only a {{ amp }}get hook. On a virtual property, writing to the array returned from either get or {{ amp }}get is legal, but whether that has any impact on the object depends on the hook implementation. 
 
 Overwriting the entire array property is fine, and behaves the same as any other property. Only working with elements of the array require special care. 
 
 
 
## Inheritance
 
<!-- start sect3 -->
<!--

   Final hooks
   
    Hooks may also be declared final,
    in which case they may not be overridden.
   
   
    Final hooks
    

<?php
class User
{
    public string $username {
        final set => strtolower($value);
    }
}

class Manager extends User
{
    public string $username {
        // This is allowed
        get => strtoupper($this->username);

        // But this is NOT allowed, because set is final in the parent.
        set => strtoupper($value);
    }
}
?>

    
   
   
    A property may also be declared final.
    A final property may not be redeclared by a child class in any way,
    which precludes altering hooks or widening its access.
   
   
    Declaring hooks final on a property that is declared final is redundant,
    and will be silently ignored.
    This is the same behavior as final methods.
   
   
    A child class may define or redefine individual hooks on a property
    by redefining the property and just the hooks it wishes to override.
    A child class may also add hooks to a property that had none.
    This is essentially the same as if the hooks were methods.
   
   
    Hook inheritance
    

<?php
class Point
{
    public int $x;
    public int $y;
}

class PositivePoint extends Point
{
    public int $x {
        set {
            if ($value < 0) {
                throw new \InvalidArgumentException('Too small');
            }
            $this->x = $value;
        }
    }
}
?>

    
   
   
    Each hook overrides parent implementations independently of each other.
    If a child class adds hooks, any default value set on the property is removed, and must be redeclared.
    That is the same consistent with how inheritance works on hook-less properties.
   
  
-->
 
<!-- start sect3 -->
<!--

   Accessing parent hooks
   
    A hook in a child class may access the parent class's property using the
    parent::$prop keyword, followed by the desired hook.
    For example, parent::$propName::get().
    It may be read as "access the prop defined on the parent class,
    and then run its get operation" (or set operation, as appropriate).
   
   
    If not accessed this way, the parent class's hook is ignored.
    This behavior is consistent with how all methods work.
    This also offers a way to access the parent class's storage, if any.
    If there is no hook on the parent property,
    its default get/set behavior will be used.
    Hooks may not access any other hook except their own parent on their own property.
   
   
    The example above could be rewritten as follows, which would allow for the
    Point class to add its own set hook
    in the future without issues (in the previous example, a hook added to
    the parent class would be ignored in the child).
   
   
    Parent hook access (set)
    

<?php
class Point
{
    public int $x;
    public int $y;
}

class PositivePoint extends Point
{
    public int $x {
        set {
            if ($value < 0) {
                throw new \InvalidArgumentException('Too small');
            }
            parent::$x::set($value);
        }
    }
}
?>

    
   
   
    An example of overriding only a get hook could be:
   
   
    Parent hook access (get)
    

<?php
class Strings
{
    public string $val;
}

class CaseFoldingStrings extends Strings
{
    public bool $uppercase = true;

    public string $val {
        get => $this->uppercase
            ? strtoupper(parent::$val::get())
            : strtolower(parent::$val::get());
    }
}
?>

    
   
  
-->
 
 
 
## Serialization
 
 PHP has a number of different ways in which an object may be serialized, either for public consumption or for debugging purposes. The behavior of hooks varies depending on the use case. In some cases, the raw backing value of a property will be used, bypassing any hooks. In others, the property will be read or written "through" the hook, just like any other normal read/write action. 
 
<!-- start simplelist -->
<!--

   var_dump: Use raw value
   serialize: Use raw value
   unserialize: Use raw value
   __serialize()/__unserialize(): Custom logic, uses get/set hook
   Array casting: Use raw value
   var_export: Use get hook
   json_encode: Use get hook
   JsonSerializable: Custom logic, uses get hook
   get_object_vars: Use get hook
   get_mangled_object_vars: Use raw value
  
-->
 

