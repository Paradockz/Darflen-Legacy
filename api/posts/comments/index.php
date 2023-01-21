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
        if ($post = get_post_info_from_id($identifier)) {
            $post_data = json_decode($post['data'], true);
            $total = $database->preparedQuery('SELECT count(id) as result FROM replies WHERE post = ?', [$post['id']])->fetch(PDO::FETCH_ASSOC)['result'];
            if ($post_data['miscellaneous']['coverage'] != 'private') {
                $paginator = 15;
                $paginator_count = floor($total / $paginator);
                if (isset($_GET['p'])) {
                    $paginator_page = $_GET['p'];
                } else {
                    $paginator_page = 0;
                }
                $replies = $database->preparedQuery('SELECT id, author, text, data FROM replies WHERE post = ? ORDER BY JSON_VALUE(data,"$.miscellaneous.creation_time") ASC LIMIT ?,?', [$post['id'], $paginator * $paginator_page, $paginator])->fetchAll(PDO::FETCH_ASSOC);
                $errors['code'] = 'success';
                foreach ($replies as $index => $reply) {
                    $reply_data = json_decode($reply['data'], true);
                    $user = get_user_info_from_id($reply['author']);
                    $errors[$index] = [
                        'identifier' => $reply['id'],
                        'post' => $identifier,
                        'author' => $user['identifier'],
                        'content' => [
                            'content' => $reply['text'],
                            'images' => $reply_data['images']
                        ],
                        'stats' => [
                            'loves' => $database->preparedQuery('SELECT count(id) AS result FROM loves WHERE type = ? AND pid = ?', ['reply', $post['id']])->fetch(PDO::FETCH_ASSOC)['result'],
                            'replies' => $database->preparedQuery('SELECT count(id) AS result FROM subreplies WHERE reply = ?', [$post['id']])->fetch(PDO::FETCH_ASSOC)['result']
                        ],
                        'miscellaneous' => [
                            'created' => $post_data['miscellaneous']['creation_time']
                        ]
                    ];
                }
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