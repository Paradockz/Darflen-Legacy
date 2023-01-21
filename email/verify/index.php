<?php
redirect_if_not_logged(ROOT_LINK);
head('Verify email', 'en', 'authenticate.css',false, '', '', 'Darflen', false);
// In case someone's just copying the link.
$yourself = json_decode(get_user_info_from_token($_COOKIE['token'])['data'], true);
if ($yourself['miscellaneous']['email_verified'] && !$yourself['miscellaneous']['upcoming_email']) {
    header('Location: '.ROOT_LINK.'/settings/');
}
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/authentication/verify-email.js" async defer></script>

<div id="content">
    <div id="page-form">
        <div id="form-container">
            <div class="form-section">
                <img src="https://static.darflen.com/img/logo.svg" alt="Darflen logo">
                <h1>Enter your code</h1>
                <p id="form-description">A verification code has been sent to your mailbox</p>
            </div>
            <div class="form-section">
                <form onsubmit="form(this);return false" autocapitalize="off" autocomplete="off" method="POST">
                    <label class="lb-label" id="tCode-label" for="tCode">Code</label>
                    <input class="lb-input" id="tCode" name="tCode" type="text">
                    <button class="lb-button" id="form-submit">Continue</button>
                </form>
            </div>
            <div class="form-section">
                <a id="form-first-link" onclick=" resend();return false">Resend confirmation email</a>
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