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
        $database = prepare_database();
        $errors = ['code' => 'ready'];
        $token = $_COOKIE['token'];
        $yourself = get_user_info_from_token($token);
        $check = $database->preparedQuery('SELECT count(id) AS result FROM tokens WHERE token = ?', [$token])->fetch(PDO::FETCH_ASSOC);
        if ($check > 0) {
            $database->preparedQuery('DELETE FROM tokens WHERE token = ?', [$token]);
            $database->preparedQuery('DELETE FROM status WHERE account = ?', [$yourself['id']]);
            setcookie('token','',time() - 36000, '/');
            $errors['code'] = 'success';
        }
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