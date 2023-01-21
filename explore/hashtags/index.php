<?php
session_start();
$_SESSION['offset'] = 0;
$_SESSION['mode'] = 'hashtags';
$database = prepare_database();
$yourself = [];
if (isset($_COOKIE['token']) && check_token_validity($_COOKIE['token'])) {
    $yourself = get_user_info_from_token($_COOKIE['token']);
}
$search = isset($_GET['s2']) ? $_GET['s2'] : '';
$yourself['id'] = isset($yourself['id']) ? $yourself['id'] : 0;
if (!empty($search)) {
    head('Hashtag #' . $search, 'en', 'explore.css', true);
    if (!in_array($search, ['*'])) {
        $posts = recommend_user_posts($_SESSION['mode'], 10, $_SESSION['offset'], $search);
        $_SESSION['query'] = $search;
    } else {
        $posts = [];
    }
} else {
    $total = count($database->rawQuery('SELECT count(hashtag) as result FROM hashtags  GROUP BY hashtag')->fetchAll(PDO::FETCH_ASSOC));
    $paginator = 24;
    $paginator_count = ceil($total / $paginator) + 1;
    if (isset($_GET['page'])) {
        $paginator_page = $_GET['page'];
        if ($paginator_page != 0) {
            $paginator_page -= 1;
        }
        if ($_GET['page'] > $paginator_count - 1 && $paginator_count > 0) {
            http_response_code(404);
            include_once(DOCUMENT_ROOT . '/errors/404.php');
            exit;
        }
    } else {
        $paginator_page = 0;
    }
    head('Hashtags', 'en', 'explore.css', true);
    $hashtags = recommend_hashtags($_SESSION['mode'], $paginator, $paginator * $paginator_page, $search);
}
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/explore.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/posts.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/video.js" async defer></script>

<div id="content">
    <div id="explore">
        <?php
        if (!empty($search)) {
        ?>
            <div class="explore-section explore-not-complete-width">
                <h1>Searching <?php echo '#' . $search ?></h1>
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
            <?php }
        } else {
            ?>
            <div class="explore-section">
                <h1>Hashtags</h1>
            </div>
            <div class="explore-section">
                <div id="explore-hashtags">
                    <?php
                    if ($hashtags) {
                        foreach ($hashtags as $hashtag) {
                            hashtag('#' . $hashtag['hashtag'], '/explore/hashtags/' . $hashtag['hashtag'], sprintf('View %s Posts', $hashtag['count']));
                        }
                    } elseif (count($hashtags) < 1) {
                    ?>
                        <p id="empty-hashtags">No hashtags found.</p>
                    <?php
                    }
                    ?>
                </div>
            </div>
            <div class="explore-section">
                <div id="explore-paginator">
                    <div class="explore-paginator-section">
                        <ul id="profile-posts-paginator">
                            <?php
                            $pg = $_GET['page'] ?? 1;
                            if ($pg > 1) : $p = $pg - 1 ?>
                                <li class="profile-posts-paginator-page">
                                    <a href="<?php echo ROOT_LINK ?>/explore/hashtags/<?php echo '?page=' . $p ?>">
                                        <</a>
                                </li>
                            <?php endif;
                            generate_paginator_5($pg, 1);
                            generate_paginator_5($pg, 2);
                            generate_paginator_5($pg, 3);
                            generate_paginator_6($pg, $paginator_count - 3);
                            generate_paginator_6($pg, $paginator_count - 2);
                            generate_paginator_6($pg, $paginator_count - 1);
                            if ($pg + 1 < $paginator_count) : $p = $pg + 1 ?>
                                <li class="profile-posts-paginator-page internal-paginator">
                                    <a href="<?php echo ROOT_LINK ?>/explore/hashtags/<?php echo '?page=' . $p ?>">></a>
                                </li>
                            <?php endif ?>
                        </ul>
                    </div>
                    <div class="explore-paginator-section">
                        <div class="internal-paginator-section">
                            <p class="internal-paginator-info">Showing hashtags <?php echo $paginator_page * $paginator + 1 ?> to <?php echo ($paginator_page * $paginator + $paginator) > $total ? $total : ($paginator_page * $paginator + $paginator) ?> in <?php echo $total ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</div>

<?php footer() ?>