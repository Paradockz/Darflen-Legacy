<?php
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
error_reporting(0);
try {
    require_once DOCUMENT_ROOT . '/includes/php/database.php';
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $errors = ['code' => 'fail', 'error' => '404', 'cause' => 'page does not exist'];
    } else {
        // There's an error.
        $errors = ['code' => 'fail', 'error' => 'Invalid request method'];
    }
} catch (Exception $error) {
    // There's an error.
    $errors = ['code' => 'fail', 'error' => $error->getMessage()];
}
ob_clean();
echo json_encode($errors);