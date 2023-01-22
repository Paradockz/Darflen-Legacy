<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once DOCUMENT_ROOT . '\vendor\autoload.php';

function head(string $title = '', string $lang = 'en_', string $css = '', bool $navbar = true, $description = '', $icon = '', $author = WEBSITE, bool $index = true, string $type = 'summary', string $ogtype = 'website') {
    include_once DOCUMENT_ROOT . '\static\html\partial\head.php';
    if ($navbar) {
        navbar($title);
    }
}

require_once(DOCUMENT_ROOT . '\includes\php\library.php');

function navbar(string $title) {
    include_once DOCUMENT_ROOT . '\static\html\partial\header.php';
}

function footer() {
    include_once DOCUMENT_ROOT . '\static\html\partial\footer.php';
}

function userCard(int $id, bool $big = false) {
    include DOCUMENT_ROOT . '\static\html\partial\userCard.php';
}

function hashtag($title,$link,$text) {
    include DOCUMENT_ROOT . '\static\html\partial\hashtag.php';
}

function loggedDevice($tag, $data, $token) {
    include DOCUMENT_ROOT . '\static\html\partial\loggedDevice.php';
}

function video($video, $poster, $mime, $controls = false) {
    include DOCUMENT_ROOT . '\static\html\partial\video.php';
}

function audio($audio, $mime, $controls = false) {
    include DOCUMENT_ROOT . '\static\html\partial\audio.php';
}


function internalMicroStat(string $title, string $desc, string $image = '', string $link = '') {
    include DOCUMENT_ROOT . '\static\html\partial\microStat.php';
}

function internalTableInfo(array $user, array $user_data) {
    include DOCUMENT_ROOT . '\static\html\partial\tableInfo.php';
}

function d($filename,$icon,$html, $time) {
    if (is_file($filename)) {
        ob_start();
        include $filename;
        return ob_get_clean();
    }
    return false;
}

function notification($icon,$html, $time) {
    return d(DOCUMENT_ROOT . '\static\html\partial\notification.php',$icon,$html, $time);
}

function prepare_database() {
    return new DB(CONFIG['database']['host'], CONFIG['database']['database'], CONFIG['database']['username'], CONFIG['database']['password'], CONFIG['database']['port']);
}

function redirect_if_logged(string $location) {
    if (isset($_COOKIE['token'])) {
        if (check_token_validity($_COOKIE['token'])) {
            header('Location:' . $location);
        } else {
            setcookie('token', '', time() - 36000, '/');
        }
    }
}

function redirect_if_not_logged(string $location) {
    if (isset($_COOKIE['token'])) {
        if (!check_token_validity($_COOKIE['token'])) {
            setcookie('token', '', time() - 36000, '/');
            header('Location:' . $location);
        }
    } else {
        header('Location:' . $location);
    }
}

function redirect_if_user_does_not_exist(int $id) {
    $database = prepare_database();
    $check = $database->preparedQuery('SELECT count(id) AS result FROM users WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC)['result'];
    if ($check < 1) {
        http_response_code(404);
        include_once 'T:\laragon\htdocs\errors\404.php';
        exit;
    }
}

function check_token_validity(string $token) {
    $database = prepare_database();
    $check = $database->preparedQuery('SELECT count(id) AS result FROM tokens WHERE token = ?', [$token])->fetch(PDO::FETCH_ASSOC)['result'];
    return $check > 0;
}

function check_user_ban($token) {
    $database = prepare_database();
    $check = $database->preparedQuery('SELECT count(id) AS result FROM bans WHERE user = ?', [$token])->fetch(PDO::FETCH_ASSOC)['result'];
    return $check > 0;
}

function get_user_info_from_token(string $token) {
    $database = prepare_database();
    $check = $database->preparedQuery('SELECT count(id) AS result,account FROM tokens WHERE token = ?', [$token])->fetch(PDO::FETCH_ASSOC);
    if ($check['result'] > 0) {
        return $database->preparedQuery('SELECT id,email,password,data,identifier FROM users WHERE id = ?', [$check['account']])->fetch(PDO::FETCH_ASSOC);
    } else {
        return false;
    }
}

function get_user_info_from_id(int $id) {
    $database = prepare_database();
    $check = $database->preparedQuery('SELECT count(id) AS result,id FROM users WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC);
    if ($check['result'] > 0) {
        return $database->preparedQuery('SELECT id,email,data,identifier FROM users WHERE id = ?', [$check['id']])->fetch(PDO::FETCH_ASSOC);
    } else {
        return false;
    }
}

function get_user_info_from_identifier(string $id) {
    $database = prepare_database();
    $check = $database->preparedQuery('SELECT id,count(id) AS result FROM users WHERE identifier = ?', [$id])->fetch(PDO::FETCH_ASSOC);
    if ($check['result'] > 0) {
        return $database->preparedQuery('SELECT id,email,password,data,identifier FROM users WHERE id = ?', [$check['id']])->fetch(PDO::FETCH_ASSOC);
    } else {
        return false;
    }
}

function get_user_follows_from_id(int $id) {
    $database = prepare_database();
    $check = $database->preparedQuery('SELECT count(id) AS result,id FROM users WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC);
    if ($check['result'] > 0) {
        return $database->preparedQuery('SELECT id,follower,following FROM follows WHERE id = ?', [$check['id']])->fetch(PDO::FETCH_ASSOC);
    } else {
        return false;
    }
}


class Users {
    private $database;
    private $user;
    private $type;

    public function __construct($user, $type = 'identifier') {
        $this->database = prepare_database();
        $this->type = $type;
        $this->user = $user;
        $this->data = $this->database->preparedQuery(sprintf('SELECT count(id) AS result,id FROM users WHERE %s = ?',$this->type), [$this->user])->fetch(PDO::FETCH_ASSOC);
        if ($this->type === 'token') {
            # code...
        }
        if ($this->data['result'] === 0) {
            return false;
        }
    }
    
    public function get($item) {
        switch ($item) {
            case 'info':
                return $this->database->preparedQuery('SELECT id,identifier,email,password,data FROM users WHERE id = ?', [$this->user])->fetch(PDO::FETCH_ASSOC);
                break;
            case 'follows':
                return $this->database->preparedQuery('SELECT id,follower,following FROM follows WHERE id = ?', [$this->user])->fetch(PDO::FETCH_ASSOC);
                break;
            default:
                return false;
                break;
        }
    }
}

function bing($database, $input, $array, $item, $tresh, $user) {
    $jsonData = json_encode([
        "miscellaneous" => [
            "creation_time" => time()
        ]
    ]);
    if ($input >= $tresh && !in_array($item, $array)) {
        $database->preparedQuery("INSERT INTO badges (badge,account,data) VALUES (?,?,?)", [$item, $user["id"], $jsonData]);
    } else if ($input <= $tresh && in_array($item, $array)) {
        $database->preparedQuery("DELETE FROM badges WHERE account = ? AND badge = ?", [$user["id"], $item]);
    }
}

function update_follow_badge(array $user, int $follows) {
    $database = prepare_database();
    $data_follow = $database->preparedQuery("SELECT badge FROM badges WHERE account = ?",[$user["id"]])->fetchAll(PDO::FETCH_ASSOC);
    $follow = [];
    foreach ($data_follow as $value) {
        array_push($follow,$value['badge']);
    }

    bing($database, $follows, $follow, "user-10", 10, $user);
    bing($database, $follows, $follow, "user-50", 50, $user);
    bing($database, $follows, $follow, "user-100", 100, $user);
}

function update_views_badge(array $user, int $views) {
    $database = prepare_database();
    $data_follow = $database->preparedQuery("SELECT badge FROM badges WHERE account = ?",[$user["id"]])->fetchAll(PDO::FETCH_ASSOC);
    $follow = [];
    foreach ($data_follow as $value) {
        array_push($follow,$value['badge']);
    }

    bing($database, $views, $follow, "view-100", 100, $user);
    bing($database, $views, $follow, "view-250", 250, $user);
    bing($database, $views, $follow, "view-500", 500, $user);
    bing($database, $views, $follow, "view-1000", 1000, $user);
    bing($database, $views, $follow, "view-5000", 5000, $user);
    bing($database, $views, $follow, "view-10000", 10000, $user);
}

function update_loves_badge(array $user, int $loves) {
    $database = prepare_database();
    $data_follow = $database->preparedQuery("SELECT badge FROM badges WHERE account = ?",[$user["id"]])->fetchAll(PDO::FETCH_ASSOC);
    $follow = [];
    foreach ($data_follow as $value) {
        array_push($follow,$value['badge']);
    }

    bing($database, $loves, $follow, "heart-1", 1, $user);
    bing($database, $loves, $follow, "heart-10", 10, $user);
    bing($database, $loves, $follow, "heart-50", 50, $user);
    bing($database, $loves, $follow, "heart-100", 100, $user);
    bing($database, $loves, $follow, "heart-500", 500, $user);
}

function update_posts_badge(array $user, int $posts) {
    $database = prepare_database();
    $data_follow = $database->preparedQuery("SELECT badge FROM badges WHERE account = ?",[$user["id"]])->fetchAll(PDO::FETCH_ASSOC);
    $follow = [];
    foreach ($data_follow as $value) {
        array_push($follow,$value['badge']);
    }

    bing($database, $posts, $follow, "message-10", 10, $user);
    bing($database, $posts, $follow, "message-50", 50, $user);
    bing($database, $posts, $follow, "message-100", 100, $user);
}

function update_settings_badge(array $user, int $invites, int $activated) {
    $database = prepare_database();
    $data_follow = $database->preparedQuery("SELECT badge FROM badges WHERE account = ?",[$user["id"]])->fetchAll(PDO::FETCH_ASSOC);
    $follow = [];
    foreach ($data_follow as $value) {
        array_push($follow,$value['badge']);
    }

    bing($database, $invites, $follow, "invite-1", 1, $user);
    bing($database, $invites, $follow, "invite-3", 3, $user);
    bing($database, $invites, $follow, "invite-6", 6, $user);
    bing($database, $activated, $follow, "activated-1", 1, $user);
    bing($database, $activated, $follow, "activated-3", 3, $user);
    bing($database, $activated, $follow, "activated-6", 6, $user);
}

// I don't know why it is here BUT DO NOT REMOVE IT!
function regex_user($regex) {
    $database = prepare_database();
    $result = $database->preparedQuery('SELECT count(id) AS result, identifier FROM users WHERE JSON_VALUE(data,"$.username") = ?',[$regex])->fetch(PDO::FETCH_ASSOC);
    
    if ($result['result'] < 1) {
        return '@'.$regex;
    }
    return $result['identifier'];
}

function parse_post_text($text) {
    $rules = [
        '/(\_\_|__)(.*?)\1/' => '<ins>\2</ins>',
        '/(\`\`\`)([^<]+?)\1/m' => function($regex) {
            return '<pre>'.trim($regex[2], "\x00..\x1F").'</pre>';
        },
        '/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/' => '<object><a href="\0">\0</a></object>',
        '/(\*\*)(.*?)\1/' => '<strong>\2</strong>',
        '/(\`\`)(.*?)\1/' => '<code>\2</code>',
        '/(\|\|)(.*?)\1/' => '<span class="text-spoiler" tabindex="0">\2</span>',
        '/(\*)(.*?)\1/' => '<em>\2</em>',
        '/(\`)(.*?)\1/' => '<code>\2</code>',
        '/\~\~(.*?)\~\~/' => '<del>\1</del>',
        '/#([a-zA-Z0-9_-]+)/' => '<object title="\1"><a href="'.ROOT_LINK.'/explore/hashtags/\1">\0</a></object>',
        '/@([a-zA-Z0-9_-]+)/' => function ($regex) {
            $database = prepare_database();
            $result = $database->preparedQuery('SELECT count(id) AS result, identifier FROM users WHERE JSON_VALUE(data,"$.username") = ?', [$regex[1]])->fetch(PDO::FETCH_ASSOC);
            if ($result['result'] < 1) {
                return '@' . $regex[1];
            }
            return sprintf('<object><a href="'.ROOT_LINK.'/users/%s">%s</a></object>',$result['identifier'],$regex[0]);
        }
    ];
    $text = htmlspecialchars($text,ENT_NOQUOTES);
    foreach ($rules as $regex => $replacement) {
        if (is_callable($replacement)) {
            $text = preg_replace_callback($regex, $replacement, $text);
        } else {
            $text = preg_replace($regex, $replacement, $text);
        }
    }
    $text = str_replace(["\r\n", "\r", "\n"], "<br/>", $text);
    return $text;
}

function is_image($path) {
    $a = getimagesize($path);
    $image_type = $a[2];
    if (in_array($image_type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_SWF, IMAGETYPE_BMP, IMAGETYPE_WEBP))) {
        return true;
    }
    return false;
}

function get_user_posts($id, $limit = PHP_INT_MAX, $offset = 0) {
    $database = prepare_database();
    $check = $database->preparedQuery('SELECT count(id) AS result,id FROM users WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC);
    if ($check['result'] > 0) {
        return $database->preparedQuery('SELECT id,author,text,data, JSON_VALUE(data, "$.miscellaneous.creation_time") AS creation FROM posts WHERE author = ? ORDER BY creation DESC LIMIT ?,?', [$id, $offset, $limit])->fetchAll(PDO::FETCH_ASSOC);
    } else {
        return false;
    }
}

function get_user_total_posts($id) {
    $database = prepare_database();
    $check = $database->preparedQuery('SELECT count(id) AS result,id FROM users WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC);
    if ($check['result'] > 0) {
        return $database->preparedQuery('SELECT count(id) AS result FROM posts WHERE author = ? AND JSON_VALUE(data,"$.miscellaneous.coverage") = ?', [$id,'public'])->fetch(PDO::FETCH_ASSOC)['result'];
    } else {
        return false;
    }
}

function get_user_post_replies($post) {
    $database = prepare_database();
    $check = $database->preparedQuery('SELECT count(id) AS result FROM posts WHERE id = ?', [$post])->fetch(PDO::FETCH_ASSOC);
    if ($check['result'] > 0) {
        return $database->preparedQuery('SELECT id,author,text,data, JSON_VALUE(data, "$.miscellaneous.creation_time") AS creation FROM replies WHERE post = ? ORDER BY creation DESC', [$post])->fetchAll(PDO::FETCH_ASSOC);
    } else {
        return false;
    }
}

function get_user_reply_subreplies($post) {
    $database = prepare_database();
    $check = $database->preparedQuery('SELECT count(id) AS result FROM replies WHERE id = ?', [$post])->fetch(PDO::FETCH_ASSOC);
    if ($check['result'] > 0) {
        return $database->preparedQuery('SELECT id,author,text,data, JSON_VALUE(data, "$.miscellaneous.creation_time") AS creation FROM subreplies WHERE reply = ? ORDER BY creation DESC', [$post])->fetchAll(PDO::FETCH_ASSOC);
    } else {
        return false;
    }
}

function get_post_info_from_id($id) {
    $database = prepare_database();
    $check = $database->preparedQuery('SELECT count(id) AS result FROM posts WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC);
    if ($check['result'] > 0) {
        return $database->preparedQuery('SELECT id,author,text,data, JSON_VALUE(data, "$.miscellaneous.creation_time") AS creation FROM posts WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC);
    } else {
        return false;
    }
}

function get_post_with_reposts_from_id($id) {
    $database = prepare_database();
    $check = $database->preparedQuery('SELECT count(id) AS result FROM posts WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC);
    if ($check['result'] > 0) {
        return $database->preparedQuery('SELECT count(id) AS result, data FROM posts WHERE JSON_EXISTS(data,"$.type") AND JSON_VALUE(data, "$.type.id") = ?', [$id])->fetch(PDO::FETCH_ASSOC)['result'];
    } else {
        return false;
    }
}

function get_reply_info_from_id($id) {
    $database = prepare_database();
    $check = $database->preparedQuery('SELECT count(id) AS result FROM replies WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC);
    if ($check['result'] > 0) {
        return $database->preparedQuery('SELECT id,post,author,text,data, JSON_VALUE(data, "$.miscellaneous.creation_time") AS creation FROM replies WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC);
    } else {
        return false;
    }
}

function get_subreply_info_from_id($id) {
    $database = prepare_database();
    $check = $database->preparedQuery('SELECT count(id) AS result FROM subreplies WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC);
    if ($check['result'] > 0) {
        return $database->preparedQuery('SELECT id,reply,author,text,data, JSON_VALUE(data, "$.miscellaneous.creation_time") AS creation FROM subreplies WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC);
    } else {
        return false;
    }
}

function send_mail(string $email, string $subject, string $html, string $text) {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = CONFIG['email']['host'];
    $mail->SMTPAuth = CONFIG['email']['auth'] == true;
    $mail->Username = CONFIG['email']['username'];
    $mail->Password = CONFIG['email']['password'];
    $mail->SMTPSecure = CONFIG['email']['security'];
    $mail->Port = CONFIG['email']['port'];
    $mail->setFrom(CONFIG['email']['username'], WEBSITE);
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $html;
    $mail->AltBody = $text;
    $mail->send();
}

function time_ago($from) {
    $diff  = time() - $from;
    $range = [
        'decade' => 315360000,
        'year' => 31536000,
        'month' => 2592000,
        'day' => 86400,
        'week' => 604800,
        'hour' => 3600,
        'min' => 60,
        'second' => 1
    ];
    foreach ($range as $unit => $sec) {
        if ($diff > $sec) {
            $round = round($diff / $sec);
            break;
        }
    }
    if (empty($round)) {
        return 'just now';
    } else {
        return sprintf('%d %s%s ago', $round, $unit, $round > 1 ? 's' : '');
    }
}

function create_image($source) {
    $info = getimagesize($source);
    switch ($info['mime']) {
        case 'image/jpeg':
            return imagecreatefromjpeg($source);
        case 'image/gif':
            return imagecreatefromgif($source);
        case 'image/png':
            return imagecreatefrompng($source);
        case 'image/bmp':
            return imagecreatefrombmp($source);
        case 'image/webp':
            return imagecreatefromwebp($source);
        default:
            throw new Exception("Invalid image mime type", 1);
            break;
    }
}

function compress_image($source,$destination,$quality) {
	try {
        $image = create_image($source);
        imagejpeg($image, $destination, $quality);
        // Free up memory.
        imagedestroy($image);
        return true;
    } catch (\Throwable $e) {
        throw new Exception($e->getMessage(), 1);
        return false;
    }
}

function resize_image($source, $destination, $percentage) {
    try {
        list($width, $height) = getimagesize($source);
        $new_w = $width * $percentage;
        $new_h = $height * $percentage;
        
        $output = imagecreatetruecolor($new_h, $new_w);
        $image = create_image($source);
        imagecopyresampled($output, $image, 0, 0, 0, 0, $new_w, $new_h, $width, $height);
        imagejpeg($output, $destination);
        // Free up memory.
        imagedestroy($image);
        return true;
    } catch (\Throwable $e) {
        throw new Exception($e->getMessage(), 1);
        return false;
    }
}

function get_darflen_upload_file_name(string $link, string $file) {
    try {
        $file = explode($link, $file)[1];
        return DOCUMENT_ROOT . '/static/' . $file;
    } catch (Exception) {
        return false;
    }
}

function get_file_mime_from_location(string $location) {
    try {
        $file_type = strtolower(mime_content_type($location));
        return $file_type;
    } catch (Exception) {
        return false;
    }
}

function get_file_mime_from_link(string $link) {
    try {
        $file_type = strtolower(mime_content_type(get_darflen_upload_file_name(STATIC_LINK."/", $link)));
        return $file_type;
    } catch (Exception) {
        return false;
    }
}

function get_file_type_from_location(string $location) {
    try {
        $file_type = strtolower(pathinfo($location, PATHINFO_EXTENSION));
        return $file_type;
    } catch (Throwable) {
        return false;
    }
}

function get_file_type(string $type) {
    return explode('/', $type)[0];
}

function upload_post_file($file,$directory,$resize = false, $percentage = 80) {
    $upload = new Upload($directory);
    switch (get_file_type($file['type'])) {
        case 'image':
            try {
                if ($resize) {
                    $upload::file($file)->compress($percentage)->rescale($percentage);
                } else {
                    $upload::file($file)->compress($percentage);
                }
                return $upload::$file["directory"];
            } catch (Exception $e) {
                return 'upload';
            }
            break;
        case 'video':
            try {
                $upload::file($file)->thumbnail()->compress($percentage);
                return $upload::$file["directory"];
            } catch (Exception $e) {
                return 'upload';
            }
            break;
        case 'audio':
            try {
                $upload::file($file)->compress($percentage);
                return $upload::$file["directory"];
             } catch (Exception $e) {
                return 'upload';
            }
            break;
        default:
            return 'type';
    }
    return get_file_type($file['type']);
}

/* Deprecated
function upload_post_image($name,$directory,$resize = false, $percentage = 1) {
    $directory_file = $directory . basename($_FILES[$name]["name"]);
    $image_type = strtolower(pathinfo($directory_file, PATHINFO_EXTENSION));
    $_FILES[$name]['name'] = sha1(openssl_random_pseudo_bytes(32)) . '.' . $image_type;
    $directory_file = $directory . basename($_FILES[$name]["name"]);
    if (move_uploaded_file($_FILES[$name]['tmp_name'], $directory_file)) {
        if ($image_type != 'gif') {
            if (!compress_image($directory_file, $directory_file, 40)) {
                return 'upload';
            }
        }
        if (!$resize) {
            return $directory_file;
        }
        if (resize_image($directory_file,$directory_file,$percentage)) {
            return $directory_file;
        } else {
            return 'upload';
        }
    } else {
        return 'upload';
    }
}
*/

function upload_mass_files($images, $upload_statement) {
    // Check images.
    $directory = DOCUMENT_ROOT . '/static/uploads/';
    $images_dir = [];
    if (count($images) > 0 && !empty($images[0]['name'])) {
        if (count($_FILES) > 10) {
            $error = 'length';
        } else {
            $total_size = 0;
            foreach ($images as $image) {
                $total_size += $image['size'];
            }
            if ($total_size > 50000000) {
                $error = 'tSize';
            } else {
                foreach ($images as $image) {
                    if ($image['size'] > 8000000) {
                        $error = 'eSize';
                    }
                }
                if ($error != 'eSize') {
                    if ($error != 'type') {
                        if ($upload_statement == true) {
                            for ($index = 0; $index  <= count($images) - 1; $index++) {
                                $result = upload_post_file($_FILES[$index], $directory);
                                if ($result == 'upload') {
                                    $error = 'upload';
                                    break;
                                }
                                if ($result == 'type') {
                                    $error = 'type';
                                    break;
                                }
                                array_push($images_dir, STATIC_LINK.'/uploads/' . substr($result, 42));
                            }
                            if ($error != 'upload' && $error != 'type') {
                                $error = ['code' => 'success', 'imgs' => $images_dir];
                            }
                        } else {
                            $error = 'success';
                        }
                    }
                }
            }
        }
    } else {
        $error = 'success';
    }
    return $error;
}

function unindex_posts_not_public($result,$database) {
    $yourself = [];
    if (isset($_COOKIE['token']) && check_token_validity($_COOKIE['token'])) {
        $yourself = get_user_info_from_token($_COOKIE['token']);
    }
    $yourself['id'] = isset($yourself['id']) ? $yourself['id'] : 0;
    foreach ($result as $index => $data) {
        $following = $database->preparedQuery('SELECT count(id) AS following FROM follows WHERE follower = ? AND following = ?', [$yourself['id'], $data['author']])->fetch(PDO::FETCH_ASSOC)['following'] > 0 || $yourself['id'] == $data['author'];
        $banned = $database->preparedQuery('SELECT count(id) AS result FROM bans WHERE user = ?',[$data['author']])->fetch(PDO::FETCH_ASSOC)['result'] > 0;
        # throw new Exception(json_encode($following), 1);
        $cover = json_decode($data['data'], true)['miscellaneous']['coverage'];
        if ($yourself['id'] != $data['author']) {
            if ($cover == 'followers' && !$following || in_array($cover, ['private', 'unlisted']) || $banned) {
                unset($result[$index]);
            }
        }
    }
    return $result;
}

function recommend_user_posts(string $type = 'trending', int $limit = PHP_INT_MAX, int $offset = 0, $query = '') {
    $database = prepare_database();
    switch ($type) {
        case 'trending':
            $result = $database->preparedQuery('SELECT id, author, text, data, JSON_VALUE(data, "$.miscellaneous.creation_time") AS creation, JSON_VALUE(posts.data, "$.miscellaneous.coverage") AS coverage, COALESCE(ROUND((JSON_VALUE(data, "$.miscellaneous.hits") - JSON_VALUE(data, "$.miscellaneous.hits_last_time")) / JSON_VALUE(data, "$.miscellaneous.hits_last_time") * 100),0) AS tscore FROM posts ORDER BY tscore DESC LIMIT ?,?', [$offset, $limit])->fetchAll(PDO::FETCH_ASSOC);
            $result = unindex_posts_not_public($result, $database);
            break;
        case 'search':
            if (!empty($query) && $query[0] == '#') {
                $tags = array_iunique(preg_split('/[#]+/', $query));
                array_shift($tags);
                $query = '';
                $params = [];
                foreach ($tags as $tag) {
                    $query .= 'hashtags.hashtag = ? OR ';
                    array_push($params,(trim($tag)));
                }
                $query = rtrim($query,' OR ');
                array_push($params,$offset,$limit);
                $result = $database->preparedQuery('SELECT posts.id, posts.author, posts.text, posts.data, JSON_VALUE(posts.data, "$.miscellaneous.creation_time") AS creation FROM posts INNER JOIN hashtags ON hashtags.post = posts.id WHERE '.$query.' ORDER BY creation DESC LIMIT ?,?', $params)->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $tags = [];
                $result = [];
            }
            if (count($result) < 1 && count($tags) == 0) {
                $result = $database->preparedQuery('SELECT posts.id,posts.author,posts.text,posts.data, JSON_VALUE(posts.data, "$.miscellaneous.creation_time") AS creation, JSON_VALUE(posts.data, "$.miscellaneous.coverage") AS coverage, COALESCE(ROUND((JSON_VALUE(data, "$.miscellaneous.hits") - JSON_VALUE(data, "$.miscellaneous.hits_last_time")) / JSON_VALUE(data, "$.miscellaneous.hits_last_time") * 100),0) AS tscore FROM posts INNER JOIN(SELECT id, JSON_VALUE(users.data, "$.username") AS username FROM users) AS user ON posts.author = user.id WHERE MATCH(posts.text) AGAINST(? IN NATURAL LANGUAGE MODE) OR posts.text LIKE ? OR posts.id = ? OR MATCH(posts.meta) AGAINST(? IN NATURAL LANGUAGE MODE) OR posts.meta LIKE ? OR user.username LIKE ? OR user.id = ? ORDER BY tscore DESC LIMIT ?,?', [$query, '%' . $query . '%', $query, metaphone($query), '%' . metaphone($query) . '%', '%' . $query . '%', $query, $offset, $limit])->fetchAll(PDO::FETCH_ASSOC);
            }
            $result = unindex_posts_not_public($result,$database);
            break;
        case 'loved':
            // SPAGHETTI!
            $result = $database->preparedQuery('SELECT DISTINCT posts.id, posts.author, posts.text, posts.data, JSON_VALUE(posts.data, "$.miscellaneous.creation_time") AS creation FROM posts LEFT JOIN(SELECT pid,count(id) AS lovescount FROM loves GROUP BY pid) AS loves ON posts.id = loves.pid LEFT JOIN (SELECT id,post,count(id) AS repliescount FROM replies GROUP BY id) AS replies ON posts.id = replies.post GROUP by posts.id ORDER BY loves.lovescount DESC, replies.repliescount DESC LIMIT ?,?', [$offset, $limit])->fetchAll(PDO::FETCH_ASSOC);
            $result = unindex_posts_not_public($result, $database);
            break;
        case 'popular':
            // SPAGHETTI!
            $result = $database->preparedQuery('SELECT DISTINCT posts.id, posts.author, posts.text, posts.data, JSON_VALUE(posts.data, "$.miscellaneous.creation_time") AS creation FROM posts ORDER BY JSON_VALUE(posts.data, "$.miscellaneous.hits")+0 DESC LIMIT ?,?', [$offset, $limit])->fetchAll(PDO::FETCH_ASSOC);
            $result = unindex_posts_not_public($result, $database);
            break;
        case 'recent':
            $result = $database->preparedQuery('SELECT id,author,text,data, JSON_VALUE(data, "$.miscellaneous.creation_time") AS creation FROM posts ORDER BY creation DESC LIMIT ?,?', [$offset, $limit])->fetchAll(PDO::FETCH_ASSOC);
            $result = unindex_posts_not_public($result,$database);
            break;
        case 'hashtags':
            $result = $database->preparedQuery('SELECT posts.id, posts.author, posts.text, posts.data, JSON_VALUE(posts.data, "$.miscellaneous.creation_time") AS creation FROM posts INNER JOIN hashtags ON hashtags.post = posts.id WHERE hashtags.hashtag = ? ORDER BY creation DESC LIMIT ?,?', [$query,$offset, $limit])->fetchAll(PDO::FETCH_ASSOC);
            $result = unindex_posts_not_public($result,$database);
            break;
        case 'feed':
            $result = $database->preparedQuery('SELECT posts.id, posts.author, posts.text, posts.data, JSON_VALUE(posts.data, "$.miscellaneous.creation_time") AS creation FROM posts INNER JOIN(SELECT follower,following FROM follows) AS yes ON posts.author = yes.following WHERE yes.follower = ? ORDER BY creation DESC LIMIT ?,?',[$query,$offset, $limit])->fetchAll(PDO::FETCH_ASSOC);
            $result = unindex_posts_not_public($result,$database);
            break;
        default:
            $result = [];
            break;
    }
    return $result;
}

function recommend_hashtags(string $type = 'hashtags', int $limit = PHP_INT_MAX, int $offset = 0, $query = '') {
    $database = prepare_database();
    switch ($type) {
        case 'hashtags':
            $result = $database->preparedQuery("SELECT hashtag,COUNT(id) as count FROM hashtags GROUP BY hashtag ORDER BY count DESC LIMIT ?,?", [$offset, $limit])->fetchAll(PDO::FETCH_ASSOC);
            break;
        default:
            return false;
            break;
    }
    return $result;
}

// Should used a html file include but no.
function build_post($post, $user, $yourself, $replies, $loved, $loves, $link = true, $side = true, $vidcontrols = false) {
    $a1 = '';
    if ($side) {
        if ($post['author'] == $yourself['id']) {
            $a1 = sprintf('
        <a href="%s/posts/%s/edit" class="profile-post-aside-button">
            <img src="%s/img/icons/interface/edit-alt.svg" loading="lazy" alt="Edit post">
        </a>
        ', ROOT_LINK, $post['id'], STATIC_LINK);
        } elseif (isset($_COOKIE['token'])) {
            $a1 = sprintf('
        <a href="%s/posts/%s/report" class="profile-post-aside-button">
            <img src="%s/img/icons/interface/flag.svg" loading="lazy" alt="Report post">
        </a>
        ', ROOT_LINK, $post['id'], STATIC_LINK);
        }
    }
    $a2 = '';
    if (!empty(json_decode($post['data'], true)['images'])) {
        foreach (json_decode($post['data'], true)['images'] as $image) {
            if (file_exists(DOCUMENT_ROOT . '/static/' . ltrim($image, STATIC_LINK . '/'))) {
                $mime = get_file_mime_from_link($image);
                switch (get_file_type($mime)) {
                    case 'image':
                        $a2 .= sprintf('<img class="profile-post-image" src="%s" alt="Post image" loading="lazy">', $image);
                        break;
                    case 'video':
                        ob_start();
                        ob_clean();
                        video($image, explode('.mp4', $image)[0] . 't.jpg', $mime, $vidcontrols);
                        $a2 .= ob_get_clean();
                        break;
                    case 'audio':
                        ob_start();
                        ob_clean();
                        audio($image, $mime, $vidcontrols);
                        $a2 .= ob_get_clean();
                        break;
                }
            }
        }
    }
    $a3 = $loved ? 'profile-post-enabled' : '';
    $a4_reposts = get_post_with_reposts_from_id($post['id']);
    $a4 = '';
    if (!empty(json_decode($post['data'], true)['type'])) {
        $database = prepare_database();
        $a4 = json_decode($post['data'], true)['type']['id'];
        $a4_id = $a4;
        if (!$a4) {
            $a4 = '<strong>The reshare post is not a public reshare or does not exist anymore.</strong>';
        }
        $a4 = get_post_info_from_id($a4);
        if (isset($a4['author'])) {
            $a4_user = get_user_info_from_id($a4['author']);
            $a4_loves = $database->preparedQuery('SELECT count(id) AS loves FROM loves WHERE type = ? AND pid = ?', ['post', $a4['id']])->fetch(PDO::FETCH_ASSOC)['loves'];
            $a4_loved = $database->preparedQuery('SELECT count(id) AS loved FROM loves WHERE type = ? AND pid = ? AND user = ?', ['post', $a4['id'], $yourself['id']])->fetch(PDO::FETCH_ASSOC)['loved'] > 0;
            $a4_replies = count(get_user_post_replies($a4_id));
            $a4 = build_post($a4, $a4_user, $yourself, $a4_replies, $a4_loved, $a4_loves);
        }
    }

    $post_id = $post['id'];
    $user_icon = json_decode($user['data'], true)['profile']['icon'];
    $user_id = $user['identifier'];
    $username = json_decode($user['data'], true)['username'];
    $post_date = time_ago(json_decode($post['data'], true)['miscellaneous']['creation_time']);
    $post_content_aside = $a1;
    $post_content_type = $link ? 'a' : 'div';
    $post_text = parse_post_text($post['text']);
    $post_additional = $a2;
    $post_repost = $a4;
    $loved = $a3;
    $hearts = $loves;
    $reposts = $a4_reposts;
    $comments = $replies;
    $hits = json_decode($post['data'], true)['miscellaneous']['hits'];
    ob_start();
    include DOCUMENT_ROOT . '\static\html\partial\post.php';
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

// Should used a html file include but no.
function build_reply($post, $user, $yourself, $loved, $loves, $link = true, $side = true, $vidcontrols = false) {
    $a1 = '';
    if ($side) {
        if ($post['author'] == $yourself['id']) {
            $a1 = sprintf('
        <a href="%s/posts/%s/edit" class="profile-post-aside-button">
            <img src="%s/img/icons/interface/edit-alt.svg" loading="lazy" alt="Edit post">
        </a>
        ', ROOT_LINK, $post['id'], STATIC_LINK);
        } elseif (isset($_COOKIE['token'])) {
            $a1 = sprintf('
        <a href="%s/posts/%s/report" class="profile-post-aside-button">
            <img src="%s/img/icons/interface/flag.svg" loading="lazy" alt="Report post">
        </a>
        ', ROOT_LINK, $post['id'], STATIC_LINK);
        }
    }
    $a2 = '';
    if (!empty(json_decode($post['data'], true)['images'])) {
        foreach (json_decode($post['data'], true)['images'] as $image) {
            if (file_exists(DOCUMENT_ROOT . '/static/' . ltrim($image, STATIC_LINK . '/'))) {
                $mime = get_file_mime_from_link($image);
                switch (get_file_type($mime)) {
                    case 'image':
                        $a2 .= sprintf('<img class="profile-post-image" src="%s" alt="Post image" loading="lazy">', $image);
                        break;
                    case 'video':
                        ob_start();
                        video($image, explode('.mp4', $image)[0] . 't.jpg', $mime, $vidcontrols);
                        $a2 .= ob_get_clean();
                        break;
                    case 'audio':
                        ob_start();
                        audio($image, $mime, $vidcontrols);
                        $a2 .= ob_get_clean();
                        break;
                }
            }
        }
    }
    $a3 = $loved ? 'profile-post-enabled' : '';

    $post_id = $post['id'];
    $user_icon = json_decode($user['data'], true)['profile']['icon'];
    $user_id = $user['identifier'];
    $username = json_decode($user['data'], true)['username'];
    $post_date = time_ago(json_decode($post['data'], true)['miscellaneous']['creation_time']);
    $post_content_aside = $a1;
    $post_content_type = $link ? 'a' : 'div';
    $post_text = parse_post_text($post['text']);
    $post_additional = $a2;
    $loved = $a3;
    $hearts = $loves;
    ob_start();
    include DOCUMENT_ROOT . '\static\html\partial\comment.php';
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

// Should used a html file include but no.
function build_subreply($post, $user, $yourself, $loved, $loves, $link = true, $side = true, $vidcontrols = false, $reply = 0) {
    $a1 = '';
    if ($side) {
        if ($post['author'] == $yourself['id']) {
            $a1 = sprintf('
        <a href="%s/posts/%s/edit" class="profile-post-aside-button">
            <img src="%s/img/icons/interface/edit-alt.svg" loading="lazy" alt="Edit post">
        </a>
        ', ROOT_LINK, $post['id'], STATIC_LINK);
        } elseif (isset($_COOKIE['token'])) {
            $a1 = sprintf('
        <a href="%s/posts/%s/report" class="profile-post-aside-button">
            <img src="%s/img/icons/interface/flag.svg" loading="lazy" alt="Report post">
        </a>
        ', ROOT_LINK, $post['id'], STATIC_LINK);
        }
    }
    $a2 = '';
    if (!empty(json_decode($post['data'], true)['images'])) {
        foreach (json_decode($post['data'], true)['images'] as $image) {
            if (file_exists(DOCUMENT_ROOT . '/static/' . ltrim($image, STATIC_LINK . '/'))) {
                $mime = get_file_mime_from_link($image);
                switch (get_file_type($mime)) {
                    case 'image':
                        $a2 .= sprintf('<img class="profile-post-image" src="%s" alt="Post image" loading="lazy">', $image);
                        break;
                    case 'video':
                        ob_start();
                        video($image, explode('.mp4', $image)[0] . 't.jpg', $mime, $vidcontrols);
                        $a2 .= ob_get_clean();
                        break;
                    case 'audio':
                        ob_start();
                        audio($image, $mime, $vidcontrols);
                        $a2 .= ob_get_clean();
                        break;
                }
            }
        }
    }
    $a3 = $loved ? 'profile-post-enabled' : '';

    $post_id = $post['id'];
    $user_icon = json_decode($user['data'], true)['profile']['icon'];
    $user_id = $user['identifier'];
    $username = json_decode($user['data'], true)['username'];
    $post_date = time_ago(json_decode($post['data'], true)['miscellaneous']['creation_time']);
    $post_content_aside = $a1;
    $post_content_type = $link ? 'a' : 'div';
    $post_text = parse_post_text($post['text']);
    $post_additional = $a2;
    $loved = $a3;
    $hearts = $loves;
    $reply_id = $reply['id'];
    ob_start();
    include DOCUMENT_ROOT . '\static\html\partial\reply.php';
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

function make_notification(int $user,int $priority, string $data) {
    try {
        $database = prepare_database();
        $check = $database->preparedQuery('SELECT count(id) AS result, JSON_VALUE(data,"$.html") AS html FROM notifications WHERE user = ? AND JSON_VALUE(data,"$.html") = ?',[$user,json_decode($data,true)['html']])->fetch(PDO::FETCH_ASSOC);
        //error_log(json_encode($check));
        if ($check['result'] < 1) {
            $database->preparedQuery('INSERT INTO notifications (user,priority,data) VALUES (?,?,?)', [$user, $priority, $data]);
        }
        return true;
    } catch (\Throwable $e) {
        return false;
    }
}

function generate_paginator_1($pg,$a,$user) {
    if ($pg > $a) { $p = $a
    ?>
     <li class="profile-posts-paginator-page">
        <a href="<?php echo ROOT_LINK ?>/posts/<?php  echo $user['identifier'] ?>/user<?php echo '?page='.$p ?>"><?php echo $p ?></a>
    </li>
    <?php 
    }
} 

// Need to remove these functions but its for compatibility.
function generate_paginator_2($pg,$a,$user) {
    if ($pg < $a) { $p = $a
    ?>
     <li class="profile-posts-paginator-page">
        <a href="<?php echo ROOT_LINK ?>/posts/<?php  echo $user['identifier'] ?>/user<?php echo '?page='.$p ?>"><?php echo $p ?></a>
    </li>
    <?php 
    }
}

function generate_paginator_3($pg,$a,$user, $part = 'users') {
    if ($pg > $a) { $p = $a
    ?>
     <li class="profile-posts-paginator-page">
        <a href="<?php echo ROOT_LINK ?>/internal/<?php echo $part ?>/<?php echo '?page='.$p ?>"><?php echo $p ?></a>
    </li>
    <?php 
    }
} 

function generate_paginator_4($pg,$a,$user, $part = 'users') {
    if ($pg < $a) { $p = $a
    ?>
     <li class="profile-posts-paginator-page">
        <a href="<?php echo ROOT_LINK ?>/internal/<?php echo $part ?>/<?php echo '?page='.$p ?>"><?php echo $p ?></a>
    </li>
    <?php 
    }
}

function generate_paginator_5($pg,$a,$user = '') {
    if ($pg > $a) { $p = $a
    ?>
     <li class="profile-posts-paginator-page">
        <a href="<?php echo ROOT_LINK ?>/explore/hashtags/<?php echo '?page='.$p ?>"><?php echo $p ?></a>
    </li>
    <?php 
    }
} 

function generate_paginator_6($pg,$a,$user = '') {
    if ($pg < $a) { $p = $a
    ?>
     <li class="profile-posts-paginator-page">
        <a href="<?php echo ROOT_LINK ?>/explore/hashtags/<?php echo '?page='.$p ?>"><?php echo $p ?></a>
    </li>
    <?php 
    }
}

function array_iunique( $array ) {
    return array_intersect_key(
        $array,
        array_unique( array_map( "strtolower", $array ) )
    );
}

function log_request() {
    $ip = $_SERVER["REMOTE_ADDR"];
    $country = isset($_SERVER["HTTP_CF_IPCOUNTRY"]) ? $_SERVER["HTTP_CF_IPCOUNTRY"] : 'UNK';
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $token = isset($_COOKIE['token']) ? $_COOKIE['token'] : 'UNK';
    $data = json_encode([
        'ip' => $ip,
        'agent' => $agent,
        'miscellaneous' => [
            'creation_time' => time(),
            'country' => $country,
            'token' => $token,
            'page' => get_page()
        ]
    ]);
    $database = prepare_database();
    $database->preparedQuery('INSERT INTO access (data) VALUES (?)',[$data]);
}

function get_ip_address() {
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        return $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    return $_SERVER['REMOTE_ADDR'];
}

function get_page() {
    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];  
}

// BAREBONE!!!
function upload_banner_image($name, $max_size, $database, $directory, $id, $resize = false) {
    if (isset($_FILES[$name])) {
        $directory_file = $directory . basename($_FILES[$name]["name"]);
        $image_type = strtolower(pathinfo($directory_file, PATHINFO_EXTENSION));
        $_FILES[$name]['name'] = sha1(openssl_random_pseudo_bytes(32)) . '.' . $image_type;
        $directory_file = $directory . basename($_FILES[$name]["name"]);
        if ($_FILES[$name]["size"] < $max_size) {
            if (is_image($_FILES[$name]['tmp_name'])) {
                if (move_uploaded_file($_FILES[$name]['tmp_name'], $directory_file)) {
                    if ($image_type != 'gif') {
                        if (!compress_image($directory_file, $directory_file, 40)) {
                            return 'upload';
                        }
                    }
                    if ($resize) {
                        $meta = getimagesize($directory_file);
                        if ($meta[0] >= 256 && $meta[1] >= 256) {
                            if (!resize_image($directory_file, $directory_file, 256/(($meta[0]+$meta[1])/2))) {
                                return 'upload';
                            }
                        }
                    }
                    $check = json_decode($database->preparedQuery('SELECT data FROM users WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC)['data'], true);
                    $check['profile'][$name] = STATIC_LINK.'/uploads/' . $_FILES[$name]['name'];
                    $check = json_encode($check);
                    $database->preparedQuery('UPDATE users SET data=? WHERE id = ?', [$check, $id]);   
                    return 'success';
                } else {
                    return 'upload';
                }
            } else {
                return 'type';
            }
        } else {
            return 'size';
        }
    } else {
        return 'success';
    }
}

function set_timeout(string $name) {
    if (session_status() === PHP_SESSION_NONE) {
        return false;
    }
    if (!isset($_SESSION['limits'][$name])) {
        $_SESSION['limits']['time'][$name] = time();
    }
    $_SESSION['limits'][$name] = !isset($_SESSION['limits'][$name]) ? 1 : $_SESSION['limits'][$name] + 1;
    return true;
}

// Idk
function reset_timeout(string $name, int $count = 1) {
    if (session_status() === PHP_SESSION_NONE || !isset($_SESSION['limits'][$name])) {
        return false;
    }
    $_SESSION['limits'][$name] = $count;
    return true;
}

function get_timeout(string $name) {
    if (session_status() === PHP_SESSION_NONE || !isset($_SESSION['limits'][$name])) {
        return false;
    }
    return ['time' => $_SESSION['limits']['time'][$name], 'count' => $_SESSION['limits'][$name]];
}

function remove_timeout(string $name) {
    if (session_status() === PHP_SESSION_NONE || !isset($_SESSION['limits'][$name])) {
        return false;
    }
    unset($_SESSION['limits']['time'][$name]);
    unset($_SESSION['limits'][$name]);
    return true;
}

function config_set($config_file, $section, $key, $value) {
    $config_data = parse_ini_file($config_file, true);
    $config_data[$section][$key] = $value;
    $new_content = '';
    foreach ($config_data as $section => $section_content) {
        $section_content = array_map(function($value, $key) {
            return "$key=$value";
        }, array_values($section_content), array_keys($section_content));
        $section_content = implode("\n", $section_content);
        $new_content .= "[$section]\n$section_content\n";
    }
    file_put_contents($config_file, $new_content);
}