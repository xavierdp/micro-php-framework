<?php

class x_Route
{
    public static $a_url = null;
    public static $routeSet = false;

    // Main method to be used to defined route and call a controller in callback
    public static function set(string $route, callable $callback)
    {
        // If a route has already been set, skip
        if (static::$routeSet === true) {
            return false;
        }

        $realRoute = preg_replace('/{(.*?)}/', '(.*)', $route); // Convert builded route to regex route
        $a = r::path('^' . $realRoute . '$'); // Check path is the current target

        if (!$a) {
            $a = r::path('^' . $realRoute . '/$'); // Check with '/' at the end
            if (!$a) {
                return false;
            }
        }

        preg_match_all('/{(.*?)}/', $route, $matches); // Get all params keys
        $params = [];

        if (isset($matches[1])) {
            foreach ($matches[1] as $k => $v) {
                if (isset($a[$k + 1])) {
                    $params[$v] = $a[$k + 1];
                } // Set route params values to each defined params
            }
        }

        
        static::$routeSet = true;
        echo call_user_func($callback, static::$a_url, $params); // Call the route callback with args raw request data and params key/value
    }

    // Used to redirect to another url
    public static function redirect(string $url)
    {
        return header('Location: ' . $url);
    }



    public static function getIdLast()
    {
        if (preg_match("%/([0-9^/])*$%", ROUTE_PATH, $a)) {
            return $a[1];
        }
    }

    public static function fork($a_in)
    {
        foreach ($a_in as $k => $v) {
            if (preg_match("%$k%", $_SERVER["REQUEST_URI"])) {
                $_SERVER["REQUEST_URI"] = preg_replace("%$k%", $v, $_SERVER["REQUEST_URI"]);

                return true;
            }
        }
    }

    public static function __callStatic($method, $args = null)
    {
        if (in_array($method, array("url", "scheme", "host", "path", "full", "query", "base", "dirname", "basename", "filename", "extension", "user", "pass"))) {
            static::parseUrl();

            if ($args == null) {
                return static::$a_url[$method];
            }

            $patern = "~$args[0]~";

            if (isset($args[1])) {
                $patern .= $args[1];
            }

            preg_match($patern, static::$a_url[$method], $a_match);

            return $a_match;
        }
    }

    public static function query($a_in = [])
    {
        static::parseUrl($a_in);
        parse_str(ROUTE_QUERY, $get);
        return $get;
    }

    public static function parseUrl($a_in = [])
    {
        foreach ($a_in as $k => $v) {
            if (preg_match("%$k%", $_SERVER["REQUEST_URI"])) {
                $_SERVER["REQUEST_URI"] = preg_replace("%$k%", $v, $_SERVER["REQUEST_URI"]);

                break;
            }
        }

        if (!empty(static::$a_url)) {
            return static::$a_url;
        }

        $url = "";
        if (PHP_CLI) {
            if (!empty($_SERVER["argv"][1]) and preg_match("%^http%", $_SERVER["argv"][1])) {
                $url = $_SERVER["argv"][1];
            }
        } else {
            $url = (empty($_SERVER["HTTPS"]) ? "http" : "https") . "://" . $_SERVER["HTTP_HOST"] . (($_SERVER["SERVER_PORT"] != "443" and $_SERVER["SERVER_PORT"] != "80") ? ":" . $_SERVER["SERVER_PORT"] : "") . $_SERVER["REQUEST_URI"];
        }

        if (empty($url)) {
            die("No URL given !\n");
        }

        $authkey  = "";
        $authdata = "";
        $url      = urldecode($url);

        if (preg_match("%/([0-9a-z]{32})=([^/]*)/%", $url, $a)) {
            // h("URLAUTH");
            // pr($a);

            $url = str_replace($a[0], "/", $url);

            // e($url);
            if (defined("AUTH_COOKIE_DATA_NAME")) {
                if (AUTH_COOKIE_DATA_NAME == $a[1]) {
                    $authkey  = $a[1];
                    $authdata = $a[2];
                }
            }
        }
        // exit;

        $basePath = definedOR("BASE_PATH", "/");

        $a_url = parse_url($url);

        $tmpPath = $a_url["path"];

        $a_url = array(
            "url"       => $url,
            "scheme"    => "",
            "host"      => "",
            "path"      => "",
            "full"      => "",
            "query"     => "",
            "base"      => $basePath,
            "dirname"   => "",
            "basename"  => "",
            "filename"  => "",
            "extension" => "",
            "authkey"   => $authkey,
            "authdata"  => $authdata,
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
                $a_url["filename"]  = preg_replace("%\..*$%", "", $file);
            } else {
                $a_url["filename"] = $file;
            }
        } else {
            $a_url["dirname"] = $tmpPath;
        }

        if (!preg_match("%^/%", $a_url["dirname"])) {
            $a_url["dirname"] = "/" . $a_url["dirname"];
        }

        $a_url         = array_merge($a_url, parse_url($url));
        $a_url["full"] = $a_url["path"];
        $a_url["path"] = $a_url["dirname"] . $a_url["basename"];

//         unset($a_url["user"]);
        //         unset($a_url["pass"]);

//         ROUTE_URL
        //         ROUTE_SCHEME
        //         ROUTE_HOST
        //         ROUTE_PATH
        //         ROUTE_QUERY
        //         ROUTE_BASE
        //         ROUTE_DIRNAME
        //         ROUTE_BASENAME
        //         ROUTE_FILENAME
        //         ROUTE_EXTENSION

        foreach ($a_url as $k => $v) {
//             e("ROUTE_".strtoupper($k));
            define("ROUTE_" . strtoupper($k), $v);
        }

        return static::$a_url = $a_url;
    }
}
