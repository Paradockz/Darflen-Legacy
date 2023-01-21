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
            case 'posts':
                $mode = 'post';
                $textarea = $_POST['textarea'];
                $reason = $_POST['reason'];
                $post_id = $_POST['id'];

                // Check text.
                if (empty($textarea)) {
                    $errors['textarea'] = 'empty';
                } elseif (strlen($textarea) >= 1024) {
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

                if ($errors['textarea'] == 'success' && $errors['reason'] == 'success') {
                    $user = get_user_info_from_token($token)['id'];
                    if (!get_post_info_from_id($post_id)) {
                        if (get_subreply_info_from_id($post_id)) {
                            $mode = 'reply';
                        } else {
                            $mode = 'comment';
                        }
                    }
                    $jsonData = json_encode([
                        'reporter' => $user,
                        'miscellaneous' => [
                            'creation_time' => time()
                        ]
                    ]);
                    $database->preparedQuery('INSERT INTO reports (type,pid,reason,description,data) VALUES (?,?,?,?,?)', [$mode, $post_id, $reason, $textarea, $jsonData]);
                    $errors = ['code' => 'success', 'post' => $post_id];
                    if ($mode == 'comment') {
                        $errors['post'] = $database->preparedQuery('SELECT post FROM replies WHERE id = ?', [$post_id])->fetch(PDO::FETCH_ASSOC)['post'].'#'.$post_id;
                    } elseif ($mode == 'reply') {
                        $rep = $database->preparedQuery('SELECT reply FROM subreplies WHERE id = ?', [$post_id])->fetch(PDO::FETCH_ASSOC)['reply'];
                        $rep = $database->preparedQuery('SELECT post FROM replies WHERE id = ?', [$rep])->fetch(PDO::FETCH_ASSOC)['post'];
                        $errors['post'] = $rep . '#' . $post_id;
                    }
                }
                break;
            case 'users':
                $mode = 'user';
                $textarea = $_POST['textarea'];
                $reason = $_POST['reason'];
                $user_id = $_POST['id'];

                // Check text.
                if (empty($textarea)) {
                    $errors['textarea'] = 'empty';
                } elseif (strlen($textarea) >= 1024) {
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

                if ($errors['textarea'] == 'success' && $errors['reason'] == 'success') {
                    $user = get_user_info_from_token($token)['identifier'];
                    $jsonData = json_encode([
                        'reporter' => $user,
                        'miscellaneous' => [
                            'creation_time' => time()
                        ]
                    ]);
                    $database->preparedQuery('INSERT INTO reports (type,pid,reason,description,data) VALUES (?,?,?,?,?)', [$mode, $user_id, $reason, $textarea, $jsonData]);
                    $errors = ['code' => 'success', 'profile' => $user_id];
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