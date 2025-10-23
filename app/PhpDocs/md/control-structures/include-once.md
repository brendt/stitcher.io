
 
## include_once
 

 
 The `include_once` expression includes and evaluates the specified file during the execution of the script. This is a behavior similar to the `include` expression, with the only difference being that if the code from a file has already been included, it will not be included again, and include_once returns `true`. As the name suggests, the file will be included just once. 
 
 `include_once` may be used in cases where the same file might be included and evaluated more than once during a particular execution of a script, so in this case it may help avoid problems such as function redefinitions, variable value reassignments, etc. 
 
 See the `include` documentation for information about how this function works. 

