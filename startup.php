<?php
ini_set('display_errors', 1);

/* UNIVAP est ne nom de l'utilisateur système pour l'application PHP */
define("UNIVAPP", basename(DIR_ROOT));


/* Répertoires de base */
define("DIR_SAV", DIR_ROOT . "/sav");
define("DIR_TMP", DIR_ROOT . "/tmp");
define("DIR_CACHE", DIR_ROOT . "/cache");
define("DIR_LOG", DIR_ROOT . "/log");
define("DIR_APP", DIR_ROOT . "/app");
define("DIR_ETC", DIR_ROOT . "/etc");
define("DIR_WEBROOT", DIR_APP . "/webroot");
define("DIR_LIB", DIR_APP . "/lib");
define("DIR_CORE", DIR_APP . "/core");

/* Bostrap XFW */
include DIR_CORE . "/base/bootstrap.php";

/* Autoload COMOSER */
require DIR_APP . "/lib/vendor/autoload.php";

/* Répéertoire de CLASS à parcourir par l'autoload XFW */
$GLOBALS["a_classPath"][] = DIR_APP . "/classes";
$GLOBALS["a_classPath"][] = DIR_CORE . "/classes";
$GLOBALS["a_classPath"][] = DIR_APP . "/webroot/static/views";

/* E_ALL pour ne rien laisser passer */
// error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// error_reporting(E_ALL & ~E_DEPRECATED);
error_reporting(E_ALL);

/* les erreur sont transorfée en exception */
set_error_handler(function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, $severity, $severity, $file, $line);
});


/* Graines de cryptage pour le cookie d'authentification  */
define("CRYPT_PASS", "mlaejhgbcuyeziureoixhjklxhkjcxvhkhj");
define("CRYPT_SALT", "5678123456781234");


/* Mouteur de template simple */
include DIR_CORE . "/lib/html5.php";


/* Configuratio nde la base de données */
function &db0()
{
    return x_Mysql::multiton(
        array(
            "user"   => UNIVAPP,
            "name"   => UNIVAPP,
            "passwd" => trim(file_get_contents(DIR_ETC . "/mysql/localhost/passwd")),
        )
    );
}

define("DOMAIN","");

