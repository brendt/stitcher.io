In case of a foreign key error when creating or altering a table, MySQL doesn't show the full message.

You can read the full message by executing the following query and inspecting the `Status` column.

```mysql
show engine innodb status;
```

```
------------------------
LATEST FOREIGN KEY ERROR
------------------------
2018-02-13 11:12:26 0x70000b776000 Error in foreign key constraint of table table/#sql-7fa_247a:
 foreign key (`my_foreing_key`) references `table` (`id`)
   on delete cascade:
Cannot resolve table name close to:
 (`id`)
   on delete cascade
````
