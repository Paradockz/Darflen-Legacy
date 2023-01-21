<?php
redirect_if_not_logged(ROOT_LINK);
$token = $_COOKIE['token'];
$database = prepare_database();
$user = get_user_info_from_token($token);
if (!json_decode($user['data'], true)['miscellaneous']['administrator']) {
    http_response_code(404);
    include_once(DOCUMENT_ROOT . '/errors/404.php');
    exit;
}
$things = ['users' => 'users', 'loves' => 'heart', 'posts' => 'message', 'replies' => 'message-alt', 'reports' => 'warning'];
foreach ($things as $key => $value) {
    $data[$key] = $database->rawQuery('SELECT COUNT(id) AS count FROM ' . $key)->fetch(PDO::FETCH_ASSOC)['count'];
    $images[$key] = sprintf('%s.svg', $value);
}
head('Website Reports', 'en', 'settings.css', true, '', '', 'Darflen', false);
$user = get_user_info_from_identifier($_GET['u']);
$data = json_decode($user['data'], true);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/internal.js" async defer></script>

<div id="content">
    <div id="settings">
        <h1><?php echo sprintf('%s\'s Settings',$data['username']) ?></h1>
        <div id="settings-user">
            <img id="settings-user-image" src="<?php echo $data['profile']['banner'] ?>" alt="User banner">
            <div class="settings-user-section">
                <div class="settings-user-content">
                    <div class="settings-user-bottom-section">
                        <img id="settings-user-icon" src="<?php echo $data['profile']['icon'] ?>" alt="User icon">
                        <span id="settings-user-user"><?php echo $data['username'] ?></span>
                        <?php
                        if ($data['miscellaneous']['administrator'] || $data['miscellaneous']['user_verified']) {
                        ?>
                            <ul id="settings-user-badges">
                                <?php
                                if ($data['miscellaneous']['administrator'] == true) {
                                ?>
                                    <li class="settings-user-badge">
                                        <img src="<?php echo STATIC_LINK ?>/img/icons/interface/settings.svg" alt="Administrator badge" title="Administrator badge">
                                    </li>
                                <?php
                                }
                                if ($data['miscellaneous']['user_verified'] == true) {
                                ?>
                                    <li class="settings-user-badge">
                                        <img src="<?php echo STATIC_LINK ?>/img/icons/interface/user-check.svg" alt="Verified user badge" title="Verified user badge">
                                    </li>
                                <?php
                                }
                                ?>
                            </ul>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="settings-user-section">
                <div class="settings-user-content">
                    <ul class="settings-user-details">
                        <li class="settings-user-detail">
                            <div class="settings-user-detail-info">
                                <span class="settings-user-detail-title <?php if (!$data['miscellaneous']['email_verified']) : echo 'settings-user-warning';
                                                                        endif; ?>">
                                    Email
                                    <?php
                                    if (!$data['miscellaneous']['email_verified']) {
                                        echo ' - Not verified';
                                    }
                                    ?>
                                </span>
                                <span class="settings-user-detail-content"><?php echo $user['email'] ?></span>
                            </div>
                        </li>
                        <li class="settings-user-detail">
                            <div class="settings-user-detail-info">
                                <span class="settings-user-detail-title">Username</span>
                                <span class="settings-user-detail-content"><?php echo $data['username'] ?></span>
                            </div>
                        </li>
                        <li class="settings-user-detail">
                            <div class="settings-user-detail-info">
                                <span class="settings-user-detail-title">Password</span>
                                <span class="settings-user-detail-content">********</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div id="settings-profile">
            <div id="settings-form">
                <form onsubmit="edit_profile(this,'<?php echo $user['identifier'] ?>');return false" autocapitalize="off" autocomplete="off" method="POST">
                    <label class="lb-label" for="email" id="email-label">Email</label>
                    <input type="email" class="lb-input" id="email" name="email" value="<?php echo $user['email'] ?>">
                    <label class="lb-label" for="username" id="username-label">Username</label>
                    <input type="text" class="lb-input" id="username" name="username" value="<?php echo $data['username'] ?>">
                    <label class="lb-label" for="password" id="password-label">Password</label>
                    <input type="password" class="lb-input" id="password" name="password">
                    <label for="banner">
                        <span class="lb-label" id="banner-label">Banner</span>
                        <div class="lb-input settings-file-selector" tabindex="0">
                            <input type="file" name="banner" id="banner" accept="image/*">
                            <span>Select a file</span>
                        </div>
                    </label>
                    <label for="icon">
                        <span class="lb-label" id="icon-label">Icon</span>
                        <div class="lb-input settings-file-selector" tabindex="0">
                            <input class="lb-input" type="file" name="icon" id="icon" accept="image/*">
                            <span>Select a file</span>
                        </div>
                    </label>
                    <label class="lb-label" for="description" id="description-label">Description</label>
                    <ul id="form-markdowns" onmousedown="return false" onselectstart="return false">
                        <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/bold.svg" alt="Bold" tabindex="0"></li>
                        <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/italic.svg" alt="Italic" tabindex="0"></li>
                        <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/underline.svg" alt="Underline" tabindex="0"></li>
                        <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/strikethrough.svg" alt="Strikethrough" tabindex="0"></li>
                        <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/comment.svg" alt="Code" tabindex="0"></li>
                        <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/eye-off.svg" alt="Spoiler" tabindex="0"></li>
                    </ul>
                    <textarea class="lb-textarea" id="description" name="description" cols="30" rows="6"><?php echo $data['profile']['description'] ?></textarea>
                    <div class="form-checkbox">
                        <label class="lb-label" id="administrator-label">Administrator</label>
                        <input type="checkbox" class="lb-checkbox" id="administrator" name="administrator" <?php echo $data['miscellaneous']['administrator'] != false ? 'checked' : '' ?>>
                    </div>
                    <div class="form-checkbox">
                        <label class="lb-label" id="user_verified-label">Account Verification</label>
                        <input type="checkbox" class="lb-checkbox" id="user_verified" name="user_verified" <?php echo $data['miscellaneous']['user_verified'] != false ? 'checked' : '' ?>>
                    </div>
                    <div class="form-checkbox">
                        <label class="lb-label" id="email_verified-label">Email Verification</label>
                        <input type="checkbox" class="lb-checkbox" id="email_verified" name="email_verified" <?php echo $data['miscellaneous']['email_verified'] != false ? 'checked' : '' ?>>
                    </div>
                    <button class="lb-button" id="form-submit">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php footer() ?>