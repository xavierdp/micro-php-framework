# micro-php-framework

micro-php-framework is a simplistic php framework that integrates various tools such as routing, authentication, an API engine, functions for interacting with databases, as well as advanced debugging tools. micro-php-framework is simple to use, robust and improves the developer mechanism.

## Features

- [Databases](#database).
- [HTTP](#http).
- [AUTH](#auth).
- [API](#api).
- [Route](#route).
- [Profiler](#profiler).
- [Debug](#debug).

And more...

## Installation

The **micro-php-framework** framework is designed to run on the **[turbonibash web](https://github.com/xavierdp/turbinobash-web)** environment. but it can still start in another environment, in this section we will show how to install it in a turnonibash web environment.

1. Start by creating an application with **[turbonibash web](https://github.com/xavierdp/turbinobash-web)**.

   ```console
   tb app sudo/create my_app --certbot 8.1
   ```

1. Go to your application directory and delete the webroot folder and clone **micro-php-framework**.

   ```console
       cd /apps/my_app/app
       rm -r webroot/
       git clone https://github.com/xavierdp/micro-php-framework.git .
   ```

1. Bulldozing your app.

   ```console
       tb app sudo/bulldozer my_app
   ```

1. If **composer** is not yet installed on **[turbonibash web](https://github.com/xavierdp/turbinobash-web)** it must be installed.

   ```console
       tb app sudo/install/composer
   ```

1. You must install the dependencies of **composer.json**.

   ```console
       cd /apps/my_app/app/lib
       su my_app
       composer install
   ```

Normally the installation should be finished launching on your browser the link of your application on the route `/test` for example `https://app_host/test` here replace `app_host` with the host of your application the browser should show the `test` message.

## Quick Start

## Database

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

### There is many ways to do SQL queries :

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

## HTTP

## AUTH

## API

**micro-php-framework** has a built-in **API** engine that can be configured in the [webroot/index.php](webroot/index.php) file..

## Route

**micro-php-framework** natively has a routing system which is located in the file [webroot/index.php](webroot/index.php).

## Profiler

## Debug

**micro-php-framework** has several debug methods in cli or web.

- ### e()

  It’s the echo function
  <br>
  PHP code :

  ```php
  e("this is a string");
  ```

  Out log :

  ```console
        this is a string
  ```

- ### pr()

  It’s the print_r function
  <br>
  PHP code :

  ```php
      $a_data = array
      (
          "0"   => "zero",
          "1"	=> "one",
          "2"	=> "two",
      );
      pr($a_data);
  ```

  Out log :

  ```php
        Array
        (
            [0] => zero
            [1] => one
            [2] => two
        )
  ```

- ### vd()

  It’s the var_dump function
  <br>
  PHP code :

  ```php
      $a_data = array
      (
          "0"   => "zero",
          "1"	=> "one",
          "2"	=> "two",
      );
      vd($a_data);
  ```

  Out log :

  ```php
        array(3) {
        [0]=>
        string(4) "zero"
        [1]=>
        string(3) "one"
        [2]=>
        string(3) "two"
        }
  ```

- ### h()

  Like an header
  <br>
  PHP code :

  ```php
      h("this is a string");
  ```

  Out log :

  ```console
    =========================== this is a string ===========================
  ```

- ### d()

  Like a date time
  <br>
  PHP code :

  ```php
      d();
  ```

  Out log :

  ```console
    ========================= 2018-11-07 18:40:34 ==========================
  ```
