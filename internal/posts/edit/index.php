<?php
redirect_if_not_logged('https://darflen.com');
$mode = 'post';
if (empty($_GET['u']) || !$post = get_post_info_from_id($_GET['u'])) {
    if (!$post = get_reply_info_from_id($_GET['u'])) {
        http_response_code(404);
        include_once(DOCUMENT_ROOT . '/errors/404.php');
        exit;
    } else {
        $mode = 'reply';
    }
} else {
    $user = get_user_info_from_token($_COOKIE['token']);
    if ($post['author'] != $user['id']) {
        header('Location: https://darflen.com/posts/' . $post['id']);
    }
}
head('Edit '.ucfirst($mode), 'en', 'profile.css', true);
?>

<script src="https://darflen.com/includes/js/posts.js" async defer></script>

<div id="content">
    <h1><?php echo 'Edit '.ucfirst($mode) ?></h1>
    <div id="page-form">
        <div id="form-container">
            <div class="form-section">
                <ul id="form-markdowns" onmousedown="return false" onselectstart="return false">
                    <li class="form-markdown"><img src="https://static.darflen.com/img/icons/interface/bold.svg" alt="Bold" tabindex="0"></li>
                    <li class="form-markdown"><img src="https://static.darflen.com/img/icons/interface/italic.svg" alt="Italic" tabindex="0"></li>
                    <li class="form-markdown"><img src="https://static.darflen.com/img/icons/interface/underline.svg" alt="Underline" tabindex="0"></li>
                    <li class="form-markdown"><img src="https://static.darflen.com/img/icons/interface/strikethrough.svg" alt="Strikethrough" tabindex="0"></li>
                    <li class="form-markdown"><img src="https://static.darflen.com/img/icons/interface/comment.svg" alt="Code" tabindex="0"></li>
                    <li class="form-markdown"><img src="https://static.darflen.com/img/icons/interface/eye-off.svg" alt="Spoiler" tabindex="0"></li>
                </ul>
                <p id="form-text-length" title="Characters remaining">1024</p>
            </div>
            <div class="form-section">
                <form onsubmit="edit(this,'<?php echo $post['id'] ?>');return false" autocapitalize="off" autocomplete="off" method="POST">
                    <div class="form-inside-section">
                        <textarea name="textarea" id="textarea" class="lb-textarea" cols="30" rows="10" placeholder="What will you share today?"><?php echo $post['text'] ?></textarea>
                    </div>
                    <div class="form-inside-section">
                        <?php
                        if ($mode == 'post') {
                        ?>
                        <select name="coverage" id="coverage" class="lb-input">
                            <option value="public" <?php echo json_decode($post['data'], true)['miscellaneous']['coverage'] == 'public' ? 'selected' : '' ?>>Public</option>
                            <option value="unlisted" <?php echo json_decode($post['data'], true)['miscellaneous']['coverage'] == 'unlisted' ? 'selected' : '' ?>>Unlisted</option>
                            <option value="followers" <?php echo json_decode($post['data'], true)['miscellaneous']['coverage'] == 'followers' ? 'selected' : '' ?>>Followers only</option>
                            <option value="private" <?php echo json_decode($post['data'], true)['miscellaneous']['coverage'] == 'private' ? 'selected' : '' ?>>Private</option>
                        </select>
                        <?php
                        }
                        ?>
                        <label class="lb-input" id="images-container">
                            <img src="https://static.darflen.com/img/icons/interface/image.svg" alt="Upload image">
                            <input type="file" name="images[]" id="images" class="lb-input" multiple>
                            <span class="images-upload-text" title="Upload images">Upload</span>
                        </label>
                    </div>
                    <button class="lb-button" id="form-submit">Edit</button>
                </form>
            </div>
            <div class="form-section">
                <h2>Miscellaneous</h2>
                <button id="post-delete" class="lb-button" onclick="disappear('<?php echo $post['id'] ?>');return false">Delete post</button>
            </div>
        </div>
    </div>
</div>

<?php footer() ?>