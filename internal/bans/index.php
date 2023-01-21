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
$data['total'] = $database->rawQuery('SELECT count(id) AS count FROM bans')->fetch(PDO::FETCH_ASSOC)['count'];
$paginator = 10;
$paginator_count = ceil($data['total'] / $paginator) + 1;
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
$bans = $database->preparedQuery('SELECT id,user,time,data FROM bans LIMIT ?,?', [$paginator * $paginator_page, $paginator])->fetchAll(PDO::FETCH_ASSOC);
head('Website Bans', 'en', 'internal.css', true, '', '', 'Darflen', false);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/internal.js" async defer></script>

<div id="content">
    <div id="internal">
        <h1 id="internal-title">Darflen bans</h1>
        <div id="internal-contents">
            <div class="internal-section">
                <ul class="internal-micro-stats">
                    <?php
                    internalMicroStat('Bans', $data['total'], 'ban.svg');
                    ?>
                </ul>
            </div>
            <div class="internal-section">
                <table id="internal-table">
                    <tr class="internal-table-section">
                        <th class="internal-table-header internal-table-thin-cell">
                            Id
                        </th>
                        <th class="internal-table-header">
                            User
                        </th>
                        <th class="internal-table-header">
                            Time
                        </th>
                        <th class="internal-table-header">
                            Reason
                        </th>
                        <th class="internal-table-header">
                            Moderator
                        </th>
                        <th class="internal-table-header">
                            Actions
                        </th>
                    </tr>
                    <?php
                    foreach ($bans as $ban) {
                        $ban_data = json_decode($ban['data'], true);
                        $user = get_user_info_from_identifier($ban['user']);
                        $moderator = get_user_info_from_identifier($ban_data['miscellaneous']['moderator']);
                    ?>
                        <tr class="internal-table-section">
                            <td class="internal-table-item internal-table-thin-cell">
                                <?php echo $ban['id'] ?>
                            </td>
                            <td class="internal-table-item">
                                <?php echo json_decode($user['data'], true)['username'] ?>
                            </td>
                            <td class="internal-table-item">
                                <?php echo date('d/m/y h:i:s', $ban['time']) ?>
                            </td>
                            <td class="internal-table-item">
                                <?php echo $ban_data['miscellaneous']['reason'] ?>
                            </td>
                            <td class="internal-table-item">
                                <?php echo json_decode($moderator['data'], true)['username'] ?>
                            </td>
                            <td class="internal-table-item">
                                <ul class="internal-table-list">
                                    <li>
                                        <button class="lb-button internal-table-button" onclick="unban('<?php echo $user['identifier'] ?>');return false">
                                            <img src="<?php echo STATIC_LINK ?>/img/icons/interface/check.svg" alt="Check icon">
                                        </button>
                                    </li>
                                    <li>
                                        <a href="<?php echo ROOT_LINK ?>/users/<?php echo $ban_data['miscellaneous']['moderator'] ?>">
                                            <button class="lb-button internal-table-button">
                                                <img src="<?php echo STATIC_LINK ?>/img/icons/interface/user-add.svg" title="Moderator user profile" alt="User add icon">
                                            </button>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo ROOT_LINK ?>/users/<?php echo $user['identifier'] ?>">
                                            <button class="lb-button internal-table-button">
                                                <img src="<?php echo STATIC_LINK ?>/img/icons/interface/user-remove.svg" title="Banned user profile" alt="User remove icon">
                                            </button>
                                        </a>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </table>
            </div>
            <div class="internal-section">
                <div id="internal-paginator">
                    <div class="internal-paginator-section">
                        <ul id="profile-posts-paginator">
                            <?php
                            $pg = $_GET['page'] ?? 1;
                            if ($pg > 1) : $p = $pg - 1 ?>
                                <li class="profile-posts-paginator-page">
                                    <a href="<?php echo ROOT_LINK ?>/internal/reports/<?php echo '?page=' . $p ?>">
                                        << /a>
                                </li>
                            <?php endif;
                            generate_paginator_3($pg, 1, $user, 'reports');
                            generate_paginator_3($pg, 2, $user, 'reports');
                            generate_paginator_3($pg, 3, $user, 'reports');
                            generate_paginator_4($pg, $paginator_count - 3, $user, 'reports');
                            generate_paginator_4($pg, $paginator_count - 2, $user, 'reports');
                            generate_paginator_4($pg, $paginator_count - 1, $user, 'reports');
                            if ($pg + 1 < $paginator_count) : $p = $pg + 1 ?>
                                <li class="profile-posts-paginator-page internal-paginator">
                                    <a href="<?php echo ROOT_LINK ?>/internal/reports/<?php echo '?page=' . $p ?>">></a>
                                </li>
                            <?php endif ?>
                        </ul>
                    </div>
                    <div class="internal-paginator-section">
                        <p class="internal-paginator-info">Showing reports <?php echo $paginator_page * $paginator + 1 ?> to <?php echo ($paginator_page * $paginator + $paginator) > $data['total'] ? $data['total'] : ($paginator_page * $paginator + $paginator) ?> in <?php echo $data['total'] ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php footer() ?>