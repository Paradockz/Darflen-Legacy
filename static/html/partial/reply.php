<div class="profile-post profile-post-smaller" id="<?php echo $post_id ?>">
    <div class="profile-post-section">
        <div class="profile-post-top-section">
            <img class="profile-post-icon" src="<?php echo $user_icon ?>" alt="User icon" loading="lazy">
            <a href="<?php echo ROOT_LINK ?>/users/<?php echo $user_id ?>" class="profile-post-user">
                <span class="profile-post-username"><?php echo $username ?></span>
                <span class="profile-post-date"><?php echo $post_date ?></span>
            </a>
        </div>
        <div class="profile-post-top-section">
            <?php echo $post_content_aside ?>
        </div>
    </div>

    <<?php echo $post_content_type ?> href="<?php echo ROOT_LINK ?>/posts/<?php echo $post_id ?>" class="profile-post-section profile-post-content-text">
        <?php echo $post_text ?>
        <?php echo $post_additional ?>
    </<?php echo $post_content_type ?>>

    <div class="profile-post-section">
        <ul class="profile-post-actions">
            <li class="profile-post-action">
                <button class="<?php echo $loved ?>" onclick="heart_subreply(this,'<?php echo $post_id ?>')">
                    <img class="profile-post-action-icon" src="<?php echo STATIC_LINK ?>/img/icons/interface/heart.svg" alt="Heart icon" loading="lazy">
                    <span class="profile-post-action-value"><?php echo $hearts ?></span>
                </button>
            </li>
            <li class="profile-post-action">
                <button onclick="load_subreply(this,'<?php echo $reply_id ?>')">
                    <img class="profile-post-action-icon" src="<?php echo STATIC_LINK ?>/img/icons/interface/message-alt.svg" alt="Heart icon" loading="lazy">
                    <span class="profile-post-action-value">Reply</span>
                </button>
            </li>
        </ul>
    </div>
</div>