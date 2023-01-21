<?php
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
error_reporting(E_ERROR);
try {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $database = prepare_database();
        $identifier = $_GET['u'] ?? 1;
        $_GET['p'] = $_GET['p'] == null ? 0 : $_GET['p'];
        if ($author = get_user_info_from_identifier($identifier)) {
            $total = $database->preparedQuery('SELECT UNIQUE count(id) as result FROM loves WHERE profile = ?', [$author['id']])->fetch(PDO::FETCH_ASSOC)['result'];
            $paginator = 15;
            $paginator_count = floor($total / $paginator);
            if (isset($_GET['p'])) {
                $paginator_page = $_GET['p'];
            } else {
                $paginator_page = 0;
            }
            $follows = $database->preparedQuery('SELECT UNIQUE user FROM loves WHERE profile = ? LIMIT ?,?', [$author['id'], $paginator * $paginator_page, $paginator])->fetchAll(PDO::FETCH_ASSOC);
            $errors['code'] = 'success';
            foreach ($follows as $index => $follow) {
                if ($user = get_user_info_from_id($follow['user'])) {
                    $user_data = json_decode($user['data'], true);
                    $errors[$index+1] = [
                        'code' => 'success',
                        'identifier' => $user['identifier'],
                        'username' => $user_data['username'],
                        'profile' => [
                            'icon' => $user_data['profile']['icon'],
                            'banner' => $user_data['profile']['banner'],
                            'description' => $user_data['profile']['description'],
                        ],
                        'stats' => [
                            'posts' => $database->preparedQuery('SELECT count(id) AS result FROM posts WHERE author = ?', [$user['id']])->fetch(PDO::FETCH_ASSOC)['result'],
                            'followers' => $database->preparedQuery('SELECT count(id) AS result FROM follows WHERE following = ?', [$user['id']])->fetch(PDO::FETCH_ASSOC)['result'],
                            'following' => $database->preparedQuery('SELECT count(id) AS result FROM follows WHERE follower = ?', [$user['id']])->fetch(PDO::FETCH_ASSOC)['result'],
                            'hearts' => $database->preparedQuery('SELECT count(id) AS result FROM loves WHERE user = ?', [$user['id']])->fetch(PDO::FETCH_ASSOC)['result'],
                            'achievements' => $database->preparedQuery('SELECT count(id) AS result FROM badges WHERE account = ?', [$user['id']])->fetch(PDO::FETCH_ASSOC)['result']
                        ],
                        'miscellaneous' => [
                            'verified' => $user_data['miscellaneous']['user_verified'],
                            'admin' => $user_data['miscellaneous']['administrator'],
                            'banned' => $database->preparedQuery('SELECT count(id) AS result FROM bans WHERE user = ?', [$user['identifier']])->fetch(PDO::FETCH_ASSOC)['result'] > 0,
                            'created' => $user_data['miscellaneous']['creation_time'],
                        ]
                    ];
                } else {
                    $errors = ['code' => 'fail', 'error' => '400', 'cause' => 'Error occured'];
                }
            }
        } else {
            $errors = ['code' => 'fail', 'error' => '404', 'cause' => 'User does not exist'];
        }
    } else {
        // There's an error.
        $errors = ['code' => 'fail', 'error' => '400', 'cause' => 'Invalid request method'];
    }
} catch (Exception $error) {
    // There's an error.
    $errors = ['code' => 'fail', 'error' => '400', 'cause' => $error->getMessage()];
}
ob_clean();
echo json_encode($errors);