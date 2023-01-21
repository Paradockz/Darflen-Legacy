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
$data['users'] = $database->rawQuery('SELECT count(id) AS count FROM users')->fetch(PDO::FETCH_ASSOC)['count'];
$data['bans'] = $database->rawQuery('SELECT count(id) AS result FROM bans')->fetch(PDO::FETCH_ASSOC)['result'];
$data['verified'] = $database->preparedQuery('SELECT count(id) AS count FROM users WHERE JSON_VALUE(data,"$.miscellaneous.email_verified") = ?', [true])->fetch(PDO::FETCH_ASSOC)['count'];
$paginator = 10;
$paginator_count = ceil($data['users'] / $paginator) + 1;
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
$users = $database->preparedQuery('SELECT id,email,data,identifier FROM users LIMIT ?,?', [$paginator * $paginator_page, $paginator])->fetchAll(PDO::FETCH_ASSOC);
head('Website Users', 'en', 'internal.css', true, '', '', 'Darflen', false);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/internal.js" async defer></script>

<div id="content">
    <div id="internal">
        <h1 id="internal-title">Darflen users</h1>
        <div id="internal-contents">
            <div class="internal-section">
                <ul class="internal-micro-stats">
                    <?php
                    internalMicroStat('Users', $data['users'], 'users.svg');
                    internalMicroStat('Verified', $data['verified'], 'user-check.svg');
                    internalMicroStat('Unverified', $data['users'] - $data['verified'], 'user-remove.svg');
                    internalMicroStat('Banned', $data['bans'], 'ban.svg', 'bans');
                    ?>
                </ul>
            </div>
            <div class="internal-section">
                <table id="internal-table">
                    <tr class="internal-table-section">
                        <th class="internal-table-header internal-table-thin-cell">
                            Id
                        </th>
                        <th class="internal-table-header internal-table-wide-cell">
                            Email
                        </th>
                        <th class="internal-table-header">
                            Username
                        </th>
                        <th class="internal-table-header">
                            Creation
                        </th>
                        <th class="internal-table-header">
                            Actions
                        </th>
                    </tr>
                    <?php
                    foreach ($users as $user) {
                        $user_data = json_decode($user['data'], true);
                    ?>
                        <tr class="internal-table-section">
                            <td class="internal-table-item internal-table-thin-cell">
                                <?php echo $user['id'] ?>
                            </td>
                            <td class="internal-table-item internal-table-wide-cell">
                                <?php echo $user['email'] ?>
                            </td>
                            <td class="internal-table-item">
                                <?php echo $user_data['username'] ?>
                            </td>
                            <td class="internal-table-item">
                                <?php echo time_ago($user_data['miscellaneous']['creation_time']) ?>
                            </td>
                            <td class="internal-table-item">
                                <ul class="internal-table-list">
                                    <?php
                                    if ($database->preparedQuery('SELECT count(id) AS result FROM bans WHERE user = ?', [$user['identifier']])->fetch(PDO::FETCH_ASSOC)['result'] < 1) {
                                    ?>
                                        <li>
                                            <a href="<?php echo ROOT_LINK ?>/internal/users/<?php echo $user['identifier'] ?>/ban">
                                                <button class="lb-button internal-table-button">
                                                    <img src="<?php echo STATIC_LINK ?>/img/icons/interface/ban.svg" alt="Ban icon">
                                                </button>
                                            </a>
                                        </li>
                                    <?php
                                    } else {
                                    ?>
                                        <li>
                                            <button class="lb-button internal-table-button" onclick="unban('<?php echo $user['identifier'] ?>');return false">
                                                <img src="<?php echo STATIC_LINK ?>/img/icons/interface/check.svg" alt="User remove icon">
                                            </button>
                                        </li>
                                    <?php
                                    }
                                    ?>
                                    <li>
                                        <a href="<?php echo ROOT_LINK ?>/internal/users/<?php echo $user['identifier'] ?>/edit">
                                            <button class="lb-button internal-table-button">
                                                <img src="<?php echo STATIC_LINK ?>/img/icons/interface/pen.svg" alt="Edit icon">
                                            </button>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo ROOT_LINK ?>/users/<?php echo $user['identifier'] ?>">
                                            <button class="lb-button internal-table-button">
                                                <img src="<?php echo STATIC_LINK ?>/img/icons/interface/user.svg" alt="User icon">
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
                                    <a href="<?php echo ROOT_LINK ?>/internal/users/<?php echo '?page=' . $p ?>">
                                        <</a>
                                </li>
                            <?php endif;
                            generate_paginator_3($pg, 1, $user);
                            generate_paginator_3($pg, 2, $user);
                            generate_paginator_3($pg, 3, $user);
                            generate_paginator_4($pg, $paginator_count - 3, $user);
                            generate_paginator_4($pg, $paginator_count - 2, $user);
                            generate_paginator_4($pg, $paginator_count - 1, $user);
                            if ($pg + 1 < $paginator_count) : $p = $pg + 1 ?>
                                <li class="profile-posts-paginator-page internal-paginator">
                                    <a href="<?php echo ROOT_LINK ?>/internal/users/<?php echo '?page=' . $p ?>">></a>
                                </li>
                            <?php endif ?>
                        </ul>
                    </div>
                    <div class="internal-paginator-section">
                        <p class="internal-paginator-info">Showing users <?php echo $paginator_page * $paginator + 1 ?> to <?php echo ($paginator_page * $paginator + $paginator) > $data['users'] ? $data['users'] : ($paginator_page * $paginator + $paginator) ?> in <?php echo $data['users'] ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php footer() ?>