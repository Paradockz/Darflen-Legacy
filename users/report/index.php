<?php
redirect_if_not_logged(ROOT_LINK);
if (empty($_GET['u']) || !$guy = get_user_info_from_identifier($_GET['u'])) {
    http_response_code(404);
    include_once(DOCUMENT_ROOT . '/errors/404.php');
    exit;
} else {
    $user = get_user_info_from_token($_COOKIE['token']);
    if ($guy['identifier'] == $user['identifier']) {
        header('Location: <?php echo ROOT_LINK ?>/users/' . $user['identifier']);
    }
}
head('Report ' . json_decode($guy['data'], true)['username'] . '\'s Profile', 'en', 'profile.css', true);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/profile.js" async defer></script>

<div id="content">
    <h1>Report Profile</h1>
    <div id="page-form">
        <div id="form-container">
            <div class="form-section">
                <form onsubmit="report_profile(this,'<?php echo $_GET['u'] ?>');return false" autocapitalize="off" autocomplete="off" method="POST" id="page-form-inputs">
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