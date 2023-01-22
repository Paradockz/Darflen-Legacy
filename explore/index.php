<?php
$_SESSION['offset'] = 0;
if (!isset($_SESSION['mode']) || !in_array($_SESSION['mode'], ['recent', 'popular', 'trending'])) {
    $_SESSION['mode'] = 'trending';
}
head('Explore', 'en', 'explore.css', true);
$database = prepare_database();
$yourself = [];
if (isset($_COOKIE['token']) && check_token_validity($_COOKIE['token'])) {
    $yourself = get_user_info_from_token($_COOKIE['token']);
}
$yourself['id'] = isset($yourself['id']) ? $yourself['id'] : 0;
$posts = recommend_user_posts($_SESSION['mode'], 10, $_SESSION['offset']);
$users = $database->rawQuery('SELECT users.id AS user FROM users LEFT JOIN (SELECT following, count(id) AS count FROM follows GROUP BY following) AS yes ON users.id = yes.following ORDER BY yes.count DESC LIMIT 15')->fetchAll(PDO::FETCH_ASSOC);
$hashtags = $database->preparedQuery("SELECT UNIQUE hashtag,COUNT(id) as count FROM hashtags GROUP BY hashtag ORDER BY count DESC LIMIT ?,?", [0, 10])->fetchAll(PDO::FETCH_ASSOC);
$activity = $database->rawQuery('SELECT count(id) AS count FROM status')->fetch(PDO::FETCH_ASSOC)['count'];
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/explore.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/posts.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/video.js" async defer></script>

<div id="content">
    <div id="explore">
        <h1>Explore</h1>
        <div class="eplore-section">
            <form id="explore-search" class="nav-search" action="<?php echo ROOT_LINK ?>/explore/search" method="GET" autocomplete="off" autocapitalize="off">
                <input type="text" name="s" placeholder="Search anything..." value="<?php echo htmlspecialchars(isset($_GET['s']) ? $_GET['s'] : '', ENT_QUOTES) ?>">
                <button type="submit" tabindex="-1">
                    <img src="<?php echo STATIC_LINK ?>/img/icons/interface/search.svg" alt="Search submit icon">
                </button>
            </form>
            <ul id="stats-accounts-grid">
                <?php foreach ($users as $user) { ?>
                    <li class="stats-account"><?php userCard($user['user']) ?></li>
                <?php } ?>
            </ul>
        </div>
        <div class="explore-section" id="explore-posts-container-larger">
            <select id="posts-mode" class="lb-input" name="mode" onchange="mode(this)">
                <option value="trending" <?php echo $_SESSION['mode'] == 'trending' ? 'selected' : '' ?>>Trending</option>
                <option value="loved" <?php echo $_SESSION['mode'] == 'loved' ? 'selected' : '' ?>>Loved</option>
                <option value="popular" <?php echo $_SESSION['mode'] == 'popular' ? 'selected' : '' ?>>Popular</option>
                <option value="recent" <?php echo $_SESSION['mode'] == 'recent' ? 'selected' : '' ?>>Recent</option>
            </select>
            <div id="explore-contents">
                <div id="explore-additional-contents">
                    <div class="explore-additional-content">
                        <div class="explore-stat-content">
                            <div class="explore-stat-section">
                                <img class="explore-stat-image" src="<?php echo STATIC_LINK ?>/img/icons/interface/users.svg" alt="Users icon">
                            </div>
                            <div class="explore-stat-section">
                                <span class="explore-stat-title"><?php echo $activity < 1 ? 1 : $activity ?></span>
                                <span class="explore-stat-info">Online users</span>
                            </div>
                        </div>
                    </div>
                    <div class="explore-additional-content">
                        <span class="explore-additional-content-title">Popular Hashtags</span>
                        <ul id="explore-content-hashtags">
                            <?php if ($hashtags) {
                                foreach ($hashtags as $hashtag) {
                            ?>
                                    <li class="explore-content-hashtag">
                                        <a href="<?php echo ROOT_LINK ?>/explore/hashtags/<?php echo $hashtag['hashtag'] ?>">
                                            <span class="explore-content-hashtag-title">#<?php echo $hashtag['hashtag'] ?></span>
                                            <span class="explore-content-hashtag-info"><?php echo $hashtag['count'] ?> Posts</span>
                                        </a>
                                    </li>
                            <?php
                                }
                            }
                            ?>
                        </ul>
                        <a href="<?php echo ROOT_LINK ?>/explore/hashtags/">
                            <button class="lb-button">Show every hashtag</button>
                        </a>
                    </div>
                    <div class="explore-additional-content">
                        <span class="explore-additional-content-title">Random Users</span>
                        <div class="explore-additional-content-big-cards">
                            <?php
                            // Fix this code immediately.
                            $database = prepare_database();

                            $victims_count = $database->preparedQuery("SELECT count(id) AS count FROM users", [])->fetch(PDO::FETCH_ASSOC)['count'];
                            $victims = [];
                            $start_seed = floor(time() / 120);
                            $index = 0;
                            while (count($victims) <= 5) {
                                srand($start_seed + $index);
                                $random = rand(1, $victims_count);
                                if (!in_array($random, $victims) && get_user_info_from_id($random)) {
                                    array_push($victims, $random);
                                    UserCard($random, true);
                                } else {
                                    srand($start_seed + $index);
                                    $random = rand(1, $victims_count);
                                    $index++;
                                }
                                $index++;
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div id="explore-posts">
                    <?php
                    if ($posts) {
                        foreach ($posts as $post) {
                            $loves = $database->preparedQuery('SELECT count(id) AS loves FROM loves WHERE type = ? AND pid = ?', ['post', $post['id']])->fetch(PDO::FETCH_ASSOC)['loves'];
                            $loved = $database->preparedQuery('SELECT count(id) AS loved FROM loves WHERE type = ? AND pid = ? AND user = ?', ['post', $post['id'], $yourself['id']])->fetch(PDO::FETCH_ASSOC)['loved'] > 0;
                            $replies = $database->preparedQuery('SELECT count(id) as replies FROM replies WHERE post = ?', [$post['id']])->fetch(PDO::FETCH_ASSOC)['replies'];
                            $user = get_user_info_from_id($post['author']);
                            echo build_post($post, $user, $yourself, $replies, $loved, $loves);
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php if (count($posts) > 7) { ?>
            <button class="lb-button profile-user-post-buttons-explore" onclick="load_more(this,<?php echo $_SESSION['offset'] ?>)">See more posts</button>
        <?php } elseif (count($posts) < 1) { ?>
            <p id="empty-explore">Nothing found.</p>
        <?php } ?>
    </div>
</div>

<?php footer() ?>