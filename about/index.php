<?php
$token = isset($_COOKIE['token']) ? $_COOKIE['token'] : 0;
head('About Us', 'en', 'about.css', true);
$database = prepare_database();
$yourself = [];
if (isset($_COOKIE['token']) && check_token_validity($_COOKIE['token'])) {
    $yourself = get_user_info_from_token($_COOKIE['token']);
}
$yourself['id'] = isset($yourself['id']) ? $yourself['id'] : 0;
$posts = recommend_user_posts('trending', 5, 0);
?>

<script src="https://www.darflen.com/includes/js/explore.js" async defer></script>
<script src="https://www.darflen.com/includes/js/posts.js" async defer></script>

<div id="content">
    <div id="about">
        <div id="about-header-container">
            <div id="about-header-content">
                <div class="about-header-section">
                    <h1 class="about-title">
                        <?php
                        if (round(rand(1,100)) == 1) {
                            echo 'Edit your posts, for free.';
                        } else {
                            echo 'Share yourself';
                        }
                        ?>
                    </h1>
                    <p class="about-description">Darflen, your social media for sharing and connecting with your friends and people you know worldwide.</p>
                    <ul class="about-buttons">
                        <?php
                        if (!check_token_validity($token)) {
                        ?>
                            <li class="about-button"><a href="https://www.darflen.com/join/">Create an account</a></li>
                        <?php } ?>
                        <li class="about-button"><a href="https://www.darflen.com/explore/">Explore the network</a></li>
                    </ul>
                </div>
                <div class="about-header-section">
                    <img class="about-header-main-mark" src="https://static.darflen.com/img/logo.svg" alt="Mark">
                </div>
            </div>
        </div>
        <div id="about-posts">
            <h2>Top 5 Trending posts</h2>
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

<?php footer() ?>