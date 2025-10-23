
 
## Errors in PHP 7
 
 PHP 7 changes how most errors are reported by PHP. Instead of reporting errors through the traditional error reporting mechanism used by PHP 5, most errors are now reported by throwing `Error` exceptions. 
 
 As with normal exceptions, these `Error` exceptions will bubble up until they reach the first matching [catch](language.exceptions.catch)] block. If there are no matching blocks, then any default exception handler installed with `set_exception_handler` will be called, and if there is no default exception handler, then the exception will be converted to a fatal error and will be handled like a traditional error. 
 
 As the `Error` hierarchy does not inherit from `Exception`, code that uses `catch (Exception $e) { ... }` blocks to handle uncaught exceptions in PHP 5 will find that these `Error`s are not caught by these blocks. Either a `catch (Error $e) { ... }` block or a `set_exception_handler` handler is required. 
 
 
## Error hierarchy
 
<ul> 
<li> 
Throwable
 
<ul> 
<li> 
Error
 
<ul> 
<li> 
ArithmeticError
 
<ul> 
<li> 
DivisionByZeroError
 </li>
 </ul>
 </li>
 
<li> 
AssertionError
 </li>
 
<li> 
CompileError
 
<ul> 
<li> 
ParseError
 </li>
 </ul>
 </li>
 
<li> 
TypeError
 
<ul> 
<li> 
ArgumentCountError
 </li>
 </ul>
 </li>
 
<li> 
ValueError
 </li>
 
<li> 
UnhandledMatchError
 </li>
 
<li> 
FiberError
 </li>
 
<li> 
RequestParseBodyException
 </li>
 </ul>
 </li>
 
<li> 
Exception
 
<ul> 
<li> 
...
 </li>
 </ul>
 </li>
 </ul>
 </li>
 </ul>
 

