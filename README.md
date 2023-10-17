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

## Démarage rapide

## Database

## HTTP

## AUTH

## API

**micro-php-framework** has a built-in **API** engine that can be configured in the [webroot/index.php](webroot/index.php) file..

## Route

**micro-php-framework** natively has a routing system which is located in the file [webroot/index.php](webroot/index.php).

## Profiler

## Debug

**micro-php-framework** has several debug methods in cli or web.
