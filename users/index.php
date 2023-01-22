<?php
if (empty($_GET['u']) || !is_string($_GET['u']) || !$user = get_user_info_from_identifier($_GET['u'])) {
    http_response_code(404);
    include_once(DOCUMENT_ROOT . '/errors/404.php');
    exit;
}
$id = $user['id'];
head(json_decode($user['data'], true)['username'] . '\'s Profile', 'en', 'profile.css', true, sprintf('%s is a '.WEBSITE.' user. Join '.WEBSITE.' to start sharing and connecting with %s or your friends and people you know worldwide.', json_decode($user['data'], true)['username'], json_decode($user['data'], true)['username']), json_decode($user['data'], true)['profile']['icon']);
$database = prepare_database();
$followers = $database->preparedQuery('SELECT count(id) AS fvalue FROM follows WHERE follower = ? UNION ALL SELECT count(id) AS fvalue FROM follows WHERE following = ?', [$id, $id])->fetchAll(PDO::FETCH_ASSOC);
$yourself = [];
if (isset($_COOKIE['token']) && check_token_validity($_COOKIE['token'])) {
    $yourself = get_user_info_from_token($_COOKIE['token']);
    $followed = $database->preparedQuery('SELECT count(id) AS fvalue FROM follows WHERE follower = ? AND following = ?', [$yourself['id'], $id])->fetch(PDO::FETCH_ASSOC)['fvalue'] > 0;
} else {
    $followed = false;
}
$yourself['id'] = isset($yourself['id']) ? $yourself['id'] : 0;
$total_posts = get_user_posts($id);
$total_posts = count(unindex_posts_not_public($total_posts, $database));
$total_hits = $database->preparedQuery('SELECT SUM(JSON_VALUE(data, "$.miscellaneous.hits")) AS sum FROM posts WHERE author = ? AND JSON_VALUE(data, "$.miscellaneous.coverage") = ?', [$id, "public"])->fetch(PDO::FETCH_ASSOC)["sum"];
$total_hits = !$total_hits ? 0 : $total_hits;
update_views_badge($user, $total_hits);
$posts = get_user_posts($id, 10, 0);
// Unindex total posts but not really.
$total = $database->preparedQuery('
SELECT count(loves.id) AS loves FROM loves 
INNER JOIN posts ON posts.id = loves.pid
WHERE type = ? AND profile = ? AND JSON_VALUE(posts.data,"$.miscellaneous.coverage") = ?
', ['post', $id, 'public'])->fetch(PDO::FETCH_ASSOC)['loves'];
update_loves_badge($user, $total);
update_posts_badge($user, $total_posts);
update_follow_badge($user, $followers[1]['fvalue']);
$invited = $database->preparedQuery('SELECT count(users.id) AS result, users.data FROM users RIGHT JOIN invites ON JSON_VALUE(users.data,"$.miscellaneous.invite") = invites.referrer WHERE invites.account = ?', [$user['id']])->fetch(PDO::FETCH_ASSOC)['result'];
$activated = $database->preparedQuery('SELECT count(users.id) AS result, users.data FROM users RIGHT JOIN invites ON JSON_VALUE(users.data,"$.miscellaneous.invite") = invites.referrer WHERE invites.account = ? AND EXISTS (SELECT author FROM posts WHERE users.id = posts.author)', [$user['id']])->fetch(PDO::FETCH_ASSOC)['result'];
update_settings_badge($user, $invited, $activated);
$posts = unindex_posts_not_public($posts, $database);
$badges = $database->preparedQuery("SELECT badge FROM badges WHERE account = ?", [$user["id"]])->fetchAll(PDO::FETCH_ASSOC);
$activity = $database->preparedQuery('SELECT activity FROM status WHERE account = ?', [$user['id']])->fetch(PDO::FETCH_ASSOC);
$activity = is_bool($activity) ? false : $activity['activity'];
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/profile.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/posts.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/video.js" async defer></script>

<div id="content">
    <div id="profile">
        <div id="profile-banner">
            <img id="profile-banner-image" src="<?php echo json_decode($user['data'], true)['profile']['banner'] ?>" alt="User banner">
            <div id="profile-banner-container">
                <div class="profile-banner-section">
                    <ul id="profile-banner-aside-buttons">
                        <li>
                            <?php
                            if (isset($_COOKIE['token']) && $id == $yourself['id']) {
                            ?>
                                <a href="<?php echo ROOT_LINK ?>/settings/" class="profile-banner-aside-button">
                                    <img src="<?php echo STATIC_LINK ?>/img/icons/interface/edit-alt.svg" alt="Edit profile">
                                </a>
                            <?php
                            } elseif (isset($_COOKIE['token'])) {
                            ?>
                                <a href="<?php echo ROOT_LINK ?>/users/<?php echo $user['identifier'] ?>/report" class="profile-banner-aside-button">
                                    <img src="<?php echo STATIC_LINK ?>/img/icons/interface/flag.svg" alt="Report profile">
                                </a>
                            <?php
                            }
                            ?>
                        </li>
                    </ul>
                </div>
                <div class="profile-banner-section">
                    <div class="profile-banner-bottom-section">
                        <div id="profile-banner-icon-contents">
                            <img id="profile-banner-icon" src="<?php echo json_decode($user['data'], true)['profile']['icon'] ?>" alt="User icon">
                            <?php
                            if ($activity > 0) {
                            ?>
                                <span class="profile-banner-icon-status icon-status-<?php echo $activity == 2 ? 'inactive' : 'active' ?>"></span>
                            <?php
                            }
                            ?>
                        </div>
                        <span id="profile-banner-user"><?php echo json_decode($user['data'], true)['username'] ?></span>
                        <ul id="profile-banner-badges">
                            <?php
                            if (json_decode($user['data'], true)['miscellaneous']['administrator'] == true) {
                            ?>
                                <li class="profile-banner-badge">
                                    <img src="<?php echo STATIC_LINK ?>/img/icons/interface/settings.svg" alt="Administrator badge" title="Administrator badge">
                                </li>
                            <?php
                            }
                            if (json_decode($user['data'], true)['miscellaneous']['user_verified'] == true) {
                            ?>
                                <li class="profile-banner-badge">
                                    <img src="<?php echo STATIC_LINK ?>/img/icons/interface/user-check.svg" alt="Verified user badge" title="Verified user badge">
                                </li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="profile-banner-bottom-section">
                        <ul id="profile-banner-stats">
                            <li class="profile-banner-stat">
                                <a href="<?php echo ROOT_LINK ?>/users/<?php echo $user['identifier'] ?>/following">
                                    <span class="profile-banner-stats-item">Following</span>
                                    <span class="profile-banner-stats-count"><?php echo $followers[0]['fvalue'] ?></span>
                                </a>
                            </li>
                            <li class="profile-banner-stat">
                                <a href="<?php echo ROOT_LINK ?>/users/<?php echo $user['identifier'] ?>/followers">
                                    <span class="profile-banner-stats-item">Followers</span>
                                    <span class="profile-banner-stats-count"><?php echo $followers[1]['fvalue'] ?></span>
                                </a>
                            </li>
                            <li class="profile-banner-stat">
                                <a href="<?php echo ROOT_LINK ?>/users/<?php echo $user['identifier'] ?>/hearts">
                                    <span class="profile-banner-stats-item">Hearts</span>
                                    <span class="profile-banner-stats-count"><?php echo $total ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <?php
                    if (isset($_COOKIE['token']) && $yourself['id'] != $id) {
                    ?>
                        <div class="profile-banner-bottom-section">
                            <button id="profile-follow-button<?php echo $followed ? '-followed' : ''; ?>" onclick="follow(this,<?php echo $id ?>)">
                                <img src="<?php echo STATIC_LINK ?>/img/icons/interface/user-<?php echo $followed ? 'remove' : 'add'; ?>.svg" alt="<?php echo $followed ? 'Unfollow' : 'Follow'; ?> user icon">
                                <span><?php echo $followed ? 'Unfollow' : 'Follow'; ?></span>
                            </button>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <div id="profile-contents">
            <div class="profile-contents-section">
                <div class="profile-contents-box">
                    <?php userCard($id) ?>
                    <h2>About Me</h2>
                    <p><?php echo parse_post_text(json_decode($user['data'], true)['profile']['description']) ?></p>
                    <!--
                    <ul id="profile-about-links">
                        <li class="profile-about-link">
                            <a href="<?php echo ROOT_LINK ?>">
                                <img src="<?php echo STATIC_LINK ?>/img/icons/interface/link.svg" alt="Link icon">
                                <span><?php echo ROOT_LINK ?></span>
                            </a>
                        </li>
                    </ul>
                    -->
                    <?php //echo $total_hits 
                    ?>
                </div>
                <div class="profile-contents-box">
                    <h2>Statistics</h2>
                    <ul id="profile-stats">
                        <li class="profile-stat">
                            <a href="<?php echo ROOT_LINK ?>/posts/<?php echo $user['identifier'] ?>/user">
                                <span class="profile-stats-item">Posts</span>
                                <span class="profile-banner-stats-count"><?php echo $total_posts ?></span>
                            </a>
                        </li>
                        <li class="profile-stat">
                            <a href="<?php echo ROOT_LINK ?>/users/<?php echo $user['identifier'] ?>/followers">
                                <span class="profile-stats-item">Followers</span>
                                <span class="profile-banner-stats-count"><?php echo $followers[1]['fvalue'] ?></span>
                            </a>
                        </li>
                        <li class="profile-stat">
                            <a href="<?php echo ROOT_LINK ?>/users/<?php echo $user['identifier'] ?>/following">
                                <span class="profile-stats-item">Following</span>
                                <span class="profile-stats-count"><?php echo $followers[0]['fvalue'] ?></span>
                            </a>
                        </li>
                        <li class="profile-stat">
                            <a href="<?php echo ROOT_LINK ?>/users/<?php echo $user['identifier'] ?>/hearts">
                                <span class="profile-stats-item">Hearts</span>
                                <span class="profile-stats-count"><?php echo $total ?></span>
                            </a>
                        </li>
                        <li class="profile-stat">
                            <div>
                                <span class="profile-stats-item">Joined</span>
                                <span class="profile-stats-count"><?php echo time_ago(json_decode($user['data'], true)['miscellaneous']['creation_time'])/*. ' ('.date('d/m/Y', json_decode($user['data'], true)['miscellaneous']['creation_time']).')' */ ?></span>
                            </div>
                        </li>
                    </ul>
                </div>
                    <div class="profile-contents-box">
                        <h2>Achievements - <?php echo count($badges) ?></h2>
                        <ul id="badges-container">
                            <?php foreach ($badges as $key => $value) : ?>
                                <?php if ($key < 9) : ?>
                                    <li class="badge-content">
                                        <a href="<?php echo ROOT_LINK ?>/achievements#<?php echo $value["badge"] ?>">
                                            <img src="<?php echo STATIC_LINK ?>/img/icons/achievements/<?php echo $value["badge"] ?>.svg" alt="Badge image" class="badge-content-image">
                                        </a>
                                    </li>
                                <?php else : ?>
                                    <a class="badges-content-more" href="<?php echo ROOT_LINK ?>/users/<?php echo $user['identifier'] ?>/achievements">
                                        <button class="lb-button">See more achievements</button>
                                    </a>
                                <?php break;
                                endif; ?> <?php endforeach; ?>
                        </ul>
                    </div>
            </div>
            <div class="profile-contents-section">
                <?php
                if (isset($yourself['id']) && $yourself['id'] == $id) {
                ?>
                    <a class="profile-user-post-buttons" href="<?php echo ROOT_LINK ?>/posts/create/">
                        <button class="lb-button">Share something</button>
                    </a>
                <?php
                }
                ?>

                <?php
                //var_dump($posts);
                if ($posts) {
                    foreach ($posts as $post) {
                        $loves = $database->preparedQuery('SELECT count(id) AS loves FROM loves WHERE type = ? AND pid = ?', ['post', $post['id']])->fetch(PDO::FETCH_ASSOC)['loves'];
                        $loved = $database->preparedQuery('SELECT count(id) AS loved FROM loves WHERE type = ? AND pid = ? AND user = ?', ['post', $post['id'], $yourself['id']])->fetch(PDO::FETCH_ASSOC)['loved'] > 0;
                        $replies = $database->preparedQuery('SELECT count(id) as replies FROM replies WHERE post = ?', [$post['id']])->fetch(PDO::FETCH_ASSOC)['replies'];
                        echo build_post($post, $user, $yourself, $replies, $loved, $loves);
                    }
                } elseif ($yourself['id'] != $id) {
                ?>
                    <p id="empty-profile"><?php echo json_decode($user['data'], true)['username'] ?> hasn't posted anything yet! Come back later to see if they've got something to share.</p>
                <?php
                }
                if ($total_posts > 5) {
                ?>
                    <a class="profile-user-post-buttons" href="<?php echo ROOT_LINK ?>/posts/<?php echo $user['identifier'] ?>/user">
                        <button class="lb-button">See more posts</button>
                    </a>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php footer() ?>