<?php
$token = isset($_COOKIE['token']) ? $_COOKIE['token'] : 0;
if (check_token_validity($token) && $title != 'Chat | Darflen') {
?>
    <ul id="bottom-links">
        <li class="bottom-link">
            <a href="<?php echo ROOT_LINK ?>/posts/create/" class="bottom-button" title="Share something">
                <img src="<?php echo STATIC_LINK ?>/img/icons/interface/add.svg" alt="Add icon" class="bottom-image">
            </a>
        </li>
        <!--
        <li class="bottom-link">
            <a href="<?php echo ROOT_LINK ?>/chat/" class="bottom-button" title="Open chat">
                <img src="<?php echo STATIC_LINK ?>/img/icons/interface/message.svg" alt="Message icon" class="bottom-image">
            </a>
        </li>
        -->
        <?php
        $user = get_user_info_from_token($token);
        if (json_decode($user['data'], true)['miscellaneous']['administrator'] == true) {
        ?>
            <li class="bottom-link">
                <a href="<?php echo ROOT_LINK ?>/internal/" class="bottom-button" title="Open admin panel">
                    <img src="<?php echo STATIC_LINK ?>/img/icons/interface/window.svg" alt="Window icon" class="bottom-image">
                </a>
            </li>
        <?php } ?>
    </ul>
<?php } ?>

<div id="navbar">
    <div id="nav-container">
        <div class="nav-section">
            <a id="nav-logo" href="<?php echo ROOT_LINK ?>">
                <img src="<?php echo STATIC_LINK ?>/img/logo.svg" alt="Darflen logo">
            </a>
            <ul>
                <li>
                    <a href="<?php echo ROOT_LINK ?>" class="nav-button">
                        <img src="<?php echo STATIC_LINK ?>/img/icons/interface/home.svg" alt="Home menu icon" title="User main menu">
                        <span>Home</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo ROOT_LINK ?>/explore" class="nav-button">
                        <img src="<?php echo STATIC_LINK ?>/img/icons/interface/compass.svg" alt="Explore Darflen icon" title="Explore the website and discover new things">
                        <span>Explore</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="nav-section">
            <form class="nav-search" action="<?php echo ROOT_LINK ?>/explore/search" method="GET" autocomplete="off" autocapitalize="off">
                <input type="search" name="s" placeholder="Search" value="<?php echo htmlspecialchars(isset($_GET['s']) ? $_GET['s'] : '', ENT_QUOTES) ?>" minlength="1">
                <button type="submit" tabindex="-1">
                    <img src="<?php echo STATIC_LINK ?>/img/icons/interface/search.svg" alt="Search submit icon">
                </button>
            </form>
        </div>
        <div class="nav-section">
            <?php
            if (check_token_validity($token)) {
                $user = get_user_info_from_token($token);
            ?>
                <ul id="nav-account">
                    <li>
                        <a href="<?php echo ROOT_LINK ?>/notifications" class="nav-account-button" title="User notifications showing updates and notices">
                            <img src="<?php echo STATIC_LINK ?>/img/icons/interface/notification.svg" alt="User notifications icon">
                            <?php
                            $database = prepare_database();
                            $unread = $database->preparedQuery('SELECT count(id) AS result FROM notifications WHERE JSON_VALUE(data,"$.miscellaneous.read") = ? AND user = ?', [0, $user['id']])->fetch(PDO::FETCH_ASSOC)['result'];
                            if ($unread > 0) {
                            ?>
                                <span class="nav-account-bubble"><?php echo $unread ?></span>
                            <?php
                            }
                            ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo ROOT_LINK ?>/settings" class="nav-account-button" title="User account settings">
                            <img src="<?php echo STATIC_LINK ?>/img/icons/interface/settings.svg" alt="User settings icon">
                            <!-- <span class="nav-account-bubble">!</span> -->
        </a>
        </li>
        <li>
            <a id="nav-profile" href="<?php echo ROOT_LINK ?>/users/<?php echo $user['identifier']; ?>">
                <img id="nav-profile-icon" src="<?php echo json_decode($user['data'], true)['profile']['icon'] ?>" alt="User profile icon">
                <span><?php echo json_decode($user['data'], true)['username'] ?></span>
            </a>
        </li>
    </ul>
<?php
            } else {
?>
    <ul id="nav-no-account">
        <li>
            <a href="<?php echo ROOT_LINK ?>/join/" class="nav-no-account-button">
                <span>Join</span>
            </a>
        </li>
        <li>
            <a href="<?php echo ROOT_LINK ?>/login/" class="nav-no-account-button">
                <span>Login</span>
            </a>
        </li>
    </ul>
<?php
            }
?>
</div>
</div>
</div>