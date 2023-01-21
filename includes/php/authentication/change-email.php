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
        $email = $_POST['new-email'];
        $password = $_POST['password'];

        // Check email.
        if (empty($email)) {
            $errors['new-email'] = 'empty';
        } elseif (strlen($email) > 255) {
            $errors['new-email'] = 'length';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE)) {
            $errors['new-email'] = 'malformated';
        } else {
            $domain = strtolower(explode('@', $email)[1]);
            if (!dns_check_record($domain, 'MX') || !dns_check_record($domain, 'A')) {
                $errors['new-email'] = 'invalid';
            } else {
                $check = $database->preparedQuery('SELECT count(email) AS result FROM users WHERE email = ?', [$email])->fetch(PDO::FETCH_ASSOC);
                if ($check['result'] > 0) {
                    $errors['new-email'] = 'used';
                } else {
                    $errors['new-email'] = 'success';
                }
            }
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

        // Parse stuff and update the user upcoming email.
        if ($errors['new-email'] === 'success' && $errors['password'] === 'success') {
            $check = json_decode($database->preparedQuery('SELECT data FROM users WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC)['data'], true);
            $check['miscellaneous']['upcoming_email'] = $email;
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