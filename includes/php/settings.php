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
        $description = $_POST['description'];
        $directory = DOCUMENT_ROOT.'/static/uploads/';
        $yourself = get_user_info_from_token($token);
        $id = $yourself['id'];
        $type = $_POST['type'];
        set_timeout('settings');
        $timeout = get_timeout('settings');
        if (time() > $timeout['time'] + 15) {
            remove_timeout('settings');
            $timeout['count'] = 0;
        }
        if ($timeout['count'] <= 8) {
            switch ($type) {
                case 'generate_link':
                    $token = $_COOKIE['token'];
                    if (check_user_ban($token)) {
                        throw new Exception("User is banned", 1);
                    }

                    if (!$account = get_user_info_from_token($token)) {
                        throw new Exception("User is not logged", 1);
                    }
                    $account = $account['id'];
                    $referrer = bin2hex(openssl_random_pseudo_bytes(8));
                    $jsonData = json_encode([
                        'miscellaneous' => [
                            'creation_time' => time()
                        ]
                    ]);
                    $database->preparedQuery('INSERT INTO invites (account,referrer,data) VALUES (?,?,?)',[$account,$referrer,$jsonData]);
                    $errors = ['code' => 'success', 'referrer' => $referrer];
                    break;
                case 'logout_device':
                    $token = $_POST['token'];
                    if ($database->preparedQuery('DELETE FROM tokens WHERE token = ?',[$token])) {
                        $errors['code'] = 'success';
                    }
                    break;
                case 'logout_devices':
                    $token = $_COOKIE['token'];
                    if ($database->preparedQuery('DELETE FROM tokens WHERE token != ?', [$token])) {
                        $errors['code'] = 'success';
                    }
                    break;
                case 'profile':
                    $banned = check_user_ban($token);
                    if ($banned) {
                        throw new Exception("User is banned", 1);
                    }

                    if (!empty($_FILES['icon']['tmp_name']) && sha1_file($directory . basename(json_decode($yourself['data'], true)['profile']['icon'])) != sha1_file($_FILES['icon']['tmp_name'])) {
                        $errors['icon'] = upload_banner_image('icon', 8000000, $database, $directory, $id, true);
                    } else {
                        $errors['icon'] = 'success';
                    }

                    if (!empty($_FILES['banner']['tmp_name']) && sha1_file($directory . basename(json_decode($yourself['data'], true)['profile']['banner'])) != sha1_file($_FILES['banner']['tmp_name'])) {
                        $errors['banner'] = upload_banner_image('banner', 8000000, $database, $directory, $id);
                    } else {
                        $errors['banner'] = 'success';
                    }

                    if (strlen($_POST['description']) <= 1024) {
                        $check = json_decode($database->preparedQuery('SELECT data FROM users WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC)['data'], true);
                        $check['profile']['description'] = $_POST['description'];
                        $check = json_encode($check);
                        $database->preparedQuery('UPDATE users SET data=? WHERE id = ?', [$check, $id]);
                        $errors['description'] = 'success';
                    } else {
                        $errors['description'] = 'length';
                    }

                    if ($errors['description'] == 'success' && $errors['icon'] == 'success' && $errors['banner'] == 'success') {
                        $errors = ['code' => 'success'];
                    }
                    break;
                case 'delete':
                    $password = $_POST['password'];

                    // Check password.
                    if (empty($password)) {
                        $errors['password'] = 'empty';
                    } elseif (!isset($yourself['password']) || !password_verify($password, $yourself['password'])) {
                        $errors['password'] = 'invalid';
                    } else {
                        $errors['password'] = 'success';
                    }

                    if ($errors['password'] == 'success') {
                        try {
                            $posts = $database->preparedQuery('SELECT id FROM posts WHERE author = ?', [$yourself['id']])->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($posts as $post) {
                                $database->preparedQuery('DELETE FROM hashtags WHERE post = ?', [$post['id']]);
                            }
                            $database->preparedQuery('DELETE FROM posts WHERE author = ?', [$yourself['id']]);
                            $replies = $database->preparedQuery('SELECT id FROM replies WHERE author = ?', [$yourself['id']])->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($replies as $reply) {
                                $database->preparedQuery('DELETE FROM hashtags WHERE post = ?', [$reply['id']]);
                            }
                            $database->preparedQuery('DELETE FROM replies WHERE author = ?', [$yourself['id']]);
                            $database->preparedQuery('DELETE FROM loves WHERE user = ?', [$yourself['id']]);
                            $database->preparedQuery('DELETE FROM verifications WHERE account = ?', [$yourself['id']]);
                            $database->preparedQuery('DELETE FROM users WHERE id = ?', [$yourself['id']]);
                            $database->preparedQuery('DELETE FROM tokens WHERE account = ?', [$yourself['id']]);
                            $database->preparedQuery('DELETE FROM notifications WHERE user = ?', [$yourself['id']]);
                            $database->preparedQuery('DELETE FROM follows WHERE follower = ?', [$yourself['id']]);
                            $database->preparedQuery('DELETE FROM follows WHERE following = ?', [$yourself['id']]);
                            $database->preparedQuery('DELETE FROM bans WHERE user = ?', [$yourself['id']]);
                            $errors = ['code' => 'success'];
                        } catch (Exception $error) {
                            // There's an error.
                            $errors = ['code' => 'fail', 'error' => $error->getMessage()];
                        }
                    }
                    break;
                case 'theme':
                    $theme = $_POST['theme'];
                    $data = json_decode($yourself['data'], true);
                    $data['miscellaneous']['theme'] = $theme;
                    $data = json_encode($data);
                    $database->preparedQuery('UPDATE users SET data = ? WHERE id = ?', [$data, $yourself['id']]);
                    $errors = ['code' => 'success'];
                    break;
                default:
                    $errors = ['code' => 'fail', 'error' => 'Invalid type'];
                    break;
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
echo json_encode($errors);