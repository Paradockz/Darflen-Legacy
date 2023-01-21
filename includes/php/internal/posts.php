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
            case 'subreply_edit':
                $textarea = $_POST['textarea'];
                $images = $_FILES;
                $id = $_POST['id'];
                $subreply = get_subreply_info_from_id($id);

                // Check text.
                if (empty($textarea)) {
                    $errors['textarea'] = 'empty';
                } elseif (strlen($textarea) > 1024) {
                    $errors['textarea'] = 'length';
                } else {
                    $errors['textarea'] = 'success';
                }

                if (count($images) > 0 && !empty($images[0]['name'])) {
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
                    if ($subreply['author'] != $user['id']) {
                        // There's an error.
                        $errors = ['code' => 'fail', 'error' => 'User is invalid'];
                    } else {
                        $errors['code'] = 'success';
                        $jsonData = json_decode($subreply['data'], true);
                        $jsonData['images'] = $images_dir;
                        $jsonData = json_encode($jsonData);
                        $database->preparedQuery('UPDATE subreplies SET text = ?, data = ? WHERE id = ?', [$textarea, $jsonData, $subreply['id']]);
                        $errors['post'] = $database->preparedQuery('SELECT reply FROM subreplies WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC)['reply'];
                        $rep = get_reply_info_from_id($errors['post']);
                        $errors['post'] = $rep['post'] . '#' . $subreply['id'];
                    }
                }
                break;
            case 'edit':
                $textarea = $_POST['textarea'];
                $images = $_FILES;
                $mode = 'post';
                $id = $_POST['id'];
                if (!$post = get_post_info_from_id($id)) {
                    $mode = 'reply';
                    $post = get_reply_info_from_id($id);
                }

                $yourself = get_user_info_from_id($post['author']);

                if ($mode == 'post') {
                    $coverage = $_POST['coverage'];
                }

                // Check text.
                if (empty($textarea)) {
                    $errors['textarea'] = 'empty';
                } elseif (strlen($textarea) > 1024) {
                    $errors['textarea'] = 'length';
                } else {
                    $errors['textarea'] = 'success';
                }

                // Check coverage.
                if ($mode == 'post') {
                    if (empty($coverage)) {
                        $errors['coverage'] = 'empty';
                    } elseif (!in_array($coverage, ['public', 'unlisted', 'followers', 'private'])) {
                        $errors['coverage'] = 'invalid';
                    } else {
                        $errors['coverage'] = 'success';
                    }
                } else {
                    $errors['coverage'] = 'success';
                }

                if (count($images) > 0 && !empty($images[0]['name'])) {
                    // Check images
                    $images = upload_mass_files($images, $errors['coverage'] == 'success' && $errors['textarea'] == 'success');
                    if (isset($images['code'])) {
                        $errors['images-container'] = $images['code'];
                        $images_dir = $images['imgs'];
                    } else {
                        $errors['images-container'] = $images;
                    }
                } else {
                    $errors['images-container'] = 'success';
                    $images_dir = json_decode($post['data'], true)['images'];
                }

                if ($errors['textarea'] == 'success' && $errors['coverage'] == 'success' && $errors['images-container'] == 'success') {
                        $errors['code'] = 'success';
                        $jsonData = json_decode($post['data'], true);
                        $jsonData['images'] = $images_dir;
                        if ($mode == 'reply') {
                            $jsonData = json_encode($jsonData);
                            $database->preparedQuery('UPDATE replies SET text = ?, data = ? WHERE id = ?', [$textarea, $jsonData, $post['id']]);
                            $errors['post'] = $database->preparedQuery('SELECT post FROM replies WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC)['post'];
                            make_notification($post['author'], 1, json_encode([
                                'icon' => 'warning.svg',
                                'html' => sprintf('An administrator modified your <a href="%s">reply</a>.', ROOT_LINK.'/posts/' . $post['post'] . '#' . $id),
                                'miscellaneous' => [
                                    'creation_time' => time(),
                                    'read' => false
                                ]
                            ]));
                        } else {
                            preg_match_all('/#[a-zA-Z0-9_-]+/', $textarea, $hashtags);
                            $database->preparedQuery('DELETE FROM hashtags WHERE post = ?', [$id]);
                            foreach (array_iunique($hashtags[0]) as $hashtag) {
                                $database->preparedQuery('INSERT INTO hashtags (post,hashtag) VALUES (?,?)', [$id, substr($hashtag, 1)]);
                            }
                            $jsonData['miscellaneous']['coverage'] = $coverage;
                            $jsonData = json_encode($jsonData);
                            $database->preparedQuery('UPDATE posts SET text = ?, meta = ?, data = ? WHERE id = ?', [$textarea, metaphone($textarea), $jsonData, $post['id']]);
                            $errors['post'] = $id;
                            make_notification($post['author'], 1, json_encode([
                                'icon' => 'warning.svg',
                                'html' => sprintf('An administrator modified your <a href="%s">post</a>.', ROOT_LINK.'/posts/' . $id),
                                'miscellaneous' => [
                                    'creation_time' => time(),
                                    'read' => false
                                ]
                            ]));
                        }
                }
                $errors['mode'] = $mode;
                break;
            case 'delete_post': 
                $id = $_POST['id'];
                $check = get_post_info_from_id($id);
                if ($check != false) {
                    $yourself = get_user_info_from_id($check['author']);
                    $errors['page'] = 'users/' . $database->preparedQuery('SELECT identifier FROM users WHERE id = ?', [$check['author']])->fetch(PDO::FETCH_ASSOC)['identifier'];
                    foreach (json_decode($check['data'], true)['images'] as $link) {
                        unlink(DOCUMENT_ROOT . '/static' . substr($link, strlen(STATIC_LINK)));
                    }
                    $database->preparedQuery('DELETE FROM hashtags WHERE post = ?', [$check['id']]);
                    $database->preparedQuery('DELETE FROM replies WHERE post = ?', [$check['id']]);
                    $database->preparedQuery('DELETE FROM loves WHERE pid = ?', [$check['id']]);
                    $database->preparedQuery('DELETE FROM posts WHERE author = ? AND id = ?', [$yourself['id'], $id]);
                    $errors['code'] = 'success';
                } elseif ($check == false) {
                    $check = get_reply_info_from_id($id);
                    if ($check != false) {
                        $errors['page'] = 'posts/' . $check['post'];
                        if ($check != false) {
                            foreach (json_decode($check['data'], true)['images'] as $link) {
                                unlink(DOCUMENT_ROOT . '/static' . substr($link, strlen(STATIC_LINK)));
                            }
                            $database->preparedQuery('DELETE FROM subreplies WHERE reply = ?', [$check['id']]);
                            $database->preparedQuery('DELETE FROM replies WHERE post = ? AND id = ?', [$check['post'], $check['id']]);
                            $database->preparedQuery('DELETE FROM loves WHERE pid = ? AND type = ?', [$check['id'], 'reply']);
                        }
                        $errors['code'] = 'success';
                    } elseif ($check == false) {
                        $check = get_subreply_info_from_id($id);
                        foreach (json_decode($check['data'], true)['images'] as $link) {
                            unlink(DOCUMENT_ROOT . '/static' . substr($link, strlen(STATIC_LINK)));
                        }
                        $database->preparedQuery('DELETE FROM subreplies WHERE id = ?', [$check['id']]);
                        $database->preparedQuery('DELETE FROM loves WHERE pid = ? AND type = ?', [$check['id'], 'subreply']);
                        $errors['code'] = 'success';
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


/*
make_notification($post['author'], 1, json_encode([
                        'icon' => 'warning.svg',
                        'html' => 'An administrator deleted a post from your profile.',
                        'miscellaneous' => [
                            'creation_time' => time(),
                            'read' => false
                        ]
                    ]));

                    */