<?php
ob_start();
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
        $username = $_POST['username'];
        $password = $_POST['password'];
        $day = $_POST['day'];
        $month = $_POST['month'];
        $year = $_POST['year'];
        $invite = $_POST['invite'];
        
        
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
                    $errors['email'] = 'used';
                } else {
                    $errors['email'] = 'success';
                }
            }
        }

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
        } elseif (strlen($password) < 6 || strlen($password) > 255) {
            $errors['password'] = 'length';
        } elseif (preg_match('/(\w)\1{3,}/', $password)) {
            $errors['password'] = 'malformated';
        } else {
            $errors['password'] = 'success';
        }

        // Check birthdate.
        $birthdate = $year . '-' . $month . '-' . $day;
        if($birthdate === '--') {
            $errors['birthdate'] = 'empty';
        } elseif (empty($year) || empty($month) || empty($day)) {
            $errors['birthdate'] = 'incomplete';
        } elseif (!checkdate($month, $day, $year)) {
            $errors['birthdate'] = 'invalid';
        } elseif (date_diff(date_create($birthdate), date_create('now'))->y < 13) {
            $errors['birthdate'] = 'young';
        } else {
            $errors['birthdate'] = 'success';
        }

        // Parse stuff and create the user.
        if ($errors['username'] === 'success' && $errors['email'] === 'success' && $errors['password'] === 'success' && $errors['birthdate'] === 'success') {
            $password = password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 1048576,
                'time_cost' => 4,
                'threads' => 8,
            ]);
            $jsonData = json_encode([
                'username' => $username,
                'profile' => [
                    'description' => 'This is your default profile description. You can change it at any time.',
                    'banner' => STATIC_LINK.'/uploads/default-banner.png',
                    'icon' => STATIC_LINK.'/uploads/default-icon.png'
                ],
                'miscellaneous' => [
                    'administrator' => false,
                    'email_verified' => false,
                    'user_verified' => false,
                    'creation_time' => time(),
                ]
            ]);
            if (!empty($invite)) {
                $jsonData = json_decode($jsonData,true);
                $jsonData['miscellaneous']['invite'] = $invite;
                $jsonData = json_encode($jsonData);
            }
            $errors['code'] = 'success';
            $token = str_rot13(base64_encode(openssl_random_pseudo_bytes(strlen($password))));
            $id = bin2hex(openssl_random_pseudo_bytes(12));
            $database->preparedQuery('INSERT INTO users (email,password,identifier,data) VALUES (?,?,?,?)', [$email, $password, $id, $jsonData]);
            $id = $database->preparedQuery('SELECT id FROM users WHERE email = ?', [$email])->fetch(PDO::FETCH_ASSOC)['id'];
            $expiration = time() + (3600 * 24 * 30);
            $database->preparedQuery('INSERT INTO tokens (account,token,expiration) VALUES (?,?,?)', [$id, $token, $expiration]);
            setcookie('token', $token, $expiration, '/', '', true, false);
            $emailContent = 'Hello, '. $username .'! Thank you for registering an account with Darflen! This email confirms that you created an account on this website. You can start your adventure by going to: '.ROOT_LINK;
            $welcome = $username;
            include_once('T:\darflen-portable\htdocs\static\html\emails\welcome.php');
            // Send email
            send_mail(
                $email,
                'Welcome to Darflen, '.$username,
                ob_get_contents(),
                $emailContent
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
ob_end_clean();
echo json_encode($errors);