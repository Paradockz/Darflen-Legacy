<?php
$mode = isset($_GET['r']) ? 'Reshare' : 'Post';
redirect_if_not_logged(ROOT_LINK);
head('Create '.$mode, 'en', 'profile.css', true);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/posts.js" async defer></script>

<div id="content">
    <h1>Create <?php echo $mode; ?></h1>
    <div id="page-form">
        <div id="form-container">
            <div class="form-section">
                <ul id="form-markdowns" onmousedown="return false" onselectstart="return false">
                    <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/bold.svg" alt="Bold" tabindex="0"></li>
                    <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/italic.svg" alt="Italic" tabindex="0"></li>
                    <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/underline.svg" alt="Underline" tabindex="0"></li>
                    <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/strikethrough.svg" alt="Strikethrough" tabindex="0"></li>
                    <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/comment.svg" alt="Code" tabindex="0"></li>
                    <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/eye-off.svg" alt="Spoiler" tabindex="0"></li>
                </ul>
                <p id="form-text-length" title="Characters remaining">1024</p>
            </div>
            <div class="form-section">
                <form onsubmit="form(this);return false" autocapitalize="off" autocomplete="off" method="POST">
                    <div class="form-inside-section">
                        <textarea name="textarea" id="textarea" class="lb-textarea" cols="30" rows="10" placeholder="What will you share today?"></textarea>
                    </div>
                    <div class="form-inside-section">
                        <select name="coverage" id="coverage" class="lb-input">
                            <option value="public" selected>Public</option>
                            <option value="unlisted">Unlisted</option>
                            <option value="followers">Followers only</option>
                            <option value="private">Private</option>
                        </select>
                        <label class="lb-input" id="images-container">
                            <img src="<?php echo STATIC_LINK ?>/img/icons/interface/image.svg" alt="Upload image">
                            <input type="file" name="images[]" id="images" class="lb-input" multiple>
                            <span class="images-upload-text" title="Upload images">Upload</span>
                        </label>
                    </div>
                    <button class="lb-button" id="form-submit">Post</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php footer() ?>