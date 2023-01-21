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
        $tCode = $_POST['tCode'];
        $type = $_POST['type'];
        $token = $_COOKIE['token'];
        $data = get_user_info_from_token($token);
        switch ($type) {
            case 'send':
                // Generate token
                $id = $data['id'];
                if (isset(json_decode($data['data'],true)['miscellaneous']['upcoming_email'])) {
                    $email = json_decode($data['data'],true)['miscellaneous']['upcoming_email'];
                } else {
                    $email = $data['email'];
                }
                $username = json_decode($data['data'], true)['username'];
                $code = hexdec(bin2hex(openssl_random_pseudo_bytes(2))) + 100000 + rand(1, 375711);
                $database->preparedQuery('INSERT INTO verifications (type,account,token) VALUES (?,?,?)', ['email', $id, $code]);
                $emailData = 'Hello ' . $username . '. Your Darflen email verification code is: ' . $code;
                // Send email
                send_mail(
                    $email,
                    'Darflen Email Verification',
                    $emailData,
                    $emailData
                );
                break;
            case 'confirm':
                $id = $data['id'];
                if (empty($tCode)) {
                    $errors['tCode'] = 'empty';
                } else {
                    $check = $database->preparedQuery('SELECT count(id) AS result FROM verifications WHERE type = ? AND account = ? AND token = ?', ['email', $id, $tCode])->fetch(PDO::FETCH_ASSOC)['result'];
                    if ($check < 1) {
                        $errors['tCode'] = 'invalid';
                    } else {
                        $errors['tCode'] = 'success';
                    }
                }
                if ($errors['tCode'] === 'success') {
                    $database->preparedQuery('DELETE FROM verifications WHERE account = ?', [$id]);
                    $check = json_decode($database->preparedQuery('SELECT data FROM users WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC)['data'], true);
                    if (isset($check['miscellaneous']['upcoming_email'])) {
                        $newEmail = $check['miscellaneous']['upcoming_email'];
                        unset($check['miscellaneous']['upcoming_email']);
                        $check['miscellaneous']['email_verified'] = true;
                        $check = json_encode($check);
                        $database->preparedQuery('UPDATE users SET email=?, data=? WHERE id = ?', [$newEmail, $check, $id]);
                        $email = $newEmail;
                        $errors = ['code' => 'success'];
                    } else {
                        $check['miscellaneous']['email_verified'] = true;
                        $check = json_encode($check);
                        $database->preparedQuery('UPDATE users SET data=? WHERE id = ?', [$check, $id]);
                        $email = $database->preparedQuery('SELECT email FROM users WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC)['email'];
                        $errors = ['code' => 'success'];
                    }
                    // Send email
                    $emailData = 'Hello ' . $username . ". If you''re seeing this email, this means your Darflen account has successfully been verified!";
                    send_mail(
                        $email,
                        'Email verified',
                        $emailData,
                        $emailData
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