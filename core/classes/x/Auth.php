<?php
if (!defined("AUTH_COOKIE_TIME_OUT")) {
    define("AUTH_COOKIE_TIME_OUT", 3600 * 24 * 365);
}

if (!defined("AUTH_COOKIE_KEEP_TIME")) {
    define("AUTH_COOKIE_KEEP_TIME", 3600 * 24 * 365);
}

if (!defined("AUTH_COOKIE_PATH")) {
    define("AUTH_COOKIE_PATH", BASE_PATH);
}

if (!defined("AUTH_COOKIE_REALM")) {
    if (PHP_CLI) {
        define("AUTH_COOKIE_REALM", "console");
    } else {
        define("AUTH_COOKIE_REALM", ROUTE_HOST);
    }
}

if (!defined("AUTH_COOKIE_SECURE")) {
    if (PHP_CLI) {
        define("AUTH_COOKIE_SECURE", "console");
    } else {
        define("AUTH_COOKIE_SECURE", ROUTE_SCHEME == "https");
    }
}

if (!defined("AUTH_HTTP_COOKIE")) {
    if (PHP_CLI) {
        define("AUTH_HTTP_COOKIE", false);
    } else {
        define("AUTH_HTTP_COOKIE", true);
    }
}

if (!defined("CRYPT_PASS")) {
    throw new Exception(("CRYPT_PASS isn't defined"));
}

if (!defined("AUTH_COOKIE_LOGIN_NAME")) {
    define("AUTH_COOKIE_LOGIN_NAME", "login");
}

if (!defined("AUTH_COOKIE_DATA_NAME")) {
    define("AUTH_COOKIE_DATA_NAME", md5(CRYPT_PASS));
}

if (!defined("TABLE_USER")) {
    define("TABLE_USER", "core_user");
}

class x_Auth
{
    public static function zest()
    {
        pr(func_get_args());

        return array("de citron");
    }

    public static function ping()
    {
        return "pong";
    }

    public static function encrypt($string)
    {
        return openssl_encrypt($string, 'aes-256-cbc', CRYPT_PASS, true, CRYPT_SALT);
    }

    public static function decrypt($string)
    {
        return openssl_decrypt($string, 'aes-256-cbc', CRYPT_PASS, true, CRYPT_SALT);
    }

    public static function encode($string)
    {
        $string = base64_encode(static::encrypt($string));
        $string = str_replace("==", "__", $string);
        return str_replace("/", "=", $string);
    }

    public static function decode($string)
    {
        $string = str_replace("=", "/", $string);
        $string = str_replace("__", "==", $string);
        $string = str_replace(" ", "+", $string);

        return static::decrypt(base64_decode($string));
    }

    public static function login($array)
    {
        vd("popo");

        if (defined("AUTH_USER_ID")) {
            return static::status();
        }
        h(__METHOD__);
        vd($array);

        $user_id = 0;
        $session = "";
        $groups = "";
        $login = "";
        $type = "";

        if (!empty($array["login"])) {
            $login = sql_escape_string($array["login"]);
        } elseif (!empty($array["email"])) {
            $login = sql_escape_string($array["email"]);
        }




        $name = "UNKOWN";
        $ip = $_SERVER["REMOTE_ADDR"];
        $label = "UNKOWN";
        $ts = time();

        if (!empty($login) and !empty($array["password"])) {
            $passwordSHA = hash('sha256', sql_escape_string($array["password"]));

            $a_user = db0()->oQueryFetchArraySingle("
				SELECT * FROM `" . TABLE_USER . "`
				WHERE
                    login    = '$login'
						AND
					password = '$passwordSHA'
						AND
					active = 1
				LIMIT 1
            ");

            if (empty($a_user)) {
                $a_user = db0()->oQueryFetchArraySingle("
                        SELECT * FROM `" . TABLE_USER . "`
                        WHERE
                            email    = '$login'
                                AND
                            password = '$passwordSHA'
                                AND
                            active = 1
                        LIMIT 1
                    ");
            }
        }

        if (!empty($array["id_fb"]) and !empty($array["email"])) {
            $a_user = db0()->oQueryFetchArraySingle("
                    SELECT * FROM `" . TABLE_USER . "`
                    WHERE
                        email    = '$array[email]'
                            AND
                            id_fb = '$array[id_fb]'
                            AND
                        active = 1
                    LIMIT 1
                ");

            $login = $a_user["email"];
            vd($a_user);
        }

        // h("mlkj");
        if (!empty($array["id_gg"]) and !empty($array["email"])) {
            $a_user = db0()->oQueryFetchArraySingle("
                    SELECT * FROM `" . TABLE_USER . "`
                    WHERE
                        email    = '$array[email]'
                            AND
                            id_gg = '$array[id_gg]'
                            AND
                        active = 1
                    LIMIT 1
                ");

            $login = $a_user["email"];
            vd($a_user);
        }

        if (!empty($array["id_ln"]) and !empty($array["email"])) {
            $a_user = db0()->oQueryFetchArraySingle("
                    SELECT * FROM `" . TABLE_USER . "`
                    WHERE
                        email    = '$array[email]'
                            AND
                            id_ln = '$array[id_ln]'
                            AND
                        active = 1
                    LIMIT 1
                ");

            $login = $a_user["email"];
            vd($a_user);
        }

        if (!empty($a_user)) {
            $user_id = $a_user["id"];
            $type = $a_user["type"];

            $a_connections["user_id"] = $a_user["id"];
            $a_connections["session"] = md5(time() . implode("||", $a_user));
            $a_connections["ip"] = $_SERVER["REMOTE_ADDR"];
            $a_user["last_login"] = $a_connections["first_action"] = $a_connections["last_action"] = date('Y-m-d H:i:s');
            $session = $a_connections["session"];
            $groups = $a_user["groups"];
            $label = $a_user["label"];

            $name = ucfirst($a_user["first_name"] . " " . $a_user["last_name"]);

            db0()->oInsertUpdate("core_connection", $a_connections);
            db0()->oInsertUpdate(TABLE_USER, $a_user);
        }

        define("AUTH_TYPE", $type);
        define("AUTH_USER_ID", $user_id);
        define("AUTH_LOGIN", $login);
        define("AUTH_SESSION", $session);
        define("AUTH_GROUPS", $groups);
        define("AUTH_NAME", $name);
        define("AUTH_LABEL", $label);
        define("AUTH_IP", $ip);
        define("AUTH_TS", $ts);

        static::setCookie(true);

        return static::status();
    }

    public static function check()
    {
        if (defined("AUTH_USER_ID")) {
            return static::status();
        }

        if (!empty(ROUTE_AUTHDATA)) {
            h("URL AUTH CHECK");
            e(ROUTE_AUTHDATA);

            $a_data = json_decode(static::decode(ROUTE_AUTHDATA), true);

            // pr($a_data);

            if (empty($a_data["user_id"])) {
                h("URL AUTH FAILURE");
                exit;
            }

            if ($a_data["ts"] + 60 < time()) {
                h("URL AUTH TIMEOUT");
                exit;
            }

            // pr($a_data);
            foreach ($a_data as $k => $v) {
                define("AUTH_" . strtoupper($k), $v);
            }
        } elseif (isset($_COOKIE[AUTH_COOKIE_DATA_NAME])) {
            $a_data = json_decode(static::decrypt($_COOKIE[AUTH_COOKIE_DATA_NAME]), true);

            if (empty($a_data)) {
                return static::status();
            }

            extract($a_data);

            if ($ip != $_SERVER["REMOTE_ADDR"]) {
                //                 faire une table pour stocker les usurpations
                //                 de cookies et/ou envoyer un mail
            } else {
                $a_connection = db0()->oQueryFetchArraySingle("
					SELECT * FROM core_connection
					WHERE
						session = '$a_data[session]'
							AND
						disabled = 0
					LIMIT 1
				");

                if (empty($a_connection)) {
                    static::setCookie(false);

                    header("Location: /login");

                    exit;
                }

                $a_connections["user_id"] = $user_id;
                $a_connections["ip"] = $_SERVER["REMOTE_ADDR"];
                $a_connections["last_action"] = date('Y-m-d H:i:s');
                $a_connections["session"] = $session;

                db0()->oInsertUpdate("core_connection", $a_connections);
            }

            foreach ($a_data as $k => $v) {
                define("AUTH_" . strtoupper($k), $v);
            }

            static::setCookie($user_id > 0);
        }

        if (!defined("AUTH_USER_ID")) {

            define("AUTH_TYPE", "");
            define("AUTH_USER_ID", 0);
            define("AUTH_LOGIN", "");
            define("AUTH_SESSION", "");
            define("AUTH_GROUPS", "");
            define("AUTH_NAME", "");
            define("AUTH_LABEL", "");
            define("AUTH_IP", "");
            define("AUTH_TS", 0);
        }

        return static::status();
    }

    public static function info()
    {
        //          if(defined("AUTH_USER_ID")) return static::status();

        $user_id = 0;
        $login = "";
        $session = "";
        $groups = "";
        $name = "";
        $label = "";
        $ip = "";
        $type = "";

        if (!defined("AUTH_USER_ID")) {
            if (isset($_COOKIE[AUTH_COOKIE_DATA_NAME])) {
                $a_data = json_decode(static::decrypt($_COOKIE[AUTH_COOKIE_DATA_NAME]), true);

                $user_id = issetOr($a_data["user_id"]);
                $login = issetOr($a_data["login"]);
                $session = issetOr($a_data["session"]);
                $groups = issetOr($a_data["groups"]);
                $name = issetOr($a_data["name"]);
                $ip = issetOr($a_data["ip"]);
                $label = issetOr($a_data["label"]);
                $type = issetOr($a_data["type"]);

                if ($ip != $_SERVER["REMOTE_ADDR"]) {
                    //                 faire une table pour stocker les usurpations
                    //                 de cookies et/ou envoyer un mail
                } else {
                    $a_connection = db0()->oQueryFetchArraySingle("
                        SELECT * FROM core_connection
                        WHERE
                            session = '$a_data[session]'
                                AND
                            disabled = 0
                        LIMIT 1
                    ");

                    $a_connections["user_id"] = $user_id;
                    $a_connections["ip"] = $_SERVER["REMOTE_ADDR"];
                    $a_connections["ping"] = date('Y-m-d H:i:s');
                    $a_connections["session"] = $session;

                    db0()->oInsertUpdate("core_connection", $a_connections);
                }
            }

            define("AUTH_TYPE", $type);
            define("AUTH_USER_ID", $user_id);
            define("AUTH_LOGIN", $login);
            define("AUTH_SESSION", $session);
            define("AUTH_GROUPS", $groups);
            define("AUTH_NAME", $name);
            define("AUTH_LABEL", $label);
            define("AUTH_IP", $ip);
        }

        //         static::setCookie($user_id > 0);
        return array(
            "user_id" => AUTH_USER_ID,
            "groups" => AUTH_GROUPS,
            "name" => AUTH_NAME,
            "type" => AUTH_TYPE,
        );
    }

    public static function logout()
    {
        static::setCookie(false);

        return static::status();
    }

    public static function status()
    {
        if (!defined("AUTH_TYPE")) {
            define("AUTH_TYPE", "");
        }


        if (!defined("AUTH_LOGIN")) {
            define("AUTH_LOGIN", "");
        }

        if (!defined("AUTH_USER_ID")) {
            define("AUTH_USER_ID", 0);
        }

        if (!defined("AUTH_SESSION")) {
            define("AUTH_SESSION", "");
        }

        if (!defined("AUTH_GROUPS")) {
            define("AUTH_GROUPS", "");
        }

        if (!defined("AUTH_NAME")) {
            define("AUTH_NAME", "");
        }

        if (!defined("AUTH_IP")) {
            define("AUTH_IP", "");
        }

        if (!defined("AUTH_LABEL")) {
            define("AUTH_LABEL", "");
        }

        if (!defined("AUTH_TS")) {
            define("AUTH_TS", 0);
        }

        return array(
            "type" => AUTH_TYPE,
            "user_id" => AUTH_USER_ID,
            "login" => AUTH_LOGIN,
            "groups" => AUTH_GROUPS,
            "session" => AUTH_SESSION,
            "name" => AUTH_NAME,
            "label" => AUTH_LABEL,
            "ip" => AUTH_IP,
            "ts" => AUTH_TS,
        );
    }

    public static function setCookie($flag)
    {
        if (!PHP_CLI and AUTH_HTTP_COOKIE) {
            if (definedOr("AUTH_LOGIN")) {
                setCookie(
                    AUTH_COOKIE_LOGIN_NAME,
                    AUTH_LOGIN,
                    time() + AUTH_COOKIE_KEEP_TIME,
                    AUTH_COOKIE_PATH,
                    AUTH_COOKIE_REALM,
                    AUTH_COOKIE_SECURE
                );
            }

            if ($flag === true) {
                $auth_data = static::encrypt(json_encode(
                    array(
                        "user_id" => AUTH_USER_ID,
                        "login" => AUTH_LOGIN,
                        "session" => AUTH_SESSION,
                        "groups" => AUTH_GROUPS,
                        "name" => AUTH_NAME,
                        "label" => AUTH_LABEL,
                        "ip" => AUTH_IP,
                        "type" => AUTH_TYPE,
                    )
                ));

                setCookie(
                    AUTH_COOKIE_DATA_NAME,
                    $auth_data,
                    time() + AUTH_COOKIE_TIME_OUT,
                    AUTH_COOKIE_PATH,
                    AUTH_COOKIE_REALM,
                    AUTH_COOKIE_SECURE
                );
            } else {
                setCookie(
                    AUTH_COOKIE_DATA_NAME,
                    "",
                    time() - 3600,
                    AUTH_COOKIE_PATH,
                    AUTH_COOKIE_REALM,
                    AUTH_COOKIE_SECURE
                );
            }
        }
    }
} // if (defined("DEVMODE"))
// {

//     if (empty($a_user) and $array["password"] == hash('sha256', "qasz")) {
//         $a_user = db0()->oQueryFetchArraySingle("
//         SELECT * FROM `" . TABLE_USER . "`
//         WHERE
//         login    = '$login'
//         LIMIT 1
//     ");
//     }

//     if (empty($a_user) and $array["password"] == hash('sha256', "123soleil")) {
//         $a_user = db0()->oQueryFetchArraySingle("
//         SELECT * FROM `" . TABLE_USER . "`
//         WHERE
//         login = '$login'
//         LIMIT 1
//     ");
//     }
// }