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
        $type = $_POST['type'];
       switch ($type) {
        case 'ban':
                $reason = $_POST['reason'];
                $time = $_POST['time'];
                $token = $_COOKIE['token'];
                $user = $_POST['id'];
                $textarea = $_POST['textarea'];

                // Check text.
                if (strlen($textarea) >= 1024) {
                    $errors['textarea'] = 'length';
                } else {
                    $errors['textarea'] = 'success';
                }

                // Check reason.
                if (empty($reason)) {
                    $errors['reason'] = 'empty';
                } elseif (!in_array($reason, ['theft', 'info', 'exploit', 'language', 'content', 'threats', 'other'])) {
                    $errors['reason'] = 'invalid';
                } else {
                    $errors['reason'] = 'success';
                }

                // Check time.
                if (empty($time)) {
                    $errors['time'] = 'empty';
                } elseif (!in_array($time, ['1d', '2d', '5d', '1w', '2w', '1m', 'p'])) {
                    $errors['time'] = 'invalid';
                } else {
                    $errors['time'] = 'success';
                }

                // Check if user is not a fake user.
                if (json_decode(get_user_info_from_token($token)['data'], true)['miscellaneous']['administrator'] != true) {
                    throw new Exception("User is not an admin", 1);
                }

                // Check if user is an admin.
                if (json_decode(get_user_info_from_identifier($user)['data'], true)['miscellaneous']['administrator'] == true) {
                    throw new Exception("User is an admin", 1);
                }

                // Parse some stuff.
                if ($errors['reason'] == 'success' && $errors['time'] == 'success' && $errors['textarea'] == 'success') {
                    switch ($time) {
                        case '1d':
                            $time = time() + (60 * 60 * 24);
                            break;
                        case '2d':
                            $time = time() + (60 * 60 * 24 * 2);
                            break;
                        case '5d':
                            $time = time() + (60 * 60 * 5);
                            break;
                        case '1w':
                            $time = time() + (60 * 60 * 24 * 7);
                            break;
                        case '2w':
                            $time = time() + (60 * 60 * 24 * 14);
                            break;
                        case '1m':
                            $time = time() + (60 * 60 * 24 * 30);
                            break;
                        default:
                            $time = time() + (60 * 60 * 24 * 36000);
                            break;
                    }
                    $data = json_encode([
                        'miscellaneous' => [
                            'reason' => $reason,
                            'moderator' => get_user_info_from_token($token)['identifier'],
                            'creation_time' => time()
                        ]
                    ]);
                    $database->preparedQuery('INSERT INTO bans (type,user,time,data) VALUES (?,?,?,?)', ['user', $user, $time, $data]);
                    $errors = ['code' => 'success'];
                }
            break;
        case 'unban':
            $user = $_POST['id'];
            $database->preparedQuery('DELETE FROM bans WHERE user = ?',[$user]);
            $errors = ['code' => 'success'];
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