<?php
function pr($data = null, $params = null)
{
    if ($GLOBALS["DEBUG"] === true) {
        debug(__FUNCTION__, $data, $params);
    }

}

function e($data = null, $params = null, $bt = false)
{
    if ($GLOBALS["DEBUG"] === true) {
        debug(__FUNCTION__, $data, $params);
    }

    return $data;
}

function vd($data = null, $params = null)
{
    if ($GLOBALS["DEBUG"] === true) {
        debug(__FUNCTION__, $data, $params);
    }

    return $data;
}

function h($data = null, $params = null)
{
    if ($GLOBALS["DEBUG"] === true) {
        debug(__FUNCTION__, $data, $params);
    }

    return $data;
}

function b($data = null, $params = null)
{
    if ($GLOBALS["DEBUG"] === true) {
        debug(__FUNCTION__, $data, $params);
    }

    return $data;
}

function d($params = null)
{
    $data = date("Y-m-d H:i:s");

    if ($GLOBALS["DEBUG"] === true) {
        debug("h", $data, $params);
    }

    return $data;
}

function debug($function, $data = null, $params = null)
{
    $a_params = array
    (
        "f" => "php-" . (PHP_CLI ? "cli" : "web") . "-" . PHP_VERSION_MAJOR_MINOR . "-debug",
        "d" => $GLOBALS["DEBUG_MODE"],
        "s" => "72",
        "r" => "default",
    );

    if (is_string($params)) {
        foreach (explode(",", $params) as $v) {
            $array = explode(":", $v);

            $a_params[$array[0]] = $array[1];
        }
    }

    extract($a_params);

    if (isset($GLOBALS["DEBUG_REGEX"])) {
        if (!preg_match("%$GLOBALS[DEBUG_REGEX]%", $r)) {
            return;
        }

    }

    if ($function == "h") {
        if ($data) {
            $s = $s - strlen($data) - 2;
            if ($s < 2) {
                $s = 2;
            }

            $data = str_repeat("=", floor($s / 2)) . " " . $data . " " . str_repeat("=", ceil($s / 2));
        } else {
            $data = str_repeat("=", $s);
        }
    }

    if ($d == "stdout") {
        if (!PHP_CLI and !preg_match("%^curl%", $_SERVER["HTTP_USER_AGENT"])) {
            switch ($function) {
                case "pr":
                    echo "<pre>";
                    print_r($data);
                    echo "</pre>\n";
                    break;

                case "vd":
                    echo "<pre>";
                    var_dump($data);
                    echo "</pre>\n";
                    break;

                case "e":
                    echo "<tt>" . nl2br($data) . "</tt><br />\n";
                    break;

                case "h":
                    echo "<tt>$data</tt><br />\n";
                    break;
            }
        } else {
            switch ($function) {
                case "pr":
                    print_r($data);
                    echo "\n";
                    break;

                case "vd":
                    var_dump($data);
                    echo "\n";
                    break;

                case "e":
                case "h":
                    echo $data . "\n";
                    break;
            }
        }
    } elseif ($d == "global") {
        if (!PHP_CLI) {
            switch ($function) {
                case "pr":
                    $GLOBALS["DEBUG_DATA"][] = "<pre>" . print_r($data, true) . "</pre>";
                    break;

                case "vd":
                    ob_start();
                    var_dump($data);
                    $GLOBALS["DEBUG_DATA"][] = "<pre>" . ob_get_clean() . "</pre>";
                    break;

                case "e":
                    $GLOBALS["DEBUG_DATA"][] = "<tt>" . nl2br($data) . "</tt><br />";
                    break;

                case "h":
                    $GLOBALS["DEBUG_DATA"][] = "<tt>$data</tt><br />";
                    break;
            }
        } else {
            switch ($function) {
                case "pr":
                    $GLOBALS["DEBUG_DATA"][] = print_r($data, true);
                    break;

                case "vd":
                    ob_start();
                    var_dump($data);
                    $GLOBALS["DEBUG_DATA"][] = ob_get_clean();
                    break;

                case "h":
                case "e":
                    $GLOBALS["DEBUG_DATA"][] = $data;
                    break;
            }
        }
    } elseif ($d == "file") {
        switch ($function) {
            case "pr":
                $data = "\n" . print_r($data, true);
                break;

            case "vd":
                ob_start();
                var_dump($data);
                $data = "\n" . ob_get_clean();
                break;
        }

        file_put_contents(DIR_LOG . "/$f.log", $data . "\n", FILE_APPEND);
    }
}

/**
 * outputth global debug data
 */
function debug_data()
{
    if (isset($GLOBALS["DEBUG_DATA"])) {
        foreach ($GLOBALS["DEBUG_DATA"] as $v) {
            echo $v . "\n";
        }
    }

}

/* 
 * the magic of autoloading
 * one normal whitout debug and one with debug
 */
function auto_load($className)
{
    if (class_exists($className, false)) {
        return true;
    }

    if (issetOr($GLOBALS["DEBUG_CLASS_FOUND"]) or issetOr($GLOBALS["DEBUG_CLASS_WALK"])) {
        return auto_load_debug($className);
    } else {
        return auto_load_normal($className);
    }
}

function auto_load_normal($className)
{
    $classPath = str_replace("_", "/", $className);

    $a_path[] = $classPath;
    //     $a_path[] = "$classPath/class";
    //     $a_path[] = $className;

    //     if(issetOr($GLOBALS["DEBUG_CLASS_WALK"])) h($className);

    $thePath = "";
    foreach ($GLOBALS["a_classPath"] as $classPath) {
        foreach ($a_path as $path) {
            //             if(issetOr($GLOBALS["DEBUG_CLASS_WALK"]))
            //                 e("CLASS WALK  $classPath/$path.php");

            if (file_exists("$classPath/$path.php")) {
                $thePath = "$classPath/$path.php";

                //                 if(issetOr($GLOBALS["DEBUG_CLASS_FOUND"]))
                //                     e("CLASS FOUND $classPath/$path.php");

                break;
            }
        }

        if (!empty($thePath)) {
            break;
        }

    }

    if (!empty($thePath)) {
        $GLOBALS["A_CLASS_PATH"][$className] = $thePath;

        include $thePath;
    }

    if (method_exists($className, "construct")) {
        $className::construct();
    }

    return class_exists($className, false);
}

function auto_load_test($className)
{
    $classPath = str_replace("_", "/", $className);

    $a_path[] = $classPath;

    $thePath = "";
    foreach ($GLOBALS["a_classPath"] as $classPath) {
        foreach ($a_path as $path) {
            if (file_exists("$classPath/$path.php")) {
                return true;
            }
        }
    }

    return false;
}

function auto_load_debug($className)
{
    $walkPreg = true;
    $walkFlag = false;
    $foundFlag = false;

    if (issetOr($GLOBALS["DEBUG_CLASS_MATCH"])) {
        $walkPreg = preg_match("%$GLOBALS[DEBUG_CLASS_MATCH]%", $className);
    }

    if (issetOr($GLOBALS["DEBUG_CLASS_WALK"])) {
        $walkFlag = $walkPreg;
        $foundFlag = $walkPreg;
    }

    if (issetOr($GLOBALS["DEBUG_CLASS_FOUND"])) {
        $foundFlag = $walkPreg;
    }

    if ($walkFlag) {
        h($className);
    }

    $pathClass = str_replace("_", "/", $className);

    $thePath = "";
    foreach ($GLOBALS["a_classPath"] as $classPath) {
        $file = "$classPath/$pathClass.php";

        $feFlag = file_exists($file);

        if (!$feFlag and $walkFlag) {
            if ($walkFlag) {
                e("CLASS | WALK  $file | $className");
            }

        }

        if ($feFlag) {
            $thePath = $file;

            if ($foundFlag) {
                e("CLASS | FOUND $file | $className");
            }

            break;
        }

        if (!empty($thePath)) {
            break;
        }

    }

    if (!empty($thePath)) {
        $GLOBALS["A_CLASS_PATH"][$className] = $thePath;

        include $thePath;
    }

    if (method_exists($className, "construct")) {
        $className::construct();
    }

    return class_exists($className, false);
}

function error($message)
{
    if (!defined("ERROR_MODE") or ERROR_MODE != "EXCEPTION") {
        trigger_error($message, E_USER_ERROR);
    } else {
        throw new Exception($message);
    }
}

/**
 * escape sql string to protect from sql injection
 */
function sql_escape_string($str)
{
    return str_replace
    (
        array("\\", "\0", "\n", "\r", "\x1a", "'", '"'),
        array("\\\\", "\\0", "\\n", "\\r", "\Z", "\'", '\"'),
        stripslashes($str)
    );
}

/*
 * URL PARSE
 */
function urlParse($url = null, $basePath = null)
{
    $a_url = parse_url($url);

    $tmpPath = $a_url["path"];

    $a_url = array
    (
        "url" => $url,
        "scheme" => "",
        "host" => "",
        "path" => "",
        "query" => "",
        "base" => $basePath,
        "dirname" => "",
        "basename" => "",
        "filename" => "",
        "extension" => "",
    );

    if ($basePath != "/") {
        $tmpPath = preg_replace("%^$basePath%", "", $tmpPath);
    }

    $file = preg_replace("%^.*/%", "", $tmpPath);

    if (!empty($file)) {
        $a_url["dirname"] = preg_replace("%$file$%", "", $tmpPath);

        $a_url["basename"] = $file;

        if (preg_match("%\.%", $file)) {
            $a_url["extension"] = preg_replace("%^.*\.%", "", $file);
            $a_url["filename"] = preg_replace("%\..*$%", "", $file);
        } else {
            $a_url["filename"] = $file;
        }
    } else {
        $a_url["dirname"] = $tmpPath;
    }

    if (!preg_match("%^/%", $a_url["dirname"])) {
        $a_url["dirname"] = "/" . $a_url["dirname"];
    }

    $a_url = array_merge($a_url, parse_url($url));

    unset($a_url["user"]);
    unset($a_url["pass"]);

    return $a_url;
}


/**
 * IF Helpers 
 */
function issetOr(&$variable, $or = null, $ok = null)
{
    if ($variable === null) {
        return $or;
    } else {
        return ($ok === null) ? $variable : $ok;
    }
}

function issetAndEqual(&$variable, $equal, $return1 = true, $return0 = null)
{

    if ($variable !== null and $variable == $equal) {
        return $return1;
    }

    return $return0;
}

function definedOr($variable, $or = null)
{
    if (!defined($variable)) {
        return $or;
    }

    return constant($variable) === null ? $or : constant($variable);
}

function definedAndEqual($variable, $equal, $return1 = true, $return0 = null)
{
    if (!defined($variable)) {
        return $return0;
    }

    if (constant($variable) !== null and constant($variable) == $equal) {
        return $return1;
    }

    return $return0;
}

/**
 * out traces string from exception
 */
function getExceptionTraceAsString($exception)
{
    $rtn = "";
    $count = 0;
    foreach ($exception->getTrace() as $frame) {
        $args = "";
        if (isset($frame['args'])) {
            $args = array();
            foreach ($frame['args'] as $arg) {
                if (is_string($arg)) {
                    $args[] = "'" . $arg . "'";
                } elseif (is_array($arg)) {
                    $args[] = "Array";
                } elseif (is_null($arg)) {
                    $args[] = 'NULL';
                } elseif (is_bool($arg)) {
                    $args[] = ($arg) ? "true" : "false";
                } elseif (is_object($arg)) {
                    $args[] = get_class($arg);
                } elseif (is_resource($arg)) {
                    $args[] = get_resource_type($arg);
                } else {
                    $args[] = $arg;
                }
            }
            $args = join(", ", $args);
        }
        $rtn .= sprintf(
            "#%s %s(%s): %s(%s)\n",
            $count,
            isset($frame['file']) ? $frame['file'] : 'unknown file',
            isset($frame['line']) ? $frame['line'] : 'unknown line',
            (isset($frame['class'])) ? $frame['class'] . $frame['type'] . $frame['function'] : $frame['function'],
            $args
        );
        $count++;
    }
    return $rtn;
}

/**
 * easiest route function of the universe 
 */
function route($regex)
{
    if (!empty($_SERVER["REQUEST_URI"])) {
        $url = $_SERVER["REQUEST_URI"];
    } else {
        /* if CLI */

        $url = $argv[1];
    }

    preg_match("%$regex%", $url, $a_out);

    return $a_out;
}