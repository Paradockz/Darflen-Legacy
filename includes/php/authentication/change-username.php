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
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Check username.
        if (empty($username) && $username != 0) {
            $errors['username'] = 'empty';
        } elseif (strlen($username) > 24 || strlen($username) < 2) {
            $errors['username'] = 'length';
        } elseif (!preg_match('/^[A-Za-z0-9]+(?:[_-][A-Za-z0-9]+)*$/', $username)) {
            $errors['username'] = 'malformated';
        } else {
            $errors['username'] = 'success';
        }

        // Check password.
        if (empty($password)) {
            $errors['password'] = 'empty';
        } else {
            $yourself = get_user_info_from_token($token);
            $id = $yourself['id'];
            $check = $database->preparedQuery('SELECT password FROM users WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC);
            if (!isset($check['password']) || !password_verify($password, $check['password'])) {
                $errors['password'] = 'invalid';
            } else {
                $errors['password'] = 'success';
            }
        }

        // Parse stuff.
        if ($errors['username'] === 'success' && $errors['password'] === 'success') {
            $check = json_decode($database->preparedQuery('SELECT data FROM users WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC)['data'], true);
            $check['username'] = $username;
            $check = json_encode($check);
            $database->preparedQuery('UPDATE users SET data=? WHERE id = ?', [$check, $id]);
            $errors = ['code' => 'success'];
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