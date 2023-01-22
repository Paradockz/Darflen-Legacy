<?php
redirect_if_not_logged(ROOT_LINK);
head('Change email', 'en', 'authenticate.css', false, '', '', WEBSITE, false);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/authentication/change-email.js" async defer></script>

<div id="content">
    <div id="page-form">
        <div id="form-container">
            <div class="form-section">
                <img src="<?php echo STATIC_LINK ?>/img/logo.svg" alt="<?php echo WEBSITE ?> logo">
                <h1>Enter an email address</h1>
                <p id="form-description">Enter an email address and your existing password.</p>
            </div>
            <div class="form-section">
                <form onsubmit="form(this);return false" autocapitalize="off" autocomplete="off" method="POST">
                    <label class="lb-label" id="new-email-label" for="new-email">New Email</label>
                    <input class="lb-input" id="new-email" name="new-email" type="email">
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