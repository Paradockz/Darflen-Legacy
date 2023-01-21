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
        if ($post = $database->preparedQuery('SELECT id, author, text, reply, data FROM subreplies WHERE id = ?', [$identifier])->fetch(PDO::FETCH_ASSOC)) {
            $post_data = json_decode($post['data'], true);
            $user = get_user_info_from_id($post['author']);
            $errors = [
                'code' => 'success',
                'identifier' => $post['id'],
                'post' => $post['reply'],
                'author' => $user['identifier'],
                'content' => [
                    'content' => $post['text'],
                    'images' => $post_data['images']
                ],
                'stats' => [
                    'loves' => $database->preparedQuery('SELECT count(id) AS result FROM loves WHERE type = ? AND pid = ?', ['post', $post['id']])->fetch(PDO::FETCH_ASSOC)['result']
                ],
                'miscellaneous' => [
                    'created' => $post_data['miscellaneous']['creation_time']
                ]
            ];
        } else {
            $errors = ['code' => 'fail', 'error' => '404', 'cause' => 'Reply does not exist'];
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