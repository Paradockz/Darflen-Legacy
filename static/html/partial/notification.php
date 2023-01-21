<div class="user-notification">
    <div class="user-notification-section">
        <img src="<?php echo STATIC_LINK ?>/img/icons/interface/<?php echo $icon ?>" alt="Icon" class="user-notification-icon">
        <p class="user-notification-text"><span><?php echo $html ?></span></p>
    </div>
    <div class="user-notification-section">
        <p><?php echo time_ago($time) ?></p>
    </div>
</div>