<?php
redirect_if_not_logged(ROOT_LINK);
$yourself = [];
if (isset($_COOKIE['token']) && check_token_validity($_COOKIE['token'])) {
    $yourself = get_user_info_from_token($_COOKIE['token']);
}
$yourself['id'] = isset($yourself['id']) ? $yourself['id'] : 0;
$_SESSION['offset'] = 0;
$_SESSION['mode'] = 'feed';
head('Feed', 'en', 'explore.css',true, '', '', 'Darflen', false);
$database = prepare_database();
$posts = recommend_user_posts($_SESSION['mode'], 10, $_SESSION['offset'], $yourself['id']);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/explore.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/posts.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/video.js" async defer></script>


<div id="content">
    <div id="explore">
        <h1 class="explore-not-complete-width explore-no-margin-bottom">Feed</h1>
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
        <?php if (count($posts) > 10) { ?>
            <button class="lb-button profile-user-post-buttons" onclick="load_more(this,<?php echo $_SESSION['offset'] ?>)">See more posts</button>
        <?php } elseif (count($posts) < 1) { ?>
            <p id="empty-explore">Looks like you have nothing in your feed.</p>
        <?php } ?>
    </div>
</div>

<?php footer() ?>