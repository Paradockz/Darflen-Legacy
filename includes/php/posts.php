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
        $errors = ['code' => 'ready'];
        $token = $_COOKIE['token'];
        $type = $_POST['type'];
        $database = prepare_database();
        switch ($type) {
            case 'post':
            case 'edit':
                $textarea = $_POST['textarea'];
                $images = $_FILES;
                $reshare = $_POST['reshare'];
                $mode = 'post';
                if ($type == 'edit') {
                    $id = $_POST['id'];
                    if (!$post = get_post_info_from_id($id)) {
                        $mode = 'reply';
                        $post = get_reply_info_from_id($id);                 
                    }
                }
                
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
                
                if (count($images) > 0 && !empty($images[0]['name']) && $type == 'edit' || $type == 'post') {
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
                    $images_dir = json_decode($post['data'],true)['images'];
                }
                
                if ($errors['textarea'] == 'success' && $errors['coverage'] == 'success' && $errors['images-container'] == 'success') {
                    $user = get_user_info_from_token($token);
                    if ($post['author'] != $user['id'] && $type == 'edit') {
                        // There's an error.
                        $errors = ['code' => 'fail', 'error' => 'User is invalid'];
                    } else {   
                        $errors['code'] = 'success';
                        if ($type == 'post') {
                            $id = bin2hex(openssl_random_pseudo_bytes(12));
                            $jsonData = json_encode([
                                'images' => $images_dir,
                                'miscellaneous' => [
                                    'coverage' => $coverage,
                                    'creation_time' => time(),
                                    'hits' => 1,
                                    'hits_last_time' => 1
                                ]
                            ]);
                            if(!empty($reshare)) {
                                $jsonData = json_decode($jsonData,true);
                                $jsonData['type']['type'] = 'repost';
                                $jsonData['type']['id'] = $reshare;
                                $jsonData = json_encode($jsonData);
                            }
                            $author = get_user_info_from_token($token)['id'];                 
                            $errors['post'] = $id;
                            $yourself = get_user_info_from_token($token);
                            $followers = $database->preparedQuery('SELECT follower FROM follows WHERE following = ?',[$author])->fetchAll(PDO::FETCH_ASSOC);
                            if ($mode != ' reply') {
                                preg_match_all('/#[a-zA-Z0-9_-]+/', $textarea, $hashtags);
                                foreach (array_iunique($hashtags[0]) as $hashtag) {
                                    $database->preparedQuery('INSERT INTO hashtags (post,hashtag) VALUES (?,?)', [$id, substr($hashtag, 1)]);
                                }
                            }
                            $database->preparedQuery('INSERT INTO posts (id,author,text,meta,data) VALUES (?,?,?,?,?)',[$id,$yourself['id'], $textarea, metaphone($textarea), $jsonData]);
                            if (!in_array($coverage,['unlisted','private'])) {
                                foreach ($followers as $follower) {
                                    make_notification($follower['follower'], 1, json_encode([
                                        'icon' => 'message.svg',
                                        'html' => sprintf('<a href="%s/users/%s">%s</a> shared a new <a href="%s">post</a>.', ROOT_LINK,  $yourself['identifier'], json_decode($yourself['data'], true)['username'], ROOT_LINK.'/posts/' . $id),
                                        'miscellaneous' => [
                                            'creation_time' => time(),
                                            'read' => false
                                        ]
                                    ]));
                                }
                            }
                        } else {
                            $jsonData = json_decode($post['data'],true);
                            $jsonData['images'] = $images_dir;
                            $jsonData['miscellaneous']['edited'] = true;
                            if ($mode == 'reply') {
                                $jsonData = json_encode($jsonData);
                                $database->preparedQuery('UPDATE replies SET text = ?, data = ? WHERE id = ?',[$textarea,$jsonData,$post['id']]);
                                $errors['post'] = $database->preparedQuery('SELECT post FROM replies WHERE id = ?',[$id])->fetch(PDO::FETCH_ASSOC)['post'];
                            } else {
                                preg_match_all('/#[a-zA-Z0-9_-]+/', $textarea, $hashtags);
                                $database->preparedQuery('DELETE FROM hashtags WHERE post = ?', [$id]);
                                foreach (array_iunique($hashtags[0]) as $hashtag) {
                                    $database->preparedQuery('INSERT INTO hashtags (post,hashtag) VALUES (?,?)', [$id, substr($hashtag,1)]);
                                }
                                $jsonData['miscellaneous']['coverage'] = $coverage;
                                $jsonData = json_encode($jsonData);
                                $database->preparedQuery('UPDATE posts SET text = ?, meta = ?, data = ? WHERE id = ?', [$textarea,metaphone($textarea), $jsonData, $post['id']]);
                                $errors['post'] = $id;
                            }
                        }
                    }
                }
                $errors['mode'] = $mode;
                break;
            case 'reply':
                $textarea = $_POST['textarea'];
                $images = $_FILES;
                $post_id = $_POST['id'];

                // Check text.
                if (empty($textarea)) {
                    $errors['textarea'] = 'empty';
                } elseif (strlen($textarea) >= 1024) {
                    $errors['textarea'] = 'length';
                } else {
                    $errors['textarea'] = 'success';
                }

                // Check images.
                $images = upload_mass_files($images,$errors['textarea'] == 'success');
                if (isset($images['code'])) {
                    $errors['images-container'] = $images['code'];
                    $images_dir = $images['imgs'];
                } else {
                    $errors['images-container'] = $images;
                }

                if ($errors['textarea'] == 'success' && $errors['images-container'] == 'success') {
                    $id = bin2hex(openssl_random_pseudo_bytes(12));
                    $jsonData = json_encode([
                        'images' => $images_dir,
                        'miscellaneous' => [
                            'creation_time' => time(),
                        ]
                    ]);
                    $errors['code'] = 'success';
                    $author = get_user_info_from_token($token);
                    $database->preparedQuery('INSERT INTO replies (id,post,author,text,data) VALUES (?,?,?,?,?)', [$id, $post_id, $author['id'], $textarea, $jsonData]);
                    $errors['post'] = $id;
                    $post = get_post_info_from_id($post_id);      
                    if ($author['id'] != $post['author']) {
                        make_notification($post['author'], 1, json_encode([
                            'icon' => 'message.svg',
                            'html' => sprintf('<a href="%s/users/%s">%s</a> commented on your <a href="%s">post</a>.', ROOT_LINK, $author['identifier'], json_decode($author['data'], true)['username'], ROOT_LINK . '/posts/' . $post_id),
                            'miscellaneous' => [
                                'creation_time' => time(),
                                'read' => false
                            ]
                        ]));
                    }
                }
                break;
            case 'heart_post':
            case 'heart_reply':
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
                        if ($type == 'heart_reply') {
                            $authorPost = get_reply_info_from_id($id);
                        } else {
                            $authorPost = get_post_info_from_id($id);
                        }
                        if ($authorPost != false) {
                            $heart_type = $type == 'heart_reply' ? 'reply' : 'post';
                            $check = $database->preparedQuery('SELECT count(id) AS result FROM loves WHERE type = ? AND user = ? AND pid = ?', [$heart_type, $yourself['id'], $id])->fetch(PDO::FETCH_ASSOC)['result'] > 0;
                            if (!$check) {
                                $database->preparedQuery('INSERT INTO loves (type,user,profile,pid) VALUES (?,?,?,?)', [$heart_type, $yourself['id'], $authorPost['author'], $id]);
                            } else {
                                $database->preparedQuery('DELETE FROM loves WHERE type = ? AND user = ? AND pid = ?', [$heart_type, $yourself['id'], $id]);
                            }
                            $errors = ['code' => 'success', 'newCount' => $database->preparedQuery('SELECT count(id) AS result FROM loves WHERE type = ? AND pid = ?', [$heart_type, $id])->fetch(PDO::FETCH_ASSOC)['result'], 'totalCount' => $database->preparedQuery('SELECT count(id) AS result FROM loves WHERE type = ? AND profile = ?', [$heart_type, $authorPost['author']])->fetch(PDO::FETCH_ASSOC)['result']];
                            if ($type == 'heart_reply' && $yourself['id'] != $authorPost['author'] && !$check) {
                                make_notification($authorPost['author'], 1, json_encode([
                                    'icon' => 'heart.svg',
                                    'html' => sprintf('<a href="%s/users/%s">%s</a> loved your comment in <a href="%s">post</a>.', ROOT_LINK, $yourself['identifier'], json_decode($yourself['data'], true)['username'], ROOT_LINK.'/posts/' . $authorPost['post']),
                                    'miscellaneous' => [
                                        'creation_time' => time(),
                                        'read' => false
                                    ]
                                ]));
                            } else if ($yourself['id'] != $authorPost['author'] && !$check) {
                                make_notification($authorPost['author'], 1, json_encode([
                                    'icon' => 'heart.svg',
                                    'html' => sprintf('<a href="%s/users/%s">%s</a> loved your <a href="%s">post</a>.', ROOT_LINK, $yourself['identifier'], json_decode($yourself['data'], true)['username'], ROOT_LINK.'/posts/' . $authorPost['id']),
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
                    // There's an error.
                    $errors['code'] = 'unlogged';
                }
                break;
            case 'delete_post':
                $id = $_POST['id'];
                $check = get_post_info_from_id($id);
                
                $yourself = get_user_info_from_token($token);
                if ($check != false) {
                    if ($check['author'] != $yourself['id']) {
                        throw new Exception("Not the post author", 1);
                    }
                    $errors['page'] = 'users/'.$database->preparedQuery('SELECT identifier FROM users WHERE id = ?',[$check['author']])->fetch(PDO::FETCH_ASSOC)['identifier'];           
                    foreach (json_decode($check['data'],true)['images'] as $link) {
                        unlink(DOCUMENT_ROOT . '/static' . substr($link, strlen(STATIC_LINK)));               
                    }
                    $database->preparedQuery('DELETE FROM hashtags WHERE post = ?', [$check['id']]);
                    $database->preparedQuery('DELETE FROM replies WHERE post = ?',[$check['id']]);
                    $database->preparedQuery('DELETE FROM loves WHERE pid = ?',[$check['id']]);
                    $database->preparedQuery('DELETE FROM posts WHERE author = ? AND id = ?',[$yourself['id'],$id]);
                    $errors['code'] = 'success';
                } elseif ($check == false) {
                    $check = get_reply_info_from_id($id);
                    if ($check['author'] != $yourself['id']) {      
                        throw new Exception("Not the reply author", 1);
                    }
                    $errors['page'] = 'posts/'.$check['post'];
                    if ($check != false) {
                        foreach (json_decode($check['data'], true)['images'] as $link) {
                            unlink(DOCUMENT_ROOT . '/static' . substr($link, strlen(STATIC_LINK)));
                        }                        
                        $database->preparedQuery('DELETE FROM subreplies WHERE reply = ?', [$check['id']]);
                        $database->preparedQuery('DELETE FROM replies WHERE post = ? AND id = ?', [$check['post'],$check['id']]);
                        $database->preparedQuery('DELETE FROM loves WHERE pid = ? AND type = ?', [$check['id'],'reply']);
                    }
                    $errors['code'] = 'success';
                }
                break;
            case 'report':
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
                        $mode = 'reply';
                    }
                    $jsonData = json_encode([
                        'user' => $user,
                        'miscelleanous' => [
                            'creation_time' => time()
                        ]
                    ]);
                    $database->preparedQuery('INSERT INTO reports (type,pid,reason,description,data) VALUES (?,?,?,?,?)', [$mode, $post_id, $reason, $textarea, $jsonData]);
                    $errors['code'] = 'success';
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