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
        $current_password = $_POST['current-password'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm-password'];
        $yourself = get_user_info_from_token($token);
        $id = $yourself['id'];
        $check = $database->preparedQuery('SELECT password FROM users WHERE id = ?',[$id])->fetch(PDO::FETCH_ASSOC);
        
        // Check current password.
        if (empty($current_password)) {
            $errors['current-password'] = 'empty';
        } elseif (!password_verify($current_password, $check['password'])) {
            $errors['current-password'] = 'invalid';
        } else {
            $errors['current-password'] = 'success';
        }

        // Check password.
        if (empty($password)) {
            $errors['password'] = 'empty';
            $errors['confirm-password'] = 'success';
        } else {
            $errors['confirm-password'] = 'success';
            if (strlen($password) < 6 || strlen($password) > 255) {
                $errors['password'] = 'length';
            }  elseif (preg_match('/(\w)\1{3,}/', $password)) {
                $errors['password'] = 'malformated';
            } elseif (password_verify($password, $check['password'])) {
                $errors['password'] = 'copy';
            } else {
                $errors['password'] = 'success';
                if (empty($confirm_password)) {
                    $errors['confirm-password'] = 'empty';
                } elseif($password != $confirm_password) {
                    $errors['confirm-password'] = 'invalid';
                } else {
                    $errors['confirm-password'] = 'success';
                }
            }
        }

        // Parse stuff.
        if ($errors['current-password'] === 'success' && $errors['password'] === 'success' && $errors['confirm-password'] === 'success') {
            $password = password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 1048576,
                'time_cost' => 4,
                'threads' => 8,
            ]);
            $email = $yourself['email'];
            $database->preparedQuery('DELETE FROM verifications WHERE account = ?', [$id]);
            $database->preparedQuery('UPDATE users SET password = ? WHERE id = ?', [$password, $id]);
            $errors['code'] = 'success';
            // Send email
            $username = json_decode($yourself['data'],true)['username'];
            $emailData = "Hello ".$username.". If you're reading this email. Your ".WEBSITE." account password has been changed.";
            $welcome = $username;
            include_once(DOCUMENT_ROOT . '\static\html\emails\change-password.php');
            send_mail(
                $email,
                'Password changed',
                ob_get_contents(),
                $emailData
            );
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