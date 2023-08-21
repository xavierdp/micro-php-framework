<?php
/**
 * set default production boolean
 */
if (!defined("PRODUCTION")) {
    define("PRODUCTION", false);
}

/**
 * set default debug boolean
 */

if (!isset($GLOBALS["DEBUG"])) {
    $GLOBALS["DEBUG"] = false;
}


/**
 * set default debug mode boolean
 * debug modes are : file, stdout, global
 *   
 */

if (!isset($GLOBALS["DEBUG_MODE"])) {
    $GLOBALS["DEBUG_MODE"] = "file";
}

/**
 * to enable to see all tests class dirs
 */
if (!isset($GLOBALS["DEBUG_CLASS_PATH"])) {
    $GLOBALS["DEBUG_CLASS_PATH"] = false;
}

/**
 * to enable to see what class file found
 */
if (!isset($GLOBALS["DEBUG_CLASS_FIND"])) {
    $GLOBALS["DEBUG_CLASS_FIND"] = false;
}

/**
 * to enable to see all views tests dirs
 */
if (!isset($GLOBALS["DEBUG_VIEW_PATH"])) {
    $GLOBALS["DEBUG_VIEW_PATH"] = false;
}

/**
 * to enable to see what view file found
 */
if (!isset($GLOBALS["DEBUG_VIEW_FIND"])) {
    $GLOBALS["DEBUG_VIEW_FIND"] = false;
}


/**
 * to enable sql debug
 */
if (!isset($GLOBALS["DEBUG_SQL"])) {
    $GLOBALS["DEBUG_SQL"] = false;
}


/**
 * CLI or WEB
 */
define("PHP_CLI", empty($_SERVER["GATEWAY_INTERFACE"]));

define("PHP_VERSION_MAJOR_MINOR", preg_replace("%\.[^.]*-.*%", "", PHP_VERSION));

/**
 * set remote adress
 */
if (!defined("REMOTE_ADDR")) {
    if (PHP_CLI) {
        define("REMOTE_ADDR", "console");
    } else {
        define("REMOTE_ADDR", $_SERVER["REMOTE_ADDR"]);
    }
}

/**
 * env LOGNAME variable
 */
if (!defined("LOGNAME") and isset($_ENV["LOGNAME"])) {
    define("LOGNAME", $_ENV["LOGNAME"]);
}

/**
 * env HOME variable
 */
if (!defined("HOME") and isset($_ENV["HOME"])) {
    define("HOME", $_ENV["HOME"]);
}

/**
 * get system HOSTNAME 
 */
if (!defined("HOSTNAME")) {
    define("HOSTNAME", gethostname());
}