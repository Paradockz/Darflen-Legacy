<?php $user = get_user_info_from_id($id) ?>
<a href="<?php echo ROOT_LINK ?>/users/<?php echo $user['identifier'] ?>" class="profile-card" tabindex="-1">
    <img class="profile-card-banner-image" src="<?php echo json_decode($user['data'], true)['profile']['banner'] ?>" alt="User banner" loading="lazy">
    <div class="profile-card-container">
        <div class="profile-card-section">
            <img class="profile-card-icon" src="<?php echo json_decode($user['data'], true)['profile']['icon'] ?>" alt="User icon" loading="lazy">
            <span class="profile-card-user"><?php echo json_decode($user['data'], true)['username'] ?></span>
        </div>
        <div class="profile-card-section">
            <ul class="profile-card-badges">
                <?php
                    if(json_decode($user['data'], true)['miscellaneous']['administrator'] == true) {
                ?>
                <li class="profile-card-badge">
                    <img src="<?php echo STATIC_LINK ?>/img/icons/interface/settings.svg" alt="Administrator badge" title="Administrator badge">
                </li>
                <?php
                }
                if(json_decode($user['data'], true)['miscellaneous']['user_verified'] == true) {
                ?>
                <li class="profile-card-badge">
                    <img src="<?php echo STATIC_LINK ?>/img/icons/interface/user-check.svg" alt="Verified user badge" title="Verified user badge">
                </li>
                <?php
                }
                ?>
            </ul>
        </div>
    </div>
</a>