<?php
redirect_if_not_logged(ROOT_LINK);
$mode = 'post';
if (empty($_GET['u']) || !get_post_info_from_id($_GET['u']) && !get_reply_info_from_id($_GET['u']) && !get_subreply_info_from_id($_GET['u'])) {
    http_response_code(404);
    include_once(DOCUMENT_ROOT . '/errors/404.php');
    exit;
} else {
    if (!$post = get_post_info_from_id($_GET['u'])) {
        if (!get_subreply_info_from_id($_GET['u'])) {
            $post = get_reply_info_from_id($_GET['u']);
            $mode = 'reply';
        } else {
            $post = get_subreply_info_from_id($_GET['u']);
            $mode = 'subreply';
        }
    }
    $user = get_user_info_from_token($_COOKIE['token']);
    if ($post['author'] == $user['id']) {
        header('Location: ' . ROOT_LINK . '/posts/' . $post['id']);
    }
}
head('Report ' . json_decode(get_user_info_from_id($post['author'])['data'], true)['username'] . '\'s ' . ucfirst(['post' => 'post', 'reply' => 'comment', 'subreply' => 'reply'][$mode]), 'en', 'profile.css', true);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/posts.js" async defer></script>

<div id="content">
    <h1>Report <?php echo ucfirst(['post' => 'post', 'reply' => 'comment', 'subreply' => 'reply'][$mode]) ?></h1>
    <div id="page-form">
        <div id="form-container">
            <div class="form-section">
                <form onsubmit="report_post(this,'<?php echo $_GET['u'] ?>');return false" autocapitalize="off" autocomplete="off" method="POST" id="page-form-inputs">
                    <div class="form-inside-section">
                        <select name="reason" id="reason" class="lb-input">
                            <option value="">Please select a category</option>
                            <option value="theft">Account Theft</option>
                            <option value="info">Asking for or Giving Private Information</option>
                            <option value="exploit">Exploiting / Scamming</option>
                            <option value="language">Inappropriate Language</option>
                            <option value="content">Inappropriate Content</option>
                            <option value="threats">Real Life Threats & Suicide Threats</option>
                            <option value="other">Other rule violation</option>
                        </select>
                    </div>
                    <div class="form-inside-section">
                        <textarea name="textarea" id="textarea" class="lb-textarea" cols="30" rows="10" placeholder="Give more informations about the reason of the report."></textarea>
                    </div>
                    <button class="lb-button" id="form-submit">Report</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php footer() ?>