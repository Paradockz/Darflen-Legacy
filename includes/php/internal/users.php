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
        $type = $_POST['type'];

        switch ($type) {
            case 'edit':
                // Store variables.
                $email = $_POST['email'];
                $username = $_POST['username'];
                $password = $_POST['password'];
                $user = $_POST['id'];
                $description = $_POST['description'];
                $directory = DOCUMENT_ROOT . '/static/uploads/';
                $yourself = get_user_info_from_identifier($user);
                $administrator = $_POST['administrator'];
                $email_verification = $_POST['email_verified'];
                $account_verification = $_POST['user_verified'];

                // Check email.
                if (empty($email)) {
                    $errors['email'] = 'empty';
                } elseif (strlen($email) > 255) {
                    $errors['email'] = 'length';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE)) {
                    $errors['email'] = 'malformated';
                } else {
                    $domain = strtolower(explode('@', $email)[1]);
                    if (!dns_check_record($domain, 'MX') || !dns_check_record($domain, 'A')) {
                        $errors['email'] = 'invalid';
                    } else {
                        $check = $database->preparedQuery('SELECT count(email) AS result FROM users WHERE email = ?', [$email])->fetch(PDO::FETCH_ASSOC);
                        if ($check['result'] > 0) {
                            $check = $database->preparedQuery('SELECT count(email) AS result FROM users WHERE email = ? AND identifier = ?', [$email,$user])->fetch(PDO::FETCH_ASSOC);
                            if ($check['result'] < 1) {
                                $errors['email'] = 'used';
                            } else {
                                $errors['email'] = 'success';
                            }
                        } else {
                            $errors['email'] = 'success';
                        }
                    }
                }

                // Check username.
                if (empty($username)) {
                    $errors['username'] = 'success';
                } elseif (strlen($username) > 24 || strlen($username) < 2) {
                    $errors['username'] = 'length';
                } elseif (!preg_match('/^[A-Za-z0-9]+(?:[_-][A-Za-z0-9]+)*$/', $username)) {
                    $errors['username'] = 'malformated';
                } else {
                    $errors['username'] = 'success';
                }

                // Check password.
                if (empty($password)) {
                    $errors['password'] = 'success';
                } elseif (strlen($password) < 6 || strlen($password) > 255) {
                    $errors['password'] = 'length';
                } elseif (preg_match('/(\w)\1{3,}/', $password)) {
                    $errors['password'] = 'malformated';
                } else {
                    $errors['password'] = 'success';
                }

                // Upload and check icon.
                if (!empty($_FILES['icon']['tmp_name']) && sha1_file($directory . basename(json_decode($yourself['data'], true)['profile']['icon'])) != sha1_file($_FILES['icon']['tmp_name'])) {

                    
                    $errors['icon'] = upload_banner_image('icon', 8000000, $database, $directory, $yourself['id'], true);
                } else {
                    $errors['icon'] = 'success';
                }

                // Upload and check banner.
                if (!empty($_FILES['banner']['tmp_name']) && sha1_file($directory . basename(json_decode($yourself['data'], true)['profile']['banner'])) != sha1_file($_FILES['banner']['tmp_name'])) {
                    $errors['banner'] = upload_banner_image('banner', 8000000, $database, $directory, $yourself['id']);
                } else {
                    $errors['banner'] = 'success';
                }

                // Check description.
                if (strlen($_POST['description']) <= 1024) {
                    $check = json_decode($database->preparedQuery('SELECT data FROM users WHERE identifier = ?', [$yourself['id']])->fetch(PDO::FETCH_ASSOC)['data'], true);
                    $check['profile']['description'] = $_POST['description'];
                    $check = json_encode($check);
                    $database->preparedQuery('UPDATE users SET data=? WHERE identifier = ?', [$check, $yourself['id']]);
                    $errors['description'] = 'success';
                } else {
                    $errors['description'] = 'length';
                }

                // Check administration.
                
                
                if (empty($administrator)) {
                    $errors['administrator'] = 'empty';
                } else {
                    $errors['administrator'] = 'success';
                }

                // Check email verification.
                $errors['email_verified'] = 'success';

                // Check account verification.
                $errors['user_verified'] = 'success';

                // Parse stuff and update the user.
                if ($errors['description'] == 'success' && $errors['icon'] == 'success' && $errors['banner'] == 'success' && $errors['username'] == 'success' && $errors['email'] == 'success' && $errors['password'] == 'success' && $errors['administrator'] == 'success' && $errors['email_verified'] == 'success' && $errors['user_verified'] == 'success') {
                    if (!empty($password)) {
                        $password = password_hash($password, PASSWORD_ARGON2ID, [
                            'memory_cost' => 1024000,
                            'time_cost' => 4,
                            'threads' => 8,
                        ]);
                    } else {
                        $password = $yourself['password'];
                    }
                    $yourself = get_user_info_from_identifier($user);
                    $jsonData = json_encode([
                        'username' => $username,
                        'profile' => [
                            'description' => $description,
                            'banner' => json_decode($yourself['data'],true)['profile']['banner'],
                            'icon' => json_decode($yourself['data'],true)['profile']['icon']
                        ],
                        'miscellaneous' => [
                            'administrator' => $administrator == 'on',
                            'email_verified' => $email_verification == 'on',
                            'user_verified' => $account_verification == 'on',
                            'creation_time' => json_decode($yourself['data'],true)['miscellaneous']['creation_time'],
                        ]
                    ]);
                    
                    $database->preparedQuery('UPDATE users SET email = ?, password = ?, data = ? WHERE identifier = ?',[$email,$password,$jsonData,$yourself['identifier']]);
                    $errors = ['code' => 'success'];
                }
                break;
            default:
                $errors = ['code' => 'fail', 'error' => 'Invalid request type'];
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