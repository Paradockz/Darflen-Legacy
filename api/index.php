<?php
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
error_reporting(E_ERROR);
try {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $database = prepare_database();
        $errors = ['code' => 'ready', 'api' => API_LINK, 'website' => ROOT_LINK];
    } else {
        // There's an error.
        $errors = ['code' => 'fail', 'error' => '400', 'cause' => 'Invalid request method'];
    }
} catch (Exception $error) {
    // There's an error.
    $errors = ['code' => 'fail', 'error' => '400', 'cause' => $error->getMessage()];
}
ob_clean();
echo json_encode($errors);