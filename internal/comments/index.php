<?php
redirect_if_not_logged(ROOT_LINK);
$token = $_COOKIE['token'];
$database = prepare_database();
$user = get_user_info_from_token($token);
if (!json_decode($user['data'], true)['miscellaneous']['administrator']) {
    http_response_code(404);
    include_once(DOCUMENT_ROOT . '/errors/404.php');
    exit;
}
$yourself = [];
if (isset($_COOKIE['token']) && check_token_validity($_COOKIE['token'])) {
    $yourself = get_user_info_from_token($_COOKIE['token']);
}
$yourself['id'] = isset($yourself['id']) ? $yourself['id'] : 0;
$things = ['replies' => 'message-alt', 'subreplies' => 'message-alt'];
foreach ($things as $key => $value) {
    $data[$key] = $database->rawQuery('SELECT COUNT(id) AS count FROM ' . $key)->fetch(PDO::FETCH_ASSOC)['count'];
}
$paginator = 10;
$paginator_count = ceil($data['replies'] / $paginator) + 1;
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
$posts = $database->preparedQuery('SELECT id,author,text,data FROM replies ORDER BY JSON_VALUE(data,"$.miscellaneous.creation_time") DESC LIMIT ?,?', [$paginator * $paginator_page, $paginator])->fetchAll(PDO::FETCH_ASSOC);
head('Website Comments', 'en', 'internal.css', true, '', '', WEBSITE, false);
?>
<script src="<?php echo ROOT_LINK ?>/includes/js/explore.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/posts.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/internal.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/video.js" async defer></script>


<div id="content">
    <div id="internal">
        <h1 id="internal-title"><?php echo WEBSITE ?> Comments</h1>
        <div id="internal-contents">
            <div class="internal-section">
                <ul class="internal-micro-stats">
                    <?php
                    internalMicroStat('Comments', $data['replies'], 'message-alt.svg');
                    internalMicroStat('Replies', $data['subreplies'], 'message-alt.svg', 'replies');
                    ?>
                </ul>
            </div>
            <div class="internal-section">
                <?php
                foreach ($posts as $post) {
                ?>
                    <div class="internal-post">
                        <div class="internal-actions">
                            <ul class="internal-table-list">
                                <li>
                                    <a href="<?php echo ROOT_LINK ?>/internal/<?php echo $post['id'] ?>/edit">
                                        <button class="lb-button internal-table-button">
                                            <img src="<?php echo STATIC_LINK ?>/img/icons/interface/edit-alt.svg" alt="User add icon">
                                        </button>
                                    </a>
                                </li>
                                <li>
                                    <button class="lb-button internal-table-button" onclick="disappear2('<?php echo $post['id'] ?>', 'Comment');return false">
                                        <img src="<?php echo STATIC_LINK ?>/img/icons/interface/delete.svg" alt="User remove icon">
                                    </button>
                                </li>
                                <li>
                                    <a href="<?php echo ROOT_LINK ?>/internal/users/<?php echo get_user_info_from_id($post['author'])['identifier'] ?>/ban">
                                        <button class="lb-button internal-table-button">
                                            <img src="<?php echo STATIC_LINK ?>/img/icons/interface/ban.svg" alt="Ban icon">
                                        </button>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <?php
                        $post_data = json_decode($post['data'], true);
                        $loves = $database->preparedQuery('SELECT count(id) AS loves FROM loves WHERE type = ? AND pid = ?', ['reply', $post['id']])->fetch(PDO::FETCH_ASSOC)['loves'];
                        $loved = $database->preparedQuery('SELECT count(id) AS loved FROM loves WHERE type = ? AND pid = ? AND user = ?', ['post', $post['id'], $yourself['id']])->fetch(PDO::FETCH_ASSOC)['loved'] > 0;
                        $user = get_user_info_from_id($post['author']);
                        echo build_reply($post, $user, $yourself, $loved, $loves, false, false);
                        ?>
                    </div>
                <?php
                }
                ?>
            </div>
            <div class="internal-section">
                <div id="internal-paginator">
                    <div class="internal-paginator-section">
                        <ul id="profile-posts-paginator">
                            <?php
                            $pg = $_GET['page'] ?? 1;
                            if ($pg > 1) : $p = $pg - 1 ?>
                                <li class="profile-posts-paginator-page">
                                    <a href="<?php echo ROOT_LINK ?>/internal/comments/<?php echo '?page=' . $p ?>">
                                        << /a>
                                </li>
                            <?php endif;
                            generate_paginator_3($pg, 1, $user, 'comments');
                            generate_paginator_3($pg, 2, $user, 'comments');
                            generate_paginator_3($pg, 3, $user, 'comments');
                            generate_paginator_4($pg, $paginator_count - 3, $user, 'comments');
                            generate_paginator_4($pg, $paginator_count - 2, $user, 'comments');
                            generate_paginator_4($pg, $paginator_count - 1, $user, 'comments');
                            if ($pg + 1 < $paginator_count) : $p = $pg + 1 ?>
                                <li class="profile-posts-paginator-page internal-paginator">
                                    <a href="<?php echo ROOT_LINK ?>/internal/comments/<?php echo '?page=' . $p ?>">></a>
                                </li>
                            <?php endif ?>
                        </ul>
                    </div>
                    <div class="internal-paginator-section">
                        <p class="internal-paginator-info">Showing reports <?php echo $paginator_page * $paginator + 1 ?> to <?php echo ($paginator_page * $paginator + $paginator) > $data['replies'] ? $data['replies'] : ($paginator_page * $paginator + $paginator) ?> in <?php echo $data['replies'] ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php footer() ?>