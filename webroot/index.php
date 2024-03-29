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

// define("PRODUCTION", true);

/* get dynamicaly where the project root directory is located */
define("DIR_ROOT", realpath(dirname(__FILE__) . "/../.."));

/* load the startup file */
include DIR_ROOT . "/app/startup.php";

/* date debug output */
d();


if ($a = route("^/test")) {
    echo "test";
    exit;
}



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

        if ($a[1] == "JSON") {
            echo json_encode($data);
        } elseif ($a[1] == "RAW") {
            echo ($data);
        }

        exit;
    } catch (Exception $e) {
        h("ERROR");

        h("API ERROR");
        if (!PRODUCTION) {
            pr(getExceptionTraceAsString($e));
            h("$class::$method");
            pr($a_params);

            if ($a[1] == "JSON") {
                $data = [
                    "message" => "error",
                    "data" => $e->getMessage() . "\n\n" . getExceptionTraceAsString($e),
                ];
                echo json_encode($data);
            } elseif ($a[1] == "RAW") {
                echo ("<pre>\n" . $e->getMessage() . "\n\n" . getExceptionTraceAsString($e) . "</pre>\n");
            }

        } else {
            if ($a[1] == "JSON") {
                $data = [
                    "message" => "error",
                    "data" => "error",
                ];

                echo json_encode($data);
            } elseif ($a[1] == "RAW") {
                echo ("error");
            }
            exit;
        }
    }

}



// Return 404 not found response header if nothing to do more
http_response_code(404);