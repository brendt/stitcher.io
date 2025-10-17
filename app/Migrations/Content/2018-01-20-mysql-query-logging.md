## Enable query logging

```
mysql -p -u root

> SET GLOBAL general_log = 'ON';

# Turning it off again when finished

> SET GLOBAL general_log = 'OFF';
```

## Find the log file

First, find the `mysqld` process ID.

```
ps auxww | grep mysql

brent             2042   0.0  0.4  2849776  67772   ??  S    Fri11AM   0:16.80 /usr/local/opt/mysql/bin/mysqld
```

Second, use `lsof` to find all files used by this process, and filter on `log`.

```
# sudo lsof -p <PID> | grep log

sudo lsof -p 2042 | grep log

mysqld  2042 brent    4u     REG                1,4  50331648  780601 /usr/local/var/mysql/ib_logfile0
mysqld  2042 brent    9u     REG                1,4  50331648  780602 /usr/local/var/mysql/ib_logfile1
mysqld  2042 brent   26u     REG                1,4        35  780672 /usr/local/var/mysql/mysql/general_log.CSM
mysqld  2042 brent   32r     REG                1,4         0  780673 /usr/local/var/mysql/mysql/general_log.CSV
mysqld  2042 brent   33w     REG                1,4     25504 9719379 /usr/local/var/mysql/HOST.log
```

`/usr/local/var/mysql/HOST.log` is the one you want, `HOST` will be the name of your host.

```
tail -f /usr/local/var/mysql/HOST.log
```
