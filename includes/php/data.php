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
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $type = $_POST['type'];
        
        switch ($type) {
            case 'links':
                $errors = [
                    'code' => 'success',
                    'static' => STATIC_LINK,
                    'link' => ROOT_LINK,
                    'website' => WEBSITE
                ];
                break;
            default:
                // There's an error.
                $errors = ['code' => 'fail', 'error' => 'Invalid request type'];
                break;
        }
    }
} catch (Exception $error) {
    // There's an error.
    $errors = ['code' => 'fail', 'error' => $error->getMessage()];
}
echo json_encode($errors);