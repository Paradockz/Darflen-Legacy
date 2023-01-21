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
        if ($post = get_post_info_from_id($identifier)) {
            $post_data = json_decode($post['data'], true);
            if ($post_data['miscellaneous']['coverage'] != 'private') {
                $user = get_user_info_from_id($post['author']);
                $errors = [
                    'code' => 'success',
                    'identifier' => $post['id'],
                    'author' => $user['identifier'],
                    'coverage' => $post_data['miscellaneous']['coverage'],
                    'content' => [
                        'content' => $post['text'],
                        'images' => $post_data['images']
                    ],
                    'stats' => [
                        'hits' => $post_data['miscellaneous']['hits'],
                        'loves' => $database->preparedQuery('SELECT count(id) AS result FROM loves WHERE type = ? AND pid = ?', ['post', $post['id']])->fetch(PDO::FETCH_ASSOC)['result'],
                        'comments' => $database->preparedQuery('SELECT count(id) AS result FROM replies WHERE post = ?', [$post['id']])->fetch(PDO::FETCH_ASSOC)['result']
                    ],
                    'miscellaneous' => [
                        'created' => $post_data['miscellaneous']['creation_time']
                    ]
                ];
            } else {
                $errors = ['code' => 'fail', 'error' => '403', 'cause' => 'Post is private'];
            }
        } else {
            $errors = ['code' => 'fail', 'error' => '404', 'cause' => 'Post does not exist'];
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