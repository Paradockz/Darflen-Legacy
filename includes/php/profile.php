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
        $id = $_POST['id'];
        $yourself = get_user_info_from_token($token);
        if ($following = get_user_info_from_id($id) != false || $token == false) {
            set_timeout('follows');
            $timeout = get_timeout('follows');
            if (time() > $timeout['time'] + 15) {
                remove_timeout('follows');
                $timeout['count'] = 0;
            }
            if ($timeout['count'] <= 10) {
                $check = $database->preparedQuery('SELECT count(id) AS result FROM follows WHERE follower = ? AND following = ?', [$yourself['id'], $id])->fetch(PDO::FETCH_ASSOC)['result'] > 0;
                if ($check == false) {
                    $jsonData = json_encode([
                        'miscellaneous' => [
                            'creation_time' => time()
                        ]
                    ]);
                    $database->preparedQuery('INSERT INTO follows (follower,following, data) VALUES (?,?,?)', [$yourself['id'], $id, $jsonData]);
                    update_follow_badge(get_user_info_from_id($id), $database->preparedQuery('SELECT count(id) AS result FROM follows WHERE following = ?', [$id])->fetch(PDO::FETCH_ASSOC)['result']);
                    make_notification($id, 1, json_encode([
                        'icon' => 'user-add.svg',
                        'html' => sprintf('<a href="%s/users/%s">%s</a> is now following you.', ROOT_LINK, $yourself['identifier'], json_decode($yourself['data'], true)['username']),
                        'miscellaneous' => [
                            'creation_time' => time(),
                            'read' => false
                        ]
                    ]));
                } else {
                    $database->preparedQuery('DELETE FROM follows WHERE follower = ? AND following = ?', [$yourself['id'], $id]);
                }
                $errors = ['code' => 'success', 'newCount' => $database->preparedQuery('SELECT count(id) AS result FROM follows WHERE following = ?', [$id])->fetch(PDO::FETCH_ASSOC)['result']];
            } else {
                // There's an error.
                $errors = ['code' => 'fail', 'error' => 'You are requesting too many time! Please slow down.'];
            }
        } else {
            // There's an error.
            $errors = ['code' => 'fail', 'error' => 'Invalid user'];
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