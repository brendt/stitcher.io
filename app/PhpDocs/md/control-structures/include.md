
 
## include
 

 
 The include expression includes and evaluates the specified file. 
 
 The documentation below also applies to require. 
 
 Files are included based on the file path given or, if none is given, the include_path specified. If the file isn't found in the include_path, include will finally check in the calling script's own directory and the current working directory before failing. The include construct will emit an E_WARNING if it cannot find a file; this is different behavior from require, which will emit an E_ERROR. 
 
 Note that both include and require raise additional E_WARNINGs, if the file cannot be accessed, before raising the final E_WARNING or E_ERROR, respectively. 
 
 If a path is defined — whether absolute (starting with a drive letter or \ on Windows, or / on Unix/Linux systems) or relative to the current directory (starting with . or ..) — the include_path will be ignored altogether. For example, if a filename begins with ../, the parser will look in the parent directory to find the requested file. 
 
 For more information on how PHP handles including files and the include path, see the documentation for include_path. 
 
 When a file is included, the code it contains inherits the variable scope of the line on which the include occurs. Any variables available at that line in the calling file will be available within the called file, from that point forward. However, all functions and classes defined in the included file have the global scope. 
 
 <div class="example">
     
## Basic include example
 

```php
vars.php
<?php

$color = 'green';
$fruit = 'apple';

?>

test.php
<?php

echo "A $color $fruit"; // A

include 'vars.php';

echo "A $color $fruit"; // A green apple

?>
```
 
</div> 
 
 If the include occurs inside a function within the calling file, then all of the code contained in the called file will behave as though it had been defined inside that function. So, it will follow the variable scope of that function. An exception to this rule are magic constants which are evaluated by the parser before the include occurs. 
 
 <div class="example">
     
## Including within functions
 

```php
<?php

function foo()
{
    global $color;

    include 'vars.php';

    echo "A $color $fruit";
}

/* vars.php is in the scope of foo() so     *
* $fruit is NOT available outside of this  *
* scope.  $color is because we declared it *
* as global.                               */

foo();                    // A green apple
echo "A $color $fruit";   // A green

?>
```
 
</div> 
 
 When a file is included, parsing drops out of PHP mode and into HTML mode at the beginning of the target file, and resumes again at the end. For this reason, any code inside the target file which should be executed as PHP code must be enclosed within valid PHP start and end tags. 
 
 If "URL include wrappers" are enabled in PHP, you can specify the file to be included using a URL (via HTTP or other supported wrapper - see for a list of protocols) instead of a local pathname. If the target server interprets the target file as PHP code, variables may be passed to the included file using a URL request string as used with HTTP GET. This is not strictly speaking the same thing as including the file and having it inherit the parent file's variable scope; the script is actually being run on the remote server and the result is then being included into the local script. 
 
 <div class="example">
     
## include through HTTP
 

```php
<?php

/* This example assumes that www.example.com is configured to parse .php
* files and not .txt files. Also, 'Works' here means that the variables
* $foo and $bar are available within the included file. */

// Won't work; file.txt wasn't handled by www.example.com as PHP
include 'http://www.example.com/file.txt?foo=1&bar=2';

// Won't work; looks for a file named 'file.php?foo=1&bar=2' on the
// local filesystem.
include 'file.php?foo=1&bar=2';

// Works.
include 'http://www.example.com/file.php?foo=1&bar=2';
?>
```
 
</div> 
 
<div class="warning">
     
## Security warning
 
 Remote file may be processed at the remote server (depending on the file extension and the fact if the remote server runs PHP or not) but it still has to produce a valid PHP script because it will be processed at the local server. If the file from the remote server should be processed there and outputted only, `readfile` is much better function to use. Otherwise, special care should be taken to secure the remote script to produce a valid and desired code. 
 
</div>
 
 See also [Remote files](features.remote-files)], `fopen` and `file` for related information. 
 
 Handling Returns: include returns FALSE on failure and raises a warning. Successful includes, unless overridden by the included file, return 1. It is possible to execute a return statement inside an included file in order to terminate processing in that file and return to the script which called it. Also, it's possible to return values from included files. You can take the value of the include call as you would for a normal function. This is not, however, possible when including remote files unless the output of the remote file has valid PHP start and end tags (as with any local file). You can declare the needed variables within those tags and they will be introduced at whichever point the file was included. 
 
 Because `include` is a special language construct, parentheses are not needed around its argument. Take care when comparing return value. <div class="example">
     
## Comparing return value of include
 

```php
<?php
// won't work, evaluated as include(('vars.php') == TRUE), i.e. include('1')
if (include('vars.php') == TRUE) {
    echo 'OK';
}

// works
if ((include 'vars.php') == TRUE) {
    echo 'OK';
}
?>
```
 
</div> 
 
 <div class="example">
     
## include and the return statement
 

```php
return.php
<?php

$var = 'PHP';

return $var;

?>

noreturn.php
<?php

$var = 'PHP';

?>

testreturns.php
<?php

$foo = include 'return.php';

echo $foo; // prints 'PHP'

$bar = include 'noreturn.php';

echo $bar; // prints 1

?>
```
 
</div> 
 
 $bar is the value 1 because the include was successful. Notice the difference between the above examples. The first uses return within the included file while the other does not. If the file can't be included, false is returned and E_WARNING is issued. 
 
 If there are functions defined in the included file, they can be used in the main file independent if they are before `return` or after. If the file is included twice, PHP will raise a fatal error because the functions were already declared. It is recommended to use `include_once` instead of checking if the file was already included and conditionally return inside the included file. 
 
 Another way to "include" a PHP file into a variable is to capture the output by using the Output Control Functions with include. For example: 
 
 <div class="example">
     
## Using output buffering to include a PHP file into a string
 

```php
<?php
$string = get_include_contents('somefile.php');

function get_include_contents($filename) {
    if (is_file($filename)) {
        ob_start();
        include $filename;
        return ob_get_clean();
    }
    return false;
}

?>
```
 
</div> 
 
 In order to automatically include files within scripts, see also the [auto_prepend_file](ini.auto-prepend-file)] and [auto_append_file](ini.auto-append-file)] configuration options in <!-- start filename -->
<!--
php.ini
-->. 
 {{ note.language-construct }} 
 See also require, require_once, include_once, get_included_files, readfile, virtual, and include_path. 

