<?php
redirect_if_logged(ROOT_LINK);
head('Join', 'en', 'authenticate.css', false, 'Log into '.WEBSITE.' to start sharing and connecting with your friends and people you know worldwide.');
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/authentication/join.js" async defer></script>

<div id="content">
    <div id="page-form">
        <div id="form-container">
            <div class="form-section">
                <img src="<?php echo STATIC_LINK ?>/img/logo.svg" alt="<?php echo WEBSITE ?> logo">
                <h1>Create an account</h1>
            </div>
            <div class="form-section">
                <form onsubmit="form(this);return false" autocapitalize="off" autocomplete="off" method="POST">
                    <label class="lb-label" id="email-label" for="email">Email</label>
                    <input class="lb-input" id="email" name="email" type="email">
                    <label class="lb-label" id="username-label" for="username">Username</label>
                    <input class="lb-input" id="username" name="username" type="text">
                    <label class="lb-label" id="password-label" for="password">Password</label>
                    <input class="lb-input" id="password" name="password" type="password">
                    <label class="lb-label" id="birthdate-label" for="birthdate">Birthdate</label>
                    <?php include_once DOCUMENT_ROOT . '\static\html\partial\birthform.php'; ?>
                    <button class="lb-button" id="form-submit">Continue</button>
                </form>
            </div>
            <div class="form-section">
                <a id="form-first-link" href="<?php echo ROOT_LINK ?>/login/">Already have an account?</a>
                <p>By registering, you agree to <?php echo WEBSITE ?> <a href="<?php echo ROOT_LINK ?>/terms">Terms of Service</a> and <a href="<?php echo ROOT_LINK ?>/privacy">Privacy Policy</a></p>
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