# Debug

## STDOUT

By default debug is outing via stdout.

```php
  $GLOBALS["DEBUG_MODE"] = "stdout";
```

## FILE

```php
  $GLOBALS["DEBUG_MODE"] = "file";
```

The debug file is in DIR_LOG : `php-cli-7.1-debug.log`

There is 4 kind of log file : **cli- debug** and **cli error** ; **cgi debug** and **cgi error**

**micro-php-framework** has several debug methods in cli or web. Debug functions directed by `$GLOBALS["DEBUG_MODE"]`

- ## e()

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

- ## pr()

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

- ## vd()

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

- ## h()

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

- ## d()

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
