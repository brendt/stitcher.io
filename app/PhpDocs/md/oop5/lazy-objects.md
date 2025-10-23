
 
## Lazy Objects
 
 A lazy object is an object whose initialization is deferred until its state is observed or modified. Some use-case examples include dependency injection components that provide lazy services fully initialized only if needed, ORMs providing lazy entities that hydrate themselves from the database only when accessed, or a JSON parser that delays parsing until elements are accessed. 
 
 Two lazy object strategies are supported: Ghost Objects and Virtual Proxies, hereafter referred to as {{ quot }}lazy ghosts{{ quot }} and {{ quot }}lazy proxies{{ quot }}. In both strategies, the lazy object is attached to an initializer or factory that is called automatically when its state is observed or modified for the first time. From an abstraction point of view, lazy ghost objects are indistinguishable from non-lazy ones: they can be used without knowing they are lazy, allowing them to be passed to and used by code that is unaware of laziness. Lazy proxies are similarly transparent, but care must be taken when their identity is used, as the proxy and its real instance have different identities. 
 
<div class="note">
     
## Version Information
 
 Lazy objects were introduced in PHP 8.4. 
 
</div>
 
 
## Creating Lazy Objects
 
 It is possible to create a lazy instance of any user defined class or the stdClass class (other internal classes are not supported), or to reset an instance of these classes to make it lazy. The entry points for creating a lazy object are the ReflectionClass::newLazyGhost and ReflectionClass::newLazyProxy methods. 
 
 Both methods accept a function that is called when the object requires initialization. The function's expected behavior varies depending on the strategy in use, as described in the reference documentation for each method. 
 
<div class="example">
     
## Creating a Lazy Ghost
 

```php
<?php
class Example
{
    public function __construct(public int $prop)
    {
        echo __METHOD__, "\n";
    }
}

$reflector = new ReflectionClass(Example::class);
$lazyObject = $reflector->newLazyGhost(function (Example $object) {
    // Initialize object in-place
    $object->__construct(1);
});

var_dump($lazyObject);
var_dump(get_class($lazyObject));

// Triggers initialization
var_dump($lazyObject->prop);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


lazy ghost object(Example)#3 (0) {
["prop"]=>
uninitialized(int)
}
string(7) "Example"
Example::__construct
int(1)

   
-->
 
</div>
 
<div class="example">
     
## Creating a Lazy Proxy
 

```php
<?php
class Example
{
    public function __construct(public int $prop)
    {
        echo __METHOD__, "\n";
    }
}

$reflector = new ReflectionClass(Example::class);
$lazyObject = $reflector->newLazyProxy(function (Example $object) {
    // Create and return the real instance
    return new Example(1);
});

var_dump($lazyObject);
var_dump(get_class($lazyObject));

// Triggers initialization
var_dump($lazyObject->prop);
?>
```
 
The above example will output:
 
<!-- start screen -->
<!--


lazy proxy object(Example)#3 (0) {
  ["prop"]=>
  uninitialized(int)
}
string(7) "Example"
Example::__construct
int(1)

   
-->
 
</div>
 
 Any access to properties of a lazy object triggers its initialization (including via ReflectionProperty). However, certain properties might be known in advance and should not trigger initialization when accessed: 
 
<div class="example">
     
## Initializing Properties Eagerly
 

```php
<?php
class BlogPost
{
    public function __construct(
        public int $id,
        public string $title,
        public string $content,
    ) { }
}

$reflector = new ReflectionClass(BlogPost::class);

$post = $reflector->newLazyGhost(function ($post) {
    $data = fetch_from_store($post->id);
    $post->__construct($data['id'], $data['title'], $data['content']);
});

// Without this line, the following call to ReflectionProperty::setValue() would
// trigger initialization.
$reflector->getProperty('id')->skipLazyInitialization($post);
$reflector->getProperty('id')->setValue($post, 123);

// Alternatively, one can use this directly:
$reflector->getProperty('id')->setRawValueWithoutLazyInitialization($post, 123);

// The id property can be accessed without triggering initialization
var_dump($post->id);
?>
```
 
</div>
 
 The ReflectionProperty::skipLazyInitialization and ReflectionProperty::setRawValueWithoutLazyInitialization methods offer ways to bypass lazy-initialization when accessing a property. 
 
 
 
## About Lazy Object Strategies
 
 Lazy ghosts are objects that initialize in-place and, once initialized, are indistinguishable from an object that was never lazy. This strategy is suitable when we control both the instantiation and initialization of the object, making it unsuitable if either of these is managed by another party. 
 
 Lazy proxies, once initialized, act as proxies to a real instance: any operation on an initialized lazy proxy is forwarded to the real instance. The creation of the real instance can be delegated to another party, making this strategy useful in cases where lazy ghosts are unsuitable. Although lazy proxies are nearly as transparent as lazy ghosts, caution is needed when their identity is used, as the proxy and its real instance have distinct identities. 
 
 
 
## Lifecycle of Lazy Objects
 
 Objects can be made lazy at instantiation time using ReflectionClass::newLazyGhost or ReflectionClass::newLazyProxy, or after instantiation by using ReflectionClass::resetAsLazyGhost or ReflectionClass::resetAsLazyProxy. Following this, a lazy object can become initialized through one of the following operations: 
 
<!-- start simplelist -->
<!--

   
    Interacting with the object in a way that triggers automatic initialization. See
    Initialization
    triggers.
   
   
    Marking all its properties as non-lazy using
    ReflectionProperty::skipLazyInitialization or
    ReflectionProperty::setRawValueWithoutLazyInitialization.
   
   
    Calling explicitly ReflectionClass::initializeLazyObject
    or ReflectionClass::markLazyObjectAsInitialized.
   
  
-->
 
 As lazy objects become initialized when all their properties are marked non-lazy, the above methods will not mark an object as lazy if no properties could be marked as lazy. 
 
 
 
## Initialization Triggers
 
 Lazy objects are designed to be fully transparent to their consumers, so normal operations that observe or modify the object's state will automatically trigger initialization before the operation is performed. This includes, but is not limited to, the following operations: 
 
<!-- start simplelist -->
<!--

   
    Reading or writing a property.
   
   
    Testing if a property is set or unsetting it.
   
   
    Accessing or modifying a property via
    ReflectionProperty::getValue,
    ReflectionProperty::getRawValue,
    ReflectionProperty::setValue,
    or ReflectionProperty::setRawValue.
   
   
    Listing properties with
    ReflectionObject::getProperties,
    ReflectionObject::getProperty,
    get_object_vars.
   
   
    Iterating over properties of an object that does not implement
    Iterator or
    IteratorAggregate using
    foreach.
   
   
    Serializing the object with serialize,
    json_encode, etc.
   
   
    Cloning the
    object.
   
  
-->
 
 Method calls that do not access the object state will not trigger initialization. Similarly, interactions with the object that invoke magic methods or hook functions will not trigger initialization if these methods or functions do not access the object's state. 
 
<!-- start sect3 -->
<!--

   Non-Triggering Operations

   
    The following specific methods or low-level operations allow access or
    modification of lazy objects without triggering initialization:
   

   
    
     Marking properties as non-lazy with
     ReflectionProperty::skipLazyInitialization or
     ReflectionProperty::setRawValueWithoutLazyInitialization.
    
    
     Retrieving the internal representation of properties using
     get_mangled_object_vars or by
     casting the object to an
     array.
    
    
     Using serialize when
     ReflectionClass::SKIP_INITIALIZATION_ON_SERIALIZE
     is set, unless
     __serialize() or
     __sleep() trigger initialization.
    
    
     Calling to ReflectionObject::__toString.
    
    
     Using var_dump or
     debug_zval_dump, unless
     __debugInfo() triggers
     initialization.
    
   
  
-->
 
 
 
## Initialization Sequence
 
 This section outlines the sequence of operations performed when initialization is triggered, based on the strategy in use. 
 
<!-- start sect3 -->
<!--

   Ghost Objects
   
    
     The object is marked as non-lazy.
    
    
     Properties not initialized with
     ReflectionProperty::skipLazyInitialization or
     ReflectionProperty::setRawValueWithoutLazyInitialization
     are set to their default values, if any. At this stage, the object
     resembles one created with
     ReflectionClass::newInstanceWithoutConstructor,
     except for already initialized properties.
    
    
     The initializer function is then called with the object as its first
     parameter. The function is expected, but not required, to initialize
     the object state, and must return null or no value. The object is no
     longer lazy at this point, so the function can access its properties
     directly.
    
   
   
    After initialization, the object is indistinguishable from an object that
    was never lazy.
   
  
-->
 
<!-- start sect3 -->
<!--

   Proxy Objects
   
    
     The object is marked as non-lazy.
    
    
     Unlike ghost objects, the properties of the object are not modified at
     this stage.
    
    
     The factory function is called with the object as its first parameter and
     must return a non-lazy instance of a compatible class (see
     ReflectionClass::newLazyProxy).
    
    
     The returned instance is referred to as the real
     instance and is attached to the proxy.
    
    
     The proxy's property values are discarded as though
     unset was called.
    
   
   
    After initialization, accessing any property on the proxy will
    yield the same result as accessing the corresponding property on
    the real instance; all property accesses on the proxy are forwarded
    to the real instance, including declared, dynamic, non-existing, or
    properties marked with
    ReflectionProperty::skipLazyInitialization or
    ReflectionProperty::setRawValueWithoutLazyInitialization.
   
   
    The proxy object itself is not replaced or substituted
    for the real instance.
   
   
    While the factory receives the proxy as its first parameter, it is
    not expected to modify it (modifications are allowed but will be lost
    during the final initialization step). However, the proxy can be used
    for decisions based on the values of initialized properties, the class,
    the object itself, or its identity. For instance, the initializer might
    use an initialized property's value when creating the real instance.
   
  
-->
 
<!-- start sect3 -->
<!--

   Common Behavior

   
    The scope and $this context of the initializer or factory
    function remains unchanged, and usual visibility constraints apply.
   

   
    After successful initialization, the initializer or factory function
    is no longer referenced by the object and may be released if it has no
    other references.
   

   
    If the initializer throws an exception, the object state is reverted to its
    pre-initialization state and the object is marked as lazy again. In other
    words, all effects on the object itself are reverted. Other side effects,
    such as effects on other objects, are not reverted. This prevents
    exposing a partially initialized instance in case of failure.
   
  
-->
 
 
 
## Cloning
 
 Cloning a lazy object triggers its initialization before the clone is created, resulting in an initialized object. 
 
 For proxy objects, both the proxy and its real instance are cloned, and the clone of the proxy is returned. The __clone method is called on the real instance, not on the proxy. The cloned proxy and real instance are linked as they are during initialization, so accesses to the proxy clone are forwarded to the real instance clone. 
 
 This behavior ensures that the clone and the original object maintain separate states. Changes to the original object or its initializer's state after cloning do not affect the clone. Cloning both the proxy and its real instance, rather than returning a clone of the real instance alone, ensures that the clone operation consistently returns an object of the same class. 
 
 
 
## Destructors
 
 For lazy ghosts, the destructor is only called if the object has been initialized. For proxies, the destructor is only called on the real instance, if one exists. 
 
 The ReflectionClass::resetAsLazyGhost and ReflectionClass::resetAsLazyProxy methods may invoke the destructor of the object being reset. 
 
