<?php
session_start();
$_SESSION['offset'] = 0;
$_SESSION['mode'] = 'search';
head('Search', 'en', 'explore.css', true);
$database = prepare_database();
$yourself = [];
if (isset($_COOKIE['token']) && check_token_validity($_COOKIE['token'])) {
    $yourself = get_user_info_from_token($_COOKIE['token']);
}
$search = isset($_GET['s']) ? $_GET['s'] : '';
$yourself['id'] = isset($yourself['id']) ? $yourself['id'] : 0;
if (!in_array($search, ['*', ''])) {
    $posts = recommend_user_posts($_SESSION['mode'], 10, $_SESSION['offset'], $search);
} else {
    $posts = [];
}
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/explore.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/posts.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/video.js" async defer></script>

<div id="content">
    <div id="explore">
        <div class="explore-section explore-not-complete-width">
            <h1>Searching <?php echo '"' . htmlspecialchars($search, ENT_QUOTES) . '"' ?></h1>
            <form id="explore-search" class="nav-search" action="<?php echo ROOT_LINK ?>/explore/search" method="GET" autocomplete="off" autocapitalize="off">
                <input type="text" name="s" placeholder="Search anything..." value="<?php echo htmlspecialchars(isset($_GET['s']) ? $_GET['s'] : '', ENT_QUOTES) ?>">
                <button type="submit" tabindex="-1">
                    <img src="<?php echo STATIC_LINK ?>/img/icons/interface/search.svg" alt="Search submit icon">
                </button>
            </form>
        </div>
        <div class="explore-section" id="explore-posts-container">
            <div id="explore-posts-wide">
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
        <?php if (count($posts) > 9) { ?>
            <button class="lb-button profile-user-post-buttons" onclick="load_more(this,<?php echo $_SESSION['offset'] ?>)">See more posts</button>
        <?php } elseif (count($posts) < 1) { ?>
            <p id="empty-explore">Nothing found with this query.</p>
        <?php } ?>
    </div>
</div>

<?php footer() ?>