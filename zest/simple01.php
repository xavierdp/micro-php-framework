<?php

$GLOBALS["DEBUG"] = true;

$GLOBALS["DEBUG_MODE"] = "file";
$GLOBALS["DEBUG_MODE"] = "stdout";
// $GLOBALS["DEBUG_MODE"] = "global";

$GLOBALS["DEBUG_REGEX"] = "^[^b]";
$GLOBALS["ERROR_LOG"]   = true;
// $GLOBALS["DEBUG_SQL"] = true;
// $GLOBALS["PROFILER_SQL"] = true;

$GLOBALS["DEBUG_CLASS_WALK"]  = true;
$GLOBALS["DEBUG_CLASS_FOUND"] = true;
// $GLOBALS["DEBUG_CLASS_MATCH"] = "c_";

define("DIR_ROOT", realpath(dirname(__FILE__) . "/../.."));

include DIR_ROOT . "/app/startup.php";

d();

