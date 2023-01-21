<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel='preconnect' href='https://fonts.googleapis.com'>
    <link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
    <style>
        <?php
        include_once(DOCUMENT_ROOT . '\static\css\resets.css');
        include_once(DOCUMENT_ROOT . '\static\css\themes.css');
        ?>
    </style>
    <link rel='stylesheet' href='<?php echo STATIC_LINK ?>/css/styles.css'>
    <link rel='stylesheet' href='<?php echo STATIC_LINK ?>/css/pages/experiments.css'>

    <title>Test</title>
</head>

<script src="<?php echo ROOT_LINK ?>/includes/js/experiments.js" async defer></script>

<body>
    <div id="website">
        <div id="content">

            <div class="darflen-modal-container">
                <div class="darflen-modal-content">
                    <span class="darflen-form-close">×</span>
                    <div class="darflen-modal-content-section">
                        <img class="darflen-modal-logo" src="<?php echo STATIC_LINK ?>/img/logo.svg" alt="">
                        <span class="darflen-modal-title">This a title</span>
                        <p class="darflen-modal-description">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Tempora quas dolorum vero.</p>
                    </div>
                    <div class="darflen-modal-content-section">
                        <!--
                        <form class="darflen-modal-form">
                            <label class="lb-label">Username</label>
                            <input type="text" class="lb-input">
                            <label class="lb-label">Password</label>
                            <input type="text" class="lb-input" placeholder="This is a placeholder">
                            <label class="lb-label lb-label-checkbox">Yes<input type="checkbox" class="lb-checkbox"></label>

                            <button class="lb-button">This is a button</button>
                        </form>
                        -->
                        <button class="lb-button">This is a button</button>
                        <a href="#" class="darflen-modal-link">This is a link</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>

</html>