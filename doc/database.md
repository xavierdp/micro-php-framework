# Database

The connection to the database is done in the file [startup.php](startup.php).
The x_Mysql class inherits from the native Mysqli class.
The basic way to create a multiton at startup is like this :

```php
    function &db0()
    {
        return x_Mysql::multiton(
            array(
                "user"   => "user",
                "name"   => "db_name",
                "passwd" => "password",
            )
        );
    }
```

One instance is created and stored to be ru-usable.

## There is many ways to do SQL queries :

- Queries without results :
  ```php
  db()->oQuery("SET ...");
  ```
- Queries with results :

  - One array of arrays data :
    ```php
        db()->oQueryFetchArray("
            SELECT *
            FROM `table`
        "));
    ```
  - One array of data :
    ```php
        db()->oQueryFetchArraySingle("
            SELECT *
            FROM `table`
            LIMIT 1
        "));
    ```
  - One data :

    ```php
        db()->oQueryFetchArraySinglePop("
            SELECT field
            FROM `table`
            LIMIT 1
        "));
    ```

## There is many ways to do inserts :

- Normal insert :
  ```php
      db()->oInsert("table",array);
  ```
- Insert Update :
  <br>
  The update is based on keys.
  ```php
      db()->oInsertUpdate("table",array);
  ```
- Insert ignore :
  <br>
  The Ignore is based on keys.

  ```php
      db()->oInsertIgnore("table",array);
  ```

### Examples :

```php
    function &db()
    {
        return	x_Mysql::multiton
        (
            array
            (
                "user" 	=> "user",
                "name"	=> "db_name",
                "passwd" 	=> "passord"
            )
        );
    }

    db()->oQuery("
        DROP TABLE IF EXISTS `datas`
    ");

    db()->oQuery("
        CREATE TABLE `datas` (
        `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
        `key` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
        `value` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
        `description` mediumtext COLLATE utf8_unicode_ci,
        `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `key` (`key`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
    ");

    db()->oInsert("datas",["key" => "SHOP_NAME","value" => "My Shop"]);
    db()->oInsert("datas",["key" => "SHOP_ADDRESS","value" => "It's here"]);

    db()->oInsertUpdate("datas",["key" => "SHOP_NAME","value" => "My super Shop"]);

    db()->oInsertIgnore("datas",["key" => "SHOP_NAME","value" => "My other Shop"]);

    print_r(db()->oQueryFetchArray("
        SELECT \*
        FROM `datas`
    "));

    print_r(db()->oQueryFetchArraySingle("
        SELECT *
        FROM `datas`
        LIMIT 1
    "));

    print_r(db()->oQueryFetchArraySinglePop("
        SELECT `value`
        FROM `datas`
        WHERE `key` = 'SHOP_NAME'
        LIMIT 1
    "));
```