<?php
redirect_if_logged(ROOT_LINK . '/feed');
$token = isset($_COOKIE['token']) ? $_COOKIE['token'] : 0;
head('', 'en', 'about.css', true);
$database = prepare_database();
$yourself = [];
if (isset($_COOKIE['token']) && check_token_validity($_COOKIE['token'])) {
    $yourself = get_user_info_from_token($_COOKIE['token']);
}
$yourself['id'] = isset($yourself['id']) ? $yourself['id'] : 0;
$posts = recommend_user_posts('trending', 5, 0);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/explore.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/posts.js" async defer></script>

<div id="content">
    <div id="about">
        <div id="about-header-container">
            <div id="about-header-content">
                <div class="about-header-section">
                    <h1 class="about-title">
                        <?php
                        if (round(rand(1, 99)) == 1) {
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
                            <li class="about-button"><a href="<?php echo ROOT_LINK ?>/join/">Create an account</a></li>
                        <?php } ?>
                        <li class="about-button"><a href="<?php echo ROOT_LINK ?>/explore/">Explore the network</a></li>
                    </ul>
                </div>
                <div class="about-header-section">
                    <img class="about-header-main-mark" src="<?php echo STATIC_LINK ?>/img/logo.svg" alt="Mark">
                </div>
            </div>
        </div>
        <div id="about-about-us">
            <div class="about-about-section">
                <div class="about-about-section-container">
                    <img class="about-about-image" src="<?php echo STATIC_LINK ?>/img/a.png" alt="The explore page">
                    <div class="about-about-section-content">
                        <h2 class="about-about-title">Adventure on the website where you belong</h2>
                        <p class="about-about-content">The explore page is the center of the website, where it lets you discover users and posts. No hidden algorithms are controlling you. It's all up to you.</p>
                    </div>
                </div>
            </div>
            <div class="about-about-section">
                <div class="about-about-section-container">
                    <img class="about-about-image" src="<?php echo STATIC_LINK ?>/img/b.png" alt="A post with an image">
                    <div class="about-about-section-content">
                        <h2 class="about-about-title">Sharing is easier than ever</h2>
                        <p class="about-about-content">Think of something you'll love to share, and post it with only a few presses. It's that simple to share. Sharing has never been easier than this.</p>
                    </div>
                </div>
            </div>
            <div class="about-about-section">
                <div class="about-about-section-container">
                    <img class="about-about-image" src="<?php echo STATIC_LINK ?>/img/c.png" alt="Someone profile page">
                    <div class="about-about-section-content">
                        <h2 class="about-about-title">Everything simplified</h2>
                        <p class="about-about-content">Every inch has been designed so that it's easy to navigate and use. It makes everyone's lifes way more easier.</p>
                    </div>

                </div>
            </div>
            <div class="about-about-bottom-section">
                <div class="about-about-bottom-section-container">
                    <div class="about-about-section-bottom-content">
                        <h3 class="about-about-bottom-title">Ready to start your adventure?</h4>
                        <a href="<?php echo ROOT_LINK ?>/join/" class="about-about-bottom-button">
                            <button class="lb-button about-about-rounded-button">Create an account</button>
                        </a>
                        <a href="<?php echo ROOT_LINK ?>/explore/" class="about-about-bottom-button">
                            <button class="lb-button about-about-rounded-button">Explore</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php footer() ?>