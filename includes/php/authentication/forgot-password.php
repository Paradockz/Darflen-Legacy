<?php
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
error_reporting(0);
ob_start();
try {
    header('Content-Type: application/json');    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $database = prepare_database();
        $errors = ['code' => 'ready'];    
        switch ($_POST['type']) {
            case 'send':
                $email = $_POST['email'];
                $errors['password'] = 'success';
                // Check email.
                if (empty($email)) {
                    $errors['email'] = 'empty';
                } else {
                    $check = $database->preparedQuery('SELECT id,email,password FROM users WHERE email = ?', [$email])->fetch(PDO::FETCH_ASSOC);
                    if (!isset($check['password'])) {
                        $errors['email'] = 'invalid';
                    } else {
                        $errors['email'] = 'success';
                    }
                }
                // Parse stuff and send email.
                if ($errors['email'] === 'success') {
                    // Generate token
                    $code = bin2hex(openssl_random_pseudo_bytes(48));
                    $id = $check['id'];
                    $username = json_decode($database->preparedQuery('SELECT data FROM users WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC)['data'],true)['username'];
                    $database->preparedQuery('INSERT INTO verifications (type,account,token) VALUES (?,?,?)', ['recover_pass', $id, $code]);
                    $link = ROOT_LINK.'/password/reset?q=' . $code;
                    $errors['code'] = 'success';
                    // Send email 
                    $emailData = 'Hello '.$username.'. This email confirms someone tried to change this account password. If it is not you, please ignore this email. Recover by using this link: ' . $link;
                    $welcome = $username;
                    include(DOCUMENT_ROOT . '/static/html/emails/forgot-password.php');
                    send_mail(
                        $email, 
                        'Password recovery',
                        ob_get_contents(),
                        $emailData
                    );
                }
                break;
            case 'confirm':
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm-password'];
                $token = $_POST['token'];
                // Check password.
                if (empty($password)) {
                    $errors['password'] = 'empty';
                } elseif (strlen($password) < 6 || strlen($password) > 255) {
                    $errors['password'] = 'length';
                } elseif (preg_match('/(\w)\1{3,}/', $password)) {
                    $errors['password'] = 'malformated';
                } else {
                    $check = $database->preparedQuery('SELECT count(id) AS result, account FROM verifications WHERE type = ? AND token = ?', ['recover_pass', $token])->fetch(PDO::FETCH_ASSOC);
                    $id = $check['account'];
                    $oldPass =  $database->preparedQuery('SELECT password FROM users WHERE id = ?', [$check['account']])->fetch(PDO::FETCH_ASSOC)['password'];
                    if (password_verify($password, $oldPass)) {
                        $errors['password'] = 'copy';
                    } else {
                        $errors['password'] = 'success';
                    }
                }

                // Check confirm password.
                if (empty($confirm_password)) {
                    $errors['confirm-password'] = 'empty';
                } elseif ($password != $confirm_password) {
                    $errors['confirm-password'] = 'invalid';
                } else {
                    $errors['confirm-password'] = 'success';
                }

                // Parse some stuff.
                if ($errors['password'] === 'success' && $errors['confirm-password'] === 'success') {
                    $password = password_hash($password, PASSWORD_ARGON2ID, [
                        'memory_cost' => 1024000,
                        'time_cost' => 4,
                        'threads' => 8,
                    ]);
                    $email = $database->preparedQuery('SELECT email FROM users WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC)['email'];
                    $database->preparedQuery('DELETE FROM verifications WHERE account = ?', [$id]);
                    $database->preparedQuery('UPDATE users SET password = ? WHERE id = ?', [$password, $id]);
                    $errors['code'] = 'success';
                    $welcome = json_decode(get_user_info_from_id($id)['data'],true)['username'];
                    include_once(DOCUMENT_ROOT . '\static\html\emails\change-password.php');
                    // Send email
                    send_mail(
                        $email,
                        'Password changed',
                        ob_get_contents(),
                        "Hello. If you're reading this email. Your Darflen account password has been changed."
                    );
                }
                break;
            default:
                // There's an error.
                $errors = ['code' => 'fail', 'error' => 'Invalid type'];
                break;
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