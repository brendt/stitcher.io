
 
## break
 

 
 break ends execution of the current for, foreach, while, do-while or switch structure. 
 
 break accepts an optional numeric argument which tells it how many nested enclosing structures are to be broken out of. The default value is 1, only the immediate enclosing structure is broken out of. 
 
  

```php
<?php
$arr = array('one', 'two', 'three', 'four', 'stop', 'five');
foreach ($arr as $val) {
    if ($val == 'stop') {
        break;    /* You could also write 'break 1;' here. */
    }
    echo "$val<br />\n";
}

/* Using the optional argument. */

$i = 0;
while (++$i) {
    switch ($i) {
        case 5:
            echo "At 5<br />\n";
            break 1;  /* Exit only the switch. */
        case 10:
            echo "At 10; quitting<br />\n";
            break 2;  /* Exit the switch and the while. */
        default:
            break;
    }
}
?>
```
  

