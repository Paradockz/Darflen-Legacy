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
        $limit = $_POST['limit'];
        $type = $_POST['type'];
        set_timeout('explore');
        $timeout = get_timeout('explore');
        if (time() > $timeout['time'] + 15) {
            remove_timeout('explore');
            $timeout['count'] = 0;
        }
        if ($timeout['count'] <= 10) {
            if ($type == 'explore') {
                $_SESSION['offset'] += $limit;
            } else {
                $_SESSION['offset'] = 0;
            }
            if (isset($_POST['mode'])) {
                if (in_array($_POST['mode'], ['recent', 'trending', 'loved', 'popular', 'hashtags'])) {
                    $_SESSION['mode'] = $_POST['mode'];
                } else {
                    $_SESSION['mode'] = $_POST['mode'] != 'search' ? $_SESSION['mode'] : 'search';
                }
            }
            $mode = $_SESSION['mode'];
            $search = $_SESSION['query'];
            if (!in_array($search, ['*'])) {
                $posts = recommend_user_posts($_SESSION['mode'], $limit, $_SESSION['offset'], $search);
            } else {
                $posts = [];
            }


            // DO NOT TOUCH ANYTHING BELOW, IT WOULD WORK FINE.
            if ($posts) {
                $result = '';
                $yourself = [];
                if (isset($_COOKIE['token']) && check_token_validity($_COOKIE['token'])) {
                    $yourself = get_user_info_from_token($_COOKIE['token']);
                }
                foreach ($posts as $post) {
                    $loves = $database->preparedQuery('SELECT count(id) AS loves FROM loves WHERE type = ? AND pid = ?', ['post', $post['id']])->fetch(PDO::FETCH_ASSOC)['loves'];
                    $loved = $database->preparedQuery('SELECT count(id) AS loved FROM loves WHERE type = ? AND pid = ? AND user = ?', ['post', $post['id'], $yourself['id']])->fetch(PDO::FETCH_ASSOC)['loved'] > 0;
                    $replies = $database->preparedQuery('SELECT count(id) as replies FROM replies WHERE post = ?', [$post['id']])->fetch(PDO::FETCH_ASSOC)['replies'];
                    $user = get_user_info_from_id($post['author']);
                    $result .= build_post($post, $user, $yourself, $replies, $loved, $loves);
                }
                $errors = ['code' => 'success', 'posts' => $result];
            } else {
                $errors = ['code' => 'ready'];
            }
        } else {
            // There's an error.
            $errors = ['code' => 'fail', 'error' => 'You are requesting too many time! Please slow down.'];
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
