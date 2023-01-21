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
            case 'delete':
                $id = $_POST['id'];
                $check = get_subreply_info_from_id($id);
                $yourself = get_user_info_from_token($token);
                if ($check['author'] != $yourself['id']) {
                    throw new Exception("Not the reply author", 1);
                }
                $errors['page'] = 'posts/' . get_post_info_from_id(get_reply_info_from_id($check['reply'])['post'])['id'].'#'.$check['reply'];
                if ($check != false) {
                    foreach (json_decode($check['data'], true)['images'] as $link) {
                        unlink(DOCUMENT_ROOT . '/static' . substr($link, strlen(STATIC_LINK)));
                    }
                    $database->preparedQuery('DELETE FROM subreplies WHERE id = ?', [$check['id']]);
                    $database->preparedQuery('DELETE FROM loves WHERE pid = ? AND type = ?', [$check['id'], 'subreply']);
                }
                $errors['code'] = 'success';
                break;
            case 'heart_subreply':
                $id = $_POST['id'];
                if (isset($_COOKIE['token']) && check_token_validity($token)) {
                    set_timeout('hearts');
                    $timeout = get_timeout('hearts');
                    if (time() > $timeout['time'] + 15) {
                        remove_timeout('hearts');
                        $timeout['count'] = 0;
                    }
                    if ($timeout['count'] <= 10) {
                        $yourself = get_user_info_from_token($token);
                        $authorPost = get_subreply_info_from_id($id);
                        if ($authorPost != false) {
                            $heart_type = 'subreply';
                            $check = $database->preparedQuery('SELECT count(id) AS result FROM loves WHERE type = ? AND user = ? AND pid = ?', [$heart_type, $yourself['id'], $id])->fetch(PDO::FETCH_ASSOC)['result'] > 0;
                            if (!$check) {
                                $database->preparedQuery('INSERT INTO loves (type,user,profile,pid) VALUES (?,?,?,?)', [$heart_type, $yourself['id'], $authorPost['author'], $id]);
                            } else {
                                $database->preparedQuery('DELETE FROM loves WHERE type = ? AND user = ? AND pid = ?', [$heart_type, $yourself['id'], $id]);
                            }
                            $errors = ['code' => 'success', 'newCount' => $database->preparedQuery('SELECT count(id) AS result FROM loves WHERE type = ? AND pid = ?', [$heart_type, $id])->fetch(PDO::FETCH_ASSOC)['result'], 'totalCount' => $database->preparedQuery('SELECT count(id) AS result FROM loves WHERE type = ? AND profile = ?', [$heart_type, $authorPost['author']])->fetch(PDO::FETCH_ASSOC)['result']];
                            $rep = get_reply_info_from_id($authorPost['reply']);
                            $pos = get_post_info_from_id($rep['post'])['id'];
                            if ($yourself['id'] != $authorPost['author'] && !$check) {
                                make_notification($authorPost['author'], 1, json_encode([
                                    'icon' => 'heart.svg',
                                    'html' => sprintf('<a href="%s/users/%s">%s</a> loved your reply in this <a href="%s">comment</a>.', ROOT_LINK, $yourself['identifier'], json_decode($yourself['data'], true)['username'], ROOT_LINK. '/posts/' . $pos . '#' . $rep['id']),
                                    'miscellaneous' => [
                                        'creation_time' => time(),
                                        'read' => false
                                    ]
                                ]));
                            }
                        } else {
                            // There's an error.
                            $errors = ['code' => 'fail', 'error' => 'Posts does not exist'];
                        }
                    } else {
                        // There's an error.
                        $errors = ['code' => 'fail', 'error' => 'You are requesting too many time! Please slow down.'];
                    }
                } else {
                    $errors['code'] = 'unlogged';
                }
                break;
            case 'load':
                $reply = $_POST['reply'];
                $rep = get_reply_info_from_id($reply)['author'];
                if (!$rep) {
                    $rep = get_subreply_info_from_id($reply);
                    $reply = $rep['reply'];
                    $rep = $rep['author'];
                }
                $user = json_decode(get_user_info_from_id($rep)['data'],true)['username'];
                $textarea = '@'.$user.' ';
                ob_start();
                include DOCUMENT_ROOT . '/static/html/partial/subreply.php';
                echo ob_get_clean();
                return;
                break;
            case 'post':
            case 'edit':
                $textarea = $_POST['textarea'];
                $images = $_FILES;
                $mode = 'post';
                $id = $_POST['id'];
                if ($type == 'edit') {
                    $subreply = get_subreply_info_from_id($id);
                }
                

                // Check text.
                if (empty($textarea)) {
                    $errors['textarea'] = 'empty';
                } elseif (strlen($textarea) > 1024) {
                    $errors['textarea'] = 'length';
                } else {
                    $errors['textarea'] = 'success';
                }

                if (count($images) > 0 && !empty($images[0]['name']) && $type == 'edit' || $type == 'post') {
                    // Check images
                    $images = upload_mass_files($images, $errors['textarea'] == 'success');
                    if (isset($images['code'])) {
                        $errors['images-container'] = $images['code'];
                        $images_dir = $images['imgs'];
                    } else {
                        $errors['images-container'] = $images;
                    }
                } else {
                    $errors['images-container'] = 'success';
                    $images_dir = json_decode($subreply['data'], true)['images'];
                }

                if ($errors['textarea'] == 'success' && $errors['images-container'] == 'success') {
                    $user = get_user_info_from_token($token);
                    if ($subreply['author'] != $user['id'] && $type == 'edit') {
                        // There's an error.
                        $errors = ['code' => 'fail', 'error' => 'User is invalid'];
                    } else {
                        $errors['code'] = 'success';
                        if ($type == 'post') {
                            $subid = bin2hex(openssl_random_pseudo_bytes(12));
                            $jsonData = json_encode([
                                'images' => $images_dir,
                                'miscellaneous' => [
                                    'creation_time' => time()
                                ]
                            ]);
                            $yourself = get_user_info_from_token($token);
                            $author = $yourself['id'];
                            $errors['post'] = get_post_info_from_id(get_reply_info_from_id($id)['post'])['id'].'#'.$subid;
                            $database->preparedQuery('INSERT INTO subreplies (id,reply,author,text,data) VALUES (?,?,?,?,?)', [$subid,$id,$author,$textarea,$jsonData]);
                            $reply = get_reply_info_from_id($id);
                            $reply_author = $reply['author'];
                            if ($author != $reply['author']) {
                                make_notification($reply['author'], 1, json_encode([
                                    'icon' => 'message.svg',
                                    'html' => sprintf('<a href="%s/users/%s">%s</a> replied to your <a href="%s">comment</a>.', ROOT_LINK, $yourself['identifier'], json_decode($yourself['data'], true)['username'], ROOT_LINK.'/posts/' . $reply['post'].'#'.$id),
                                    'miscellaneous' => [
                                        'creation_time' => time(),
                                        'read' => false
                                    ]
                                ]));
                            }
                        } else {
                            $jsonData = json_decode($subreply['data'], true);
                            $jsonData['images'] = $images_dir;
                            $jsonData = json_encode($jsonData);
                            $database->preparedQuery('UPDATE subreplies SET text = ?, data = ? WHERE id = ?', [$textarea, $jsonData, $subreply['id']]);
                            $errors['post'] = $database->preparedQuery('SELECT reply FROM subreplies WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC)['reply'];
                            $rep = get_reply_info_from_id($errors['post']);
                            $errors['post'] = $rep['post'].'#'.$subreply['id'];
                        }
                    }
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