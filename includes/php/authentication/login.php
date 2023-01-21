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
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Check email.
        if (empty($email)) {
            $errors['email'] = 'empty';
        } else {
            $check = $database->preparedQuery('SELECT email,password FROM users WHERE email = ?', [$email])->fetch(PDO::FETCH_ASSOC);
            if (!isset($check['password'])) {
                $errors['email'] = 'invalid';
            } else {
                $errors['email'] = 'success';
            }
        }

        // Check password.
        if (empty($password)) {
            $errors['password'] = 'empty';
        } elseif(!isset($check['password']) || !password_verify($password,$check['password'])) {
            $errors['password'] = 'invalid';
        } else {
            $errors['password'] = 'success';
        }

        // Parse stuff and log the user.
        if ($errors['email'] === 'success' && $errors['password'] === 'success') {
            $token = base64_encode(openssl_random_pseudo_bytes(32));
            $agent = $_SERVER['HTTP_USER_AGENT'];
            $jsonData = json_encode([
                'agent' => $agent,
                'miscellaneous' => [
                    'creation_time' => time()
                ]
            ]);
            $id = $database->preparedQuery('SELECT id FROM users WHERE email = ?', [$email])->fetch(PDO::FETCH_ASSOC)['id'];
            $expiration = time() + (3600 * 24 * 30);
            $database->preparedQuery('INSERT INTO tokens (account,token,expiration, data) VALUES (?,?,?,?)', [$id, $token, $expiration, $jsonData]);
            setcookie('token', $token, $expiration, '/', '', true, false);
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