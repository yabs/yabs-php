Badge System - Readme
=========

Online Badge system, using PHP, MySQL, HTML, and JavaScript.

```work in progress```

The Badge system is not yet complete; the next part is the frontend admin panel and the 

Requirements
--------------
 - Apache or nginx
 - MySQL server

Code map
--------------
See ```codemap.md```

Installation
--------------
Download the repo
```
$ git clone https://github.com/yabs/yabs-php.git
$ cd badge_sys
```
Then we have to configure everything.

The default database name is ```badge_sys```, the default user is ```badger```.

If you want to change the database name or user, edit ```./setup.sql``` like the following
```
FILE ./setup.sql

    CREATE DATABASE {database name};
    USE {database name};
    
    GRANT ALL PRIVILEGES ON {database name}.*
        TO '{database user}'@'localhost'
        WITH GRANT OPTION;
    
    [...]
```

Next, create the MySQL user that PHP will use.
If you didn't change the user in ```./setup.sql```, you have to create the user ```badger```

```
$ mysql -u root -p

mysql>CREATE USER '{database user}'@'{database host}'
    -> IDENTIFIED BY '{user's password}';
mysql>exit

$
```

Now the database is ready to be installed
```
$ mysql -u root -p < setup.sql
```

Now that the database is up, we have to tell PHP how to get in.
Edit ```./include/config/sql.php``` like so
```
FILE ./include/config/sql.php

    [...]

    define('DB_HOST', '{database host}'  );
    define('DB_NAME', '{database name}'  );
    define('DB_USER', '{database user}'  );
    define('DB_PASS', '{user's password}');
    
    [...]
```

Now that the SQL is configured, we have to tell apache which files to serve.

In the apache configuration, add a virtual host like so;

```
FILE {apache virtual host file}
    
    [...]
    
    <VirtualHost *:80>
        ServerAdmin admin@localhost
        
        DocumentRoot /path/to/yabs/www
        <Directory /path/to/yabs/www/>
            Options FollowSymLinks MultiViews -Indexes
            AllowOverride all
            Order allow,deny
            allow from all
        </Directory>
        
        ErrorLog ${APACHE_LOG_DIR}/yabs.err
        
        LogLevel warn
        
        CustomLog ${APACHE_LOG_DIR}/yabs.log combined
    </VirtualHost>
    
    [...]
```

Replace the ```/path/to/yabs/``` correctly.

It isn't necessary to use these exact settings,
but it is important that ```DocumentRoot``` is set correctly,
and ```AllowOverride``` is set to ```all```


If you are running nginx, modify ```nginx.conf``` as needed, 
and move to ```/etc/nginx/sites-enabled/```. 
Remove ```.htaccess``` from ```www/```


Installation is complete!
------------

Next up is to add badges to your system via the Admin Panel.

Feel free to fork the project, contributions are appreciated.
