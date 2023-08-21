<?php
$GLOBALS["DEBUG"] = true;
$GLOBALS["DEBUG_MODE"] = "file";
// $GLOBALS["DEBUG_MODE"] = "stdout";
// $GLOBALS["DEBUG_MODE"] = "global";

$GLOBALS["DEBUG_REGEX"] = "^[^b]";
$GLOBALS["ERROR_LOG"] = true;
// $GLOBALS["DEBUG_SQL"] = true;
// $GLOBALS["DEBUG_CLASS_WALK"]  = true;
// $GLOBALS["DEBUG_CLASS_FOUND"] = true;


/* get dynamicaly where the project root directory is located */
define("DIR_ROOT", realpath(dirname(__FILE__) . "/../.."));

/* load the startup file */
include DIR_ROOT . "/app/startup.php";

/* date debug output */
d();

/**
 * API AJAX
 * 
 * THE INPUT IS JSON
 *
 * THE OUTPUT IS JSON OR RAW (like HTML)
 * {
 *  message: "ok",
 *  data: data
 * }
 *
 */

if ($a = route("^/JSON/(JSON|RAW)/([^/]*)/([^/]*)")) {
    $class = $a[2];
    $method = $a[3];
    $a_params = [];

    if (!empty($_GET)) {
        $a_params = $_GET;
    } else if (!empty($_POST)) {
        $a_params = $_POST;
    } else {
        $a_params = json_decode(trim(file_get_contents("php://input")), true);
    }

    // vd($a_params);

    try {
        if ($a[1] == "JSON") {
            $data = [
                "message" => "ok",
                "data" => call_user_func_array("$class::$method", [$a_params]),
            ];
        } else {
            $data = call_user_func_array("$class::$method", [$a_params]);
        }

    } catch (Exception $e) {
        $data = [
            "message" => "erreur",
            "data" => $e->getMessage() . "\n\n" . getExceptionTraceAsString($e),
        ];

        h("ERROR");

        if ($GLOBALS["DEBUG_MODE"] != "sdout" or PRODUCTION == false) {
            pr(getExceptionTraceAsString($e));
            h("$class::$method");
            pr($a_params);
        } else {
            h("API ERROR");
        }
    }

    if ($a[1] == "JSON") {
        echo json_encode($data);
    } elseif ($a[1] == "RAW") {
        echo ($data);
    }

    exit;
}



// Return 404 not found response header if nothing to do more
http_response_code(404);