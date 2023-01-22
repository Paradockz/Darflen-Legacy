<?php
redirect_if_not_logged(ROOT_LINK);
$token = $_COOKIE['token'];
$database = prepare_database();
$user = get_user_info_from_token($token);
if (!json_decode($user['data'], true)['miscellaneous']['administrator']) {
    http_response_code(404);
    include_once(DOCUMENT_ROOT . '/errors/404.php');
    exit;
}
$things = ['users' => 'users', 'loves' => 'heart', 'posts' => 'message', 'replies' => 'message-alt', 'reports' => 'warning'];
foreach ($things as $key => $value) {
    $data[$key] = $database->rawQuery('SELECT COUNT(id) AS count FROM ' . $key)->fetch(PDO::FETCH_ASSOC)['count'];
    $images[$key] = sprintf('%s.svg', $value);
}
head('Ban', 'en', 'internal.css', true, '', '', WEBSITE, false);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/internal.js" async defer></script>

<div id="content">
    <div id="internal">
        <h1 id="internal-title">Ban</h1>
        <div id="page-form">
            <div id="form-container">
                <div class="form-section">
                    <form onsubmit="user_ban(this,'<?php echo $_GET['u'] ?>');return false" autocapitalize="off" autocomplete="off" method="POST" id="page-form-inputs">
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
                            <select name="time" id="time" class="lb-input">
                                <option value="">Please select a time</option>
                                <option value="1d">1 Day</option>
                                <option value="2d">2 Days</option>
                                <option value="5d">5 Days</option>
                                <option value="1w">1 Week</option>
                                <option value="2w">2 Weeks</option>
                                <option value="1m">1 Month</option>
                                <option value="p">Permanent</option>
                            </select>
                        </div>
                        <div class="form-inside-section">
                            <textarea name="textarea" id="textarea" class="lb-textarea" cols="30" rows="10" placeholder="Give more informations about the reason of the ban."></textarea>
                        </div>
                        <button class="lb-button" id="form-submit">Ban</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php footer() ?>