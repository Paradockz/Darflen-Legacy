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
        $token = isset($_COOKIE['token']) ? $_COOKIE['token'] : 0;
        if (check_token_validity($token)) {
            $database = prepare_database();
            $yourself = get_user_info_from_token($token);
            $check = $database->preparedQuery('SELECT count(id) AS count FROM status WHERE account = ?', [$yourself['id']])->fetch(PDO::FETCH_ASSOC)['count'] > 0;
            if ($check) {
                $status = $database->preparedQuery('SELECT account,activity,data FROM status WHERE account = ?', [$yourself['id']])->fetch(PDO::FETCH_ASSOC);
                $jsonData = json_decode($status['data'],true);
                $jsonData['latest_update'] = time();
                $jsonData = json_encode($jsonData);
                $database->preparedQuery('UPDATE status SET data = ?, activity = ? WHERE account = ?',[$jsonData,1,$yourself['id']]);
            } else {
                $jsonData = json_encode([
                    'latest_update' => time(),
                    'miscellaneous' => [
                        'creation_time' => time(),
                    ]
                ]);
                $database->preparedQuery('INSERT INTO status (account,activity,data) VALUES (?,?,?)',[$yourself['id'],1,$jsonData]);
            }            
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