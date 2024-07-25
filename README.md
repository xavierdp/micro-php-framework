# micro-php-framework

micro-php-framework is a simplistic php framework that integrates various tools such as routing, authentication, an API engine, functions for interacting with databases, as well as advanced debugging tools. micro-php-framework is simple to use, robust and improves the developer mechanism.

## Features

- [Databases](doc/database.md).
- [HTTP](doc/http.md).
- [AUTH](doc/auth.md).
- [API](doc/api.md).
- [Route](doc/route.md).
- [Profiler](doc/profiler.md).
- [Debug](doc/debug.md).

And more...

## Installation

The **micro-php-framework** framework is designed to run on the **[turbinobash web](https://github.com/xavierdp/turbinobash-web)** environment. but it can still start in another environment, in this section we will show how to install it in a turnonibash web environment.

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

1. If **composer** is not yet installed on **[turbinobash web](https://github.com/xavierdp/turbinobash-web)** it must be installed.

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

The bootstrap load the strict minimum to be ready

The XFW PHP is principally composed of debug, autoload, an HTTP router, Auth and a JSON API server.

All your PHP files need to load the bootstrap : you have to find the good relative path from it.

```php
   <?php
   // debug flag
   $GLOBALS["DEBUG"] = true;
   // $GLOBALS["DEBUG_MODE"] = "file";
   // $GLOBALS["DEBUG_MODE"] = "global";
   $GLOBALS["DEBUG_MODE"] = "stdout";


   // path of the bootstrap
   define("DIR_ROOT",realpath(dirname(__FILE__)."/../.."));

   // include the bootstrap
   include(DIR ROOT."/app/startup.php");
```

Include the [startup.php](startup.php) file : You have to configure the base you need your scripts

- paths
- database connexion
- ...

### Paths

Here the base paths i use :

```
├── app
├── etc
├── log
├── sav
└── tmp
```

Here the base skeleton of an application :

```
├── app
│   ├── classes
│   │   └── c
│   │       └── Test.php
│   ├── scripts
│   │   ├── 001.php
│   │   └── 002.php
│   ├── startup.php
│   └── webroot
│       └── index.php
├── core
│   ├── base
│   │   ├── bootstrap.php
│   │   ├── defines.php
│   │   └── functions.php
│   ├── classes
│   │   └── x
│   │       ├── Auth.php
│   │       ├── Core.php
│   │       ├── Model.php
│   │       ├── Mysql.php
│   └── lib
│       └── html5.php
├── cron
│   └── process.php
```

The [startup.php](startup.php) file define the differents paths of your application : it depends of your need

```php
<?php
   define("DIR_SAV",		DIR_ROOT."/sav");
   define("DIR_TMP",		DIR_ROOT."/tmp");
   define("DIR_LOG",		DIR_ROOT."/log");
   define("DIR_APP",		DIR_ROOT."/app");
   define("DIR_ETC",		DIR_ROOT."/etc");
   define("DIR_WEBROOT",	DIR_APP."/webroot");
```
