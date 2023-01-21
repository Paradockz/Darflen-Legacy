<div id="subreply-form">
    <div id="subreply-container">
        <div class="form-section">
            <ul id="subreply-markdowns" onmousedown="return false" onselectstart="return false">
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
            <form onsubmit="subreply(this,'<?php echo $reply ?>','<?php echo $sub ?>');return false" autocapitalize="off" autocomplete="off" method="POST">
                <div class="form-inside-section">
                    <textarea name="textarea" id="textarea" class="lb-textarea" cols="30" rows="5" placeholder="What will be your reply?"><?php echo $textarea ?></textarea>
                </div>
                <div class="form-inside-section">
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