<?php
if (empty($_GET['u']) || !$user = get_user_info_from_identifier($_GET['u'])) {
    http_response_code(404);
    include_once(DOCUMENT_ROOT . '/errors/404.php');
    exit;
}
$id = $user['id'];
head(json_decode($user['data'], true)['username'] . '\'s Followers', 'en', 'profile.css', true);
$database = prepare_database();
$total_followers = count($database->preparedQuery('SELECT UNIQUE count(user) FROM loves WHERE profile = ?', [$id])->fetchAll(PDO::FETCH_ASSOC));
$paginator = 36;
$paginator_count = ceil($total_followers / $paginator) + 1;if (isset($_GET['page'])) {
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
$followers = $database->preparedQuery('SELECT UNIQUE user FROM loves WHERE profile = ? LIMIT ?,?', [$id, $paginator * $paginator_page, $paginator])->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="content">
    <div id="profile-followers">
        <div class="profile-followers-section">
            <h1><?php echo json_decode($user['data'], true)['username'] ?>'s Hearts</h1>
            <ul id="stats-accounts-grid">
                <?php
                foreach ($followers as $follower) {
                ?>
                    <li class="stats-account"><?php userCard($follower['user']) ?></li>
                <?php
                }
                ?>
            </ul>
            <?php
            if ($total_followers < 1) {
            ?>
                <p id="empty-stats"><?php echo json_decode($user['data'], true)['username'] ?> doesn't have anyone heart their posts yet! Come back later to see if they've got some hearts.</p>
            <?php
            }
            ?>
        </div>
        <div class="profile-followers-section">
            <ul id="profile-posts-paginator">
                <?php
                $pg = $_GET['page'] ?? 1; if ($pg > 1): $p = $pg - 1 ?>
                     <li class="profile-posts-paginator-page">
                        <a href="<?php echo ROOT_LINK ?>/users/<?php  echo $user['identifier'] ?>/hearts<?php echo '?page='.$p ?>"><</a>
                    </li>
                <?php endif;
                generate_paginator_1($pg,1,$user);
                generate_paginator_1($pg,2,$user);
                generate_paginator_1($pg,3,$user);
                generate_paginator_2($pg,$paginator_count-3,$user);
                generate_paginator_2($pg,$paginator_count-2,$user);
                generate_paginator_2($pg,$paginator_count-1,$user);
                if ($pg+1 < $paginator_count): $p = $pg + 1 ?>
                     <li class="profile-posts-paginator-page">
                        <a href="<?php echo ROOT_LINK ?>/users/<?php  echo $user['identifier'] ?>/hearts<?php echo '?page='.$p ?>">></a>
                    </li>
                <?php endif ?>
            </ul>
        </div>
    </div>
</div>

<?php footer() ?>