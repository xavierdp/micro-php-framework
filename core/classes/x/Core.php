<?php

class x_Core
{
    public static function init()
    {
        $className = get_called_class();

//          h("$className::".__FUNCTION__."()","r:bootstrap");

        set_error_handler("$className::errorHandler", error_reporting());
        set_exception_handler("$className::exceptionHandler");
        register_shutdown_function("$className::shutdownHandler");

        ini_set("html_errors", !(isset($_SERVER["HTTP_USER_AGENT"]) and preg_match("%^curl%", $_SERVER["HTTP_USER_AGENT"])));

        ini_set("display_errors", !PRODUCTION);
    }

    public static function errorHandler($errNo, $errStr, $errFile, $errLine)
    {
        throw new ErrorException($errStr, $errNo, $errNo, $errFile, $errLine);
    }

    public static function exceptionHandler()
    {
//          h(get_called_class()."::".__FUNCTION__."()","r:bootstrap");

        static::errorCollector(func_get_args());

//         exit;
    }

    public static function errorCollector($a_arg)
    {
//          h(get_called_class()."::".__FUNCTION__."()","r:bootstrap");

        if (!is_object($a_arg[0]))
        {
            return false;
        }

        $a_errors = array
            (
            0     => "E_EXCEPTION",
            1     => "E_ERROR",
            2     => "E_WARNING",
            4     => "E_PARSE",
            8     => "E_NOTICE",
            16    => "E_CORE_ERROR",
            32    => "E_CORE_WARNING",
            64    => "E_COMPILE_ERROR",
            128   => "E_COMPILE_WARNING",
            256   => "E_USER_ERROR",
            512   => "E_USER_WARNING",
            1024  => "E_USER_NOTICE",
            2048  => "E_STRICT",
            4096  => "E_RECOVERABLE_ERROR",
            8192  => "E_DEPRECATED",
            16384 => "E_USER_DEPRECATED",
            30719 => "E_ALL",
        );

        $a_trace = $a_arg[0]->getTrace();
        $errStr  = $a_arg[0]->getMessage();
        $errNo   = $a_arg[0]->getCode();
        $errType = $a_errors[$errNo];

        $GLOBALS["ERRORS"]["string"] = "[XXX] : $errType | $errStr";

        foreach ($a_trace as $k => $v)
        {
            foreach (array("class", "type", "function", "file", "line") as $vv)
            {
                if (!isset($v[$vv]))
                {
                    $v[$vv] = "";
                }
            }

            $GLOBALS["ERRORS"][] = sprintf("[%03d]", $k) . " : ($v[class]$v[type]$v[function]) $v[file]:$v[line]";
        }

        if (isset($a_arg[4]) and preg_match("/^mysqli::query/", $errStr))
        {
            foreach (explode("\n", $a_arg[4]["query"]) as $k => $v)
            {
                $v = trim($v);

                $a_query[] = $v;
            }

            $GLOBALS["ERRORS"][] = print_r($a_query, true);
        }
    }

    public static function handle()
    {

    }

    public static function shutdownHandler()
    {
//          h(get_called_class()."::".__FUNCTION__."()","r:bootstrap");

        if (!isset($GLOBALS["ERRORS"]))
        {
            exit;
        }

        $f_log = DIR_LOG . "/php-" . (PHP_CLI ? "cli" : "web") . "-" . PHP_VERSION_MAJOR_MINOR . "-debug";

        h();
        error_log(str_repeat("=", 80) . "\n", 3, $f_log);
        foreach ($GLOBALS["ERRORS"] as $v)
        {
            error_log("$v\n", 3, $f_log);
            e($v);
        }
        error_log(str_repeat("=", 80) . "\n", 3, $f_log);
        h();

        exit;
    }
}
