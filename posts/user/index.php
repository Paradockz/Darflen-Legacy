<?php
if (empty($_GET['u']) || !$user = get_user_info_from_identifier($_GET['u'])) {
    http_response_code(404);
    include_once(DOCUMENT_ROOT . '/errors/404.php');
    exit;
}
$id = $_GET['u'];
head(json_decode($user['data'], true)['username'] . '\'s Posts', 'en', 'profile.css', true);
$database = prepare_database();
$yourself = [];
if (isset($_COOKIE['token']) && check_token_validity($_COOKIE['token'])) {
    $yourself = get_user_info_from_token($_COOKIE['token']);
}
$yourself['id'] = isset($yourself['id']) ? $yourself['id'] : 0;
$total_posts = get_user_total_posts(get_user_info_from_identifier($id)['id']);
$paginator = 10;
$paginator_count = ceil($total_posts / $paginator) + 1;
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
$posts = get_user_posts($user['id'], $paginator, $paginator * $paginator_page);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/profile.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/posts.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/video.js" async defer></script>

<div id="content">
    <div id="profile-posts">
        <div class="post-section post-section-no-margins">
            <h1><?php echo json_decode($user['data'], true)['username'] ?>'s Posts</h1>
        </div>
        <div class="post-section">
            <?php
            if ($posts) {
                $posts = unindex_posts_not_public($posts, $database);
                foreach ($posts as $post) {
                    $loves = $database->preparedQuery('SELECT count(id) AS loves FROM loves WHERE type = ? AND pid = ?', ['post', $post['id']])->fetch(PDO::FETCH_ASSOC)['loves'];
                    $loved = $database->preparedQuery('SELECT count(id) AS loved FROM loves WHERE type = ? AND pid = ? AND user = ?', ['post', $post['id'], $yourself['id']])->fetch(PDO::FETCH_ASSOC)['loved'] > 0;
                    $replies = $database->preparedQuery('SELECT count(id) as replies FROM replies WHERE post = ?', [$post['id']])->fetch(PDO::FETCH_ASSOC)['replies'];
                    echo build_post($post, $user, $yourself, $replies, $loved, $loves);
                }
            } elseif ($yourself['id'] != $id && ($paginator_page < $paginator_count)) {
            ?>
                <p id="empty-profile"><?php echo json_decode($user['data'], true)['username'] ?> hasn't posted anything yet! Come back later to see if they've got something to share.</p>
            <?php
            }
            ?>
            <ul id="profile-posts-paginator">
                <?php
                $pg = $_GET['page'] ?? 1;
                if ($pg > 1) : $p = $pg - 1 ?>
                    <li class="profile-posts-paginator-page">
                        <a href="<?php echo ROOT_LINK ?>/posts/<?php echo $user['identifier'] ?>/user<?php echo '?page=' . $p ?>">
                            <</a>
                    </li>
                <?php endif;
                generate_paginator_1($pg, 1, $user);
                generate_paginator_1($pg, 2, $user);
                generate_paginator_1($pg, 3, $user);
                generate_paginator_2($pg, $paginator_count - 3, $user);
                generate_paginator_2($pg, $paginator_count - 2, $user);
                generate_paginator_2($pg, $paginator_count - 1, $user);
                if ($pg + 1 < $paginator_count) : $p = $pg + 1 ?>
                    <li class="profile-posts-paginator-page">
                        <a href="<?php echo ROOT_LINK ?>/posts/<?php echo $user['identifier'] ?>/user<?php echo '?page=' . $p ?>">></a>
                    </li>
                <?php endif ?>
            </ul>
        </div>
    </div>
</div>

<?php footer() ?>