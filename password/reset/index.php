<?php
redirect_if_logged(ROOT_LINK);
$q = isset($_GET['q']) ? $_GET['q'] : 0;
$database = prepare_database();
$check = $database->preparedQuery('SELECT count(id) AS result, account FROM verifications WHERE type = ? AND token = ?', ['recover_pass', $q])->fetch(PDO::FETCH_ASSOC);
if ($check['result'] < 1) {
    header('Location: <?php echo ROOT_LINK ?>');
}
head('Password Recovery', 'en', 'authenticate.css', false, '', '', WEBSITE, false);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/authentication/forgot-password.js" async defer></script>

<div id="content">
    <div id="page-form">
        <div id="form-container">
            <div class="form-section">
                <img src="<?php echo STATIC_LINK ?>/img/logo.svg" alt="<?php echo WEBSITE ?> logo">
                <h1>Reset password</h1>
            </div>
            <div class="form-section">
                <form onsubmit="form(this);return false" autocapitalize="off" autocomplete="off" method="POST">
                    <label class="lb-label" id="password-label" for="password">Password</label>
                    <input class="lb-input" id="password" name="password" type="password">
                    <label class="lb-label" id="confirm-password-label" for="confirm-password">Confirm New Password</label>
                    <input class="lb-input" id="confirm-password" name="confirm-password" type="password">
                    <button class="lb-button" id="form-submit">Continue</button>
                </form>
            </div>
            <div class="form-section">
                <a href="<?php echo ROOT_LINK ?>/join">Don't have an account?</a>
            </div>
        </div>
    </div>

    <ul class="circles">
        <?php
        for ($index = 0; $index <= 14; $index++) {
            echo '<li></li>';
        }
        ?>
    </ul>
</div>