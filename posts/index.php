<?php
if (empty($_GET['u']) || !$post = get_post_info_from_id($_GET['u'])) {
    http_response_code(404);
    include_once(DOCUMENT_ROOT . '/errors/404.php');
    exit;
}
$database = prepare_database();
$yourself = [];
if (isset($_COOKIE['token']) && check_token_validity($_COOKIE['token'])) {
    $yourself = get_user_info_from_token($_COOKIE['token']);
}
$yourself['id'] = isset($yourself['id']) ? $yourself['id'] : 0;
$cover = json_decode($post['data'], true)['miscellaneous']['coverage'];
switch ($cover) {
    case 'followers':
        $following = $database->preparedQuery('SELECT count(id) AS following FROM follows WHERE follower = ? AND following = ?', [$yourself['id'], $post['author']])->fetch(PDO::FETCH_ASSOC)['following'] > 0 || $yourself['id'] == $post['author'];
        if (!$following) {
            http_response_code(404);
            include_once(DOCUMENT_ROOT . '/errors/404.php');
            exit;
        }
        break;
    case 'private':
        if ($yourself['id'] != $post['author']) {
            http_response_code(404);
            include_once(DOCUMENT_ROOT . '/errors/404.php');
            exit;
        }
        break;
    default:
        if (!in_array($cover, ['public', 'unlisted'])) {
            http_response_code(404);
            include_once(DOCUMENT_ROOT . '/errors/404.php');
            exit;
        }
        break;
}
$user = get_user_info_from_id($post['author']);
$id = $_GET['u'];
set_timeout('posts_' . $id);
$timeout = get_timeout('posts_' . $id);
if (time() > $timeout['time'] + 60) {
    remove_timeout('posts_' . $id);
    $timeout['count'] = 0;
}
if ($yourself['id'] > 0 && $yourself['id'] != $post['author'] && $timeout['count'] <= 1) {
    $database->preparedQuery('UPDATE posts SET data = JSON_SET(data,"$.miscellaneous.hits",JSON_VALUE(data, "$.miscellaneous.hits")+1) WHERE id = ?', [$id]);
}
$loves = $database->preparedQuery('SELECT count(id) AS loves FROM loves WHERE type = ? AND pid = ?', ['post', $post['id']])->fetch(PDO::FETCH_ASSOC)['loves'];
$loved = $database->preparedQuery('SELECT count(id) AS loved FROM loves WHERE type = ? AND pid = ? AND user = ?', ['post', $post['id'], $yourself['id']])->fetch(PDO::FETCH_ASSOC)['loved'] > 0;
$replies = get_user_post_replies($id);
$comments = $database->preparedQuery('SELECT count(id) as replies FROM replies WHERE post = ?', [$post['id']])->fetch(PDO::FETCH_ASSOC)['replies'];
$mime = 'miscellaneous';
if (isset(json_decode($post['data'], true)['images'][0])) {
    $mime = get_file_type(get_file_mime_from_link(json_decode($post['data'], true)['images'][0]));
}
if (isset(json_decode($post['data'], true)['images'][0])) {
    $type = $mime == 'video' ? 'player' : 'summary_large_image';
} else {
    $type = 'summary';
}
$ogtype = isset(json_decode($post['data'], true)['images'][0]) && $mime == 'video' ? 'video' : 'website';
head(json_decode($user['data'], true)['username'] . '\'s Post', 'en', 'profile.css', true, strip_tags(preg_replace("/<br\W*?\/?>/", " ", parse_post_text($post['text']))), isset(json_decode($post['data'], true)['images'][0]) ? json_decode($post['data'], true)['images'][0] : '', json_decode($user['data'], true)['username'], true, $type, $ogtype);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/posts.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/video.js" async defer></script>

<div id="content">
    <div id="profile-posts">
        <div class="post-section">
            <?php echo build_post($post, $user, $yourself, count($replies), $loved, $loves, false, true, true); ?>
        </div>
        <div class="post-section">
            <?php
            if (isset($_COOKIE['token']) && check_token_validity($_COOKIE['token'])) {
            ?>
                <div id="post-reply">
                    <div id="page-form">
                        <div id="form-container">
                            <div class="form-section">
                                <ul id="form-markdowns" onmousedown="return false" onselectstart="return false">
                                    <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/bold.svg" alt="Bold" tabindex="0"></li>
                                    <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/italic.svg" alt="Italic" tabindex="0"></li>
                                    <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/underline.svg" alt="Underline" tabindex="0"></li>
                                    <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/strikethrough.svg" alt="Strikethrough" tabindex="0"></li>
                                    <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/comment.svg" alt="Code" tabindex="0"></li>
                                    <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/eye-off.svg" alt="Spoiler" tabindex="0"></li>
                                </ul>
                                <p id="form-text-length" title="Characters remaining">1024</p>
                            </div>
                            <div class="form-section">
                                <form onsubmit="reply(this,'<?php echo $post['id'] ?>');return false" autocapitalize="off" autocomplete="off" method="POST">
                                    <div class="form-inside-section">
                                        <textarea name="textarea" id="textarea" class="lb-textarea" cols="30" rows="5" placeholder="What will you share today?"></textarea>
                                    </div>
                                    <div class="form-inside-section">
                                        <label class="lb-input" id="images-container">
                                            <img src="<?php echo STATIC_LINK ?>/img/icons/interface/image.svg" alt="Upload image">
                                            <input type="file" name="images[]" id="images" class="lb-input" multiple>
                                            <span class="images-upload-text" title="Upload images">Upload</span>
                                        </label>
                                    </div>
                                    <button class="lb-button" id="form-submit">Post</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
            <?php
            //var_dump($posts);
            if ($replies) {
                foreach ($replies as $post) {
                    $user_id = isset($_COOKIE['token']) && check_token_validity($_COOKIE['token']) ? get_user_info_from_token($_COOKIE['token'])['id'] : 0;
                    $author = get_user_info_from_id($post['author']);
                    $loves = $database->preparedQuery('SELECT count(id) AS loves FROM loves WHERE type = ? AND pid = ?', ['reply', $post['id']])->fetch(PDO::FETCH_ASSOC)['loves'];
                    $loved_reply = $database->preparedQuery('SELECT count(id) AS loved FROM loves WHERE type = ? AND pid = ? AND user = ?', ['reply', $post['id'], $yourself['id']])->fetch(PDO::FETCH_ASSOC)['loved'] > 0;
                    echo build_reply($post, $author, $yourself, $loved_reply, $loves, false, true, true);
                    $subreplies = get_user_reply_subreplies($post['id']);
                    foreach (array_reverse($subreplies) as $subreply) {
                        $sub_author = get_user_info_from_id($subreply['author']);
                        $sub_loves = $database->preparedQuery('SELECT count(id) AS loves FROM loves WHERE type = ? AND pid = ?', ['subreply', $subreply['id']])->fetch(PDO::FETCH_ASSOC)['loves'];
                        $loved_subreply = $database->preparedQuery('SELECT count(id) AS loved FROM loves WHERE type = ? AND pid = ? AND user = ?', ['subreply', $subreply['id'], $yourself['id']])->fetch(PDO::FETCH_ASSOC)['loved'] > 0;
                        echo build_subreply($subreply, $sub_author, $yourself, $loved_subreply, $sub_loves, false, true, true, $subreply);
                    }
                }
            } elseif (isset($_COOKIE['token'])) {
                ?>
                <p id="empty-profile">There are no reply on <?php echo json_decode($user['data'], true)['username'] ?> post. Will you be the first to reply?</p>
            <?php
            }
            ?>
        </div>
    </div>
</div>
</div>

<?php footer() ?>