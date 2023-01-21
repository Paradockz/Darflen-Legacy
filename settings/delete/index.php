<?php
redirect_if_not_logged(ROOT_LINK);
head('Delete account', 'en', 'authenticate.css',false, '', '', 'Darflen', false);
// In case someone's just copying the link.
$yourself = json_decode(get_user_info_from_token($_COOKIE['token'])['data'], true);
if ($yourself['miscellaneous']['email_verified'] && !$yourself['miscellaneous']['upcoming_email']) {
    header('Location: <?php echo ROOT_LINK ?>/settings/');
}
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/authentication/delete.js" async defer></script>

<div id="content">
    <div id="page-form">
        <div id="form-container">
            <div class="form-section">
                <img src="<?php echo STATIC_LINK ?>/img/logo.svg" alt="Darflen logo">
                <h1>Account deletion</h1>
                <p id="form-description">Please enter your password to continue the process.</p>
            </div>
            <div class="form-section">
                <form onsubmit="form(this);return false" autocapitalize="off" autocomplete="off" method="POST">
                    <label class="lb-label" id="password-label" for="password">Password</label>
                    <input class="lb-input" id="password" name="password" type="password">
                    <button class="lb-button" id="form-submit">Continue</button>
                </form>
            </div>
            <div class="form-section">
                <a id="form-first-link-without-margin" onclick="window.history.back();">Go back</a>
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