<?php
redirect_if_logged(ROOT_LINK);
head('Login', 'en', 'authenticate.css', false, 'Log into '.WEBSITE.' to start sharing and connecting with your friends and people you know worldwide.');
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/authentication/login.js" async defer></script>

<div id="content">
    <div id="page-form">
        <div id="form-container">
            <div class="form-section">
                <img src="<?php echo STATIC_LINK ?>/img/logo.svg" alt="<?php echo WEBSITE ?> logo">
                <h1>Login</h1>
            </div>
            <div class="form-section">
                <form onsubmit="form(this);return false" autocapitalize="off" autocomplete="off" method="POST">
                    <label class="lb-label" id="email-label" for="email">Email</label>
                    <input class="lb-input" id="email" name="email" type="email">
                    <label class="lb-label" id="password-label" for="password">Password</label>
                    <input class="lb-input" id="password" name="password" type="password">
                    <a id="form-inside-first-link" onclick="forgot();return false">Forgot password</a>
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