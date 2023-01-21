<?php
redirect_if_not_logged(ROOT_LINK);
head('Settings', 'en', 'settings.css', true, '', '', 'Darflen', false);
$user = get_user_info_from_token($_COOKIE['token']);
$data = json_decode($user['data'], true);
$banned = !check_user_ban($user['identifier']);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/settings.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/authentication/logout.js" async defer></script>

<div id="content">
    <div id="settings">
        <h1>My Settings</h1>
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
                            <?php if ($banned) : ?>
                                <a href="<?php echo ROOT_LINK ?>/email/change/" class="settings-user-detail-button">
                                    <button class="lb-button">Edit</button>
                                </a>
                            <?php endif; ?>
                        </li>
                        <li class="settings-user-detail">
                            <div class="settings-user-detail-info">
                                <span class="settings-user-detail-title">Username</span>
                                <span class="settings-user-detail-content"><?php echo $data['username'] ?></span>
                            </div>
                            <?php if ($banned) : ?>
                                <a href="<?php echo ROOT_LINK ?>/username/change/" class="settings-user-detail-button">
                                    <button class="lb-button">Edit</button>
                                </a>
                            <?php endif; ?>
                        </li>
                        <li class="settings-user-detail">
                            <div class="settings-user-detail-info">
                                <span class="settings-user-detail-title">Password</span>
                                <span class="settings-user-detail-content">********</span>
                            </div>
                            <?php if ($banned) : ?>
                                <a href="<?php echo ROOT_LINK ?>/password/change/" class="settings-user-detail-button">
                                    <button class="lb-button">Edit</button>
                                </a>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
                <div class="settings-user-content">
                    <ul class="settings-user-details">
                        <?php
                        if (!$data['miscellaneous']['email_verified'] || isset($data['miscellaneous']['upcoming_email'])) {
                        ?>
                            <li class="settings-user-detail">
                                <div class="settings-user-detail-info-single">
                                    <span class="settings-user-detail-title">
                                        <?php
                                        if (isset($data['miscellaneous']['upcoming_email'])) {
                                            echo 'Verify your new email address!';
                                        } else {
                                            echo 'Email is not verified!';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <a onclick="verify(this);return false" class="settings-user-detail-button">
                                    <button class="lb-button">Verify</button>
                                </a>
                            </li>
                        <?php
                        }
                        ?>
                        <li class="settings-user-detail">
                            <button onclick="logout(this);return false" class="lb-button">Logout</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div id="settings-profile">
            <div id="settings-form">
                <form onsubmit="profile(this);return false" autocapitalize="off" autocomplete="off" method="POST">
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
                    <?php if ($banned) : ?>
                        <ul id="form-markdowns" onmousedown="return false" onselectstart="return false">
                            <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/bold.svg" alt="Bold" tabindex="0"></li>
                            <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/italic.svg" alt="Italic" tabindex="0"></li>
                            <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/underline.svg" alt="Underline" tabindex="0"></li>
                            <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/strikethrough.svg" alt="Strikethrough" tabindex="0"></li>
                            <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/comment.svg" alt="Code" tabindex="0"></li>
                            <li class="form-markdown"><img src="<?php echo STATIC_LINK ?>/img/icons/interface/eye-off.svg" alt="Spoiler" tabindex="0"></li>
                        </ul>
                    <?php endif; ?>
                    <textarea class="lb-textarea" id="description" name="description" cols="30" rows="6"><?php echo $data['profile']['description'] ?></textarea>
                    <?php if ($banned) : ?>
                        <button class="lb-button" id="form-submit">Save</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <h2>Devices</h2>
        <div id="settings-devices">
            <div class="settings-devices-section">
                <h3 class="settings-devices-title">Logged devices</h3>
                <p class="settings-devices-description">Here are all the devices that are currently logged with your Darflen account. If you see an entry you don't recognize, log out of that device and immediately change your account password.</p>
            </div>
            <div class="settings-devices-section">
                <ul id="settings-logged-devices">
                    <?php
                    $database = prepare_database();
                    $token = $_COOKIE['token'];
                    $tokens = $database->preparedQuery('SELECT token, data FROM tokens WHERE token = ?', [$token])->fetch(PDO::FETCH_ASSOC);
                    echo loggedDevice('li', $tokens['data'], $tokens['token']);
                    $tokens = $database->preparedQuery('SELECT token, data FROM tokens WHERE account = ? AND token != ?', [$user['id'], $token])->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($tokens as $value) {
                        echo loggedDevice('li', $value['data'], $value['token']);
                    }
                    ?>
                </ul>
            </div>
            <div class="settings-devices-section">
                <h3 class="settings-devices-title">Log out of all known devices</h3>
                <p class="settings-devices-description">You'll have to log back on all logged out devices.</p>
                <button id="logout-devices-submit" onclick="logout_devices()" class="lb-button">Logout all known devices</button>
            </div>
        </div>
        <h2>Invites</h2>
        <div id="settings-invites">
            <p id="settings-invite-title">Invite your friends and get some unknown surprises</p>
            <div id="settings-invites-form">
                <label for="link" class="lb-label">Your invite link:</label>
                <div id="settings-invites-input">
                    <?php
                    $referrer = array_reverse($database->preparedQuery('SELECT referrer FROM invites WHERE account = ?', [$user['id']])->fetchAll(PDO::FETCH_ASSOC))[0]['referrer'];
                    $invited = $database->preparedQuery('SELECT count(users.id) AS result, users.data FROM users RIGHT JOIN invites ON JSON_VALUE(users.data,"$.miscellaneous.invite") = invites.referrer WHERE invites.account = ?', [$user['id']])->fetch(PDO::FETCH_ASSOC)['result'];
                    $activated = $database->preparedQuery('SELECT count(users.id) AS result, users.data FROM users RIGHT JOIN invites ON JSON_VALUE(users.data,"$.miscellaneous.invite") = invites.referrer WHERE invites.account = ? AND EXISTS (SELECT author FROM posts WHERE users.id = posts.author)', [$user['id']])->fetch(PDO::FETCH_ASSOC)['result'];
                    ?>
                    <input id="link_invite" type="text" readonly value="<?php echo ROOT_LINK ?>/join?ref=<?php echo $referrer ?>">
                    <button id="link-generate" onclick="generate_link()" class="lb-button">Generate new link</button>
                </div>
            </div>
            <ul id="settings-invites-stats">
                <li class="settings-invites-stat">
                    <div class="settings-invites-stat-content">
                        <img class="settings-invites-stat-image" src="<?php echo STATIC_LINK ?>/img/icons/interface/users.svg" alt="Icon">
                        <div class="settings-invites-info">
                            <span class="settings-invites-title"><?php echo $invited ?></span>
                            <span class="settings-invites-description">Invited Users</span>
                        </div>
                    </div>
                </li>
                <li class="settings-invites-stat">
                    <div class="settings-invites-stat-content">
                        <img class="settings-invites-stat-image" src="<?php echo STATIC_LINK ?>/img/icons/interface/user-check.svg" alt="Icon">
                        <div class="settings-invites-info">
                            <span class="settings-invites-title"><?php echo $activated ?></span>
                            <span class="settings-invites-description">Activated Users</span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <h2>Miscellaneous</h2>
        <div id="settings-miscellaneous">
            <div class="settings-miscellaneous-section">
                <h3>Account removal</h3>
                <p>Deleting your account is a one way journey, you can not recover anything after doing it.</p>
                <a href="<?php echo ROOT_LINK ?>/settings/delete/">
                    <button class="lb-button">Delete account</button>
                </a>
            </div>
            <div class="settings-miscellaneous-section">
                <h3>Change website theme</h3>
                <p>Change the website theme to your favorite looking one if you don't like the default one.</p>
                <select name="theme" id="settings-themes" class="lb-input" onchange="theme(this)">
                    <option value="light" <?php echo $data['miscellaneous']['theme'] === 'light' ? 'selected' : '' ?> class="settings-theme">Light</option>
                    <option value="dark" class="settings-theme" <?php echo $data['miscellaneous']['theme'] === 'dark' ? 'selected' : '' ?>>Dark</option>
                </select>
            </div>
        </div>
    </div>
</div>

<?php footer() ?>