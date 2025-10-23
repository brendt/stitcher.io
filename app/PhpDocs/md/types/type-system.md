 
## Type System
 
 PHP uses a nominal type system with a strong behavioral subtyping relation. The subtyping relation is checked at compile time whereas the verification of types is dynamically checked at run time. 
 
 PHP's type system supports various atomic types that can be composed together to create more complex types. Some of these types can be written as [type declarations](language.types.declarations)]. 
 
 
## Atomic types
 
 Some atomic types are built-in types which are tightly integrated with the language and cannot be reproduced with user defined types. 
 
 The list of base types is: <ul> 
<li> 
Built-in types
 
<ul> 
<li> 
 Scalar types: 
 
<ul> 
<li> 
bool type
 </li>
 
<li> 
int type
 </li>
 
<li> 
float type
 </li>
 
<li> 
string type
 </li>
 </ul>
 </li>
 
<li> 
array type
 </li>
 
<li> 
object type
 </li>
 
<li> 
resource type
 </li>
 
<li> 
never type
 </li>
 
<li> 
void type
 </li>
 
<li> 
 Relative class types: self, parent, and static 
 </li>
 
<li> 
 Singleton types 
 
<ul> 
<li> 
false
 </li>
 
<li> 
true
 </li>
 </ul>
 </li>
 
<li> 
 Unit types 
 
<ul> 
<li> 
null
 </li>
 </ul>
 </li>
 </ul>
 </li>
 
<li> 
 User-defined types (generally referred to as class-types) 
 
<ul> 
<li> 
Interfaces
 </li>
 
<li> 
Classes
 </li>
 
<li> 
Enumerations
 </li>
 </ul>
 </li>
 
<li> 
callable type
 </li>
 </ul> 
 
<!-- start sect3 -->
<!--

   Scalar types
   
    A value is considered scalar if it is of type int,
    float, string or bool.
   
  
-->
 
<!-- start sect3 -->
<!--

   User-defined types
   
    It is possible to define custom types with
    interfaces,
    classes and
    enumerations.
    These are considered as user-defined types, or class-types.
    For example, a class called Elephant can be defined,
    then objects of type Elephant can be instantiated,
    and a function can request a parameter of type Elephant.
   
  
-->
 
 
 
## Composite types
 
 It is possible to combine multiple atomic types into composite types. PHP allows types to be combined in the following ways: 
 
<ul> 
<li> 
 Intersection of class-types (interfaces and class names). 
 </li>
 
<li> 
 Union of types. 
 </li>
 </ul>
 
<!-- start sect3 -->
<!--

   Intersection types
   
    An intersection type accepts values which satisfies multiple
    class-type declarations, rather than a single one.
    Individual types which form the intersection type are joined by the
    {{ amp }} symbol. Therefore, an intersection type comprised
    of the types T, U, and
    V will be written as T{{ amp }}U{{ amp }}V.
   
  
-->
 
<!-- start sect3 -->
<!--

   Union types
   
    A union type accepts values of multiple different types,
    rather than a single one.
    Individual types which form the union type are joined by the
    | symbol. Therefore, a union type comprised
    of the types T, U, and
    V will be written as T|U|V.
    If one of the types is an intersection type, it needs to be bracketed
    with parenthesis for it to written in DNF:
    T|(X{{ amp }}Y).
   
  
-->
 
 
 
## Type aliases
 
 PHP supports two type aliases: `mixed` and `iterable` which corresponds to the [union type](language.types.type-system.composite.union)] of `object|resource|array|string|float|int|bool|null` and `Traversable|array` respectively. 
 
<div class="note">
     
 PHP does not support user-defined type aliases. 
 
</div>
 

