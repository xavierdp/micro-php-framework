<?php
error_reporting(E_ALL);

if (!defined("DIR_WEBROOT")) {
    die("DIR_WEBROOT not configured");
}

if (!defined("DIR_TMP")) {
    die("DIR_TMP not configured");
}

if (!defined("DIR_CACHE")) {
    die("DIR_CACHE not configured");
}

if (!defined("DIR_LOG")) {
    die("DIR_LOG not configured");
}

if (!defined("DIR_CORE")) {
    die("DIR_CORE not configured");
}

include DIR_CORE . "/base/defines.php";
include DIR_CORE . "/base/functions.php";


if (isset($GLOBALS["a_includePath"])) {
    ini_set("include_path", implode(PATH_SEPARATOR, $GLOBALS["a_includePath"]));
}

spl_autoload_register("auto_load");