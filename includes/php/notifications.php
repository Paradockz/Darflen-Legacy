<?php
session_start();
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
        $yourself = get_user_info_from_token($token);
        $limit = $_POST['limit'];
        $_SESSION['offset'] += $limit;
        $mode = $_SESSION['mode'];
        $notifications = $database->preparedQuery('SELECT user,priority,data FROM notifications WHERE user = ? ORDER BY priority DESC, id DESC LIMIT ?,?',[$yourself['id'],$_SESSION['offset'],15])->fetchAll(PDO::FETCH_ASSOC);
        // DO NOT TOUCH ANYTHING BELOW, IT WOULD WORK FINE.
        if ($notifications) {
            $result = '';
            foreach ($notifications as $notification) {
                $data = json_decode($notification['data'], true);
                $result .= notification($data['icon'], $data['html'], $data['miscellaneous']['creation_time']);
            }
            $errors = ['code' => 'success', 'posts' => $result];
        } else {
            $errors = ['code' => 'ready'];
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