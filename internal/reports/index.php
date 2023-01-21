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
$data['total'] = $database->rawQuery('SELECT count(id) AS count FROM reports')->fetch(PDO::FETCH_ASSOC)['count'];
$data['posts'] = $database->preparedQuery('SELECT count(id) AS count, type FROM reports WHERE type = ?', ['post'])->fetch(PDO::FETCH_ASSOC);
$data['comments'] = $database->preparedQuery('SELECT count(id) AS count, type FROM reports WHERE type = ?', ['comment'])->fetch(PDO::FETCH_ASSOC);
$data['replies'] = $database->preparedQuery('SELECT count(id) AS count, type FROM reports WHERE type = ?', ['reply'])->fetch(PDO::FETCH_ASSOC);
$data['users'] = $database->preparedQuery('SELECT count(id) AS count, type FROM reports WHERE type = ?', ['user'])->fetch(PDO::FETCH_ASSOC);

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
$reports = $database->preparedQuery('SELECT id,type,pid,reason,description,data FROM reports LIMIT ?,?', [$paginator * $paginator_page, $paginator])->fetchAll(PDO::FETCH_ASSOC);
for ($index = 0; $index  <= 3; $index ++) { 
   if(!isset($data['type'][$index]['count'])) {
        $data['type'][$index]['count'] = 0;
   }
}
head('Website Reports', 'en', 'internal.css', true, '', '', 'Darflen', false);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/explore.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/posts.js" async defer></script>

<div id="content">
    <div id="internal">
        <h1 id="internal-title">Darflen reports</h1>
        <div id="internal-contents">
            <div class="internal-section">
                <ul class="internal-micro-stats">
                    <?php
                    internalMicroStat('Reports', $data['total'], 'flag.svg');
                    internalMicroStat('Post', $data['posts']['count'], 'message.svg');
                    internalMicroStat('Comment', $data['comments']['count'], 'message-alt.svg');
                    internalMicroStat('Reply', $data['replies']['count'], 'message-alt.svg');
                    internalMicroStat('User', $data['users']['count'], 'users.svg');
                    ?>
                </ul>
            </div>
            <div class="internal-section">
                <table id="internal-table">
                    <tr class="internal-table-section">
                        <th class="internal-table-header internal-table-thin-cell">
                            Id
                        </th>
                        <th class="internal-table-header internal-table-thin-cell">
                            Type
                        </th>
                        <th class="internal-table-header internal-table-thin-cell">
                            Reason
                        </th>
                        <th class="internal-table-header">
                            Reported
                        </th>
                        <th class="internal-table-header internal-table-wide-cell">
                            Description
                        </th>
                        <th class="internal-table-header">
                            Actions
                        </th>
                    </tr>
                    <?php
                    foreach ($reports as $report) {
                        $report_data = json_decode($report['data'], true);
                    ?>
                        <tr class="internal-table-section">
                            <td class="internal-table-item internal-table-thin-cell">
                                <?php echo $report['id'] ?>
                            </td>
                            <td class="internal-table-item internal-table-thin-cell">
                                <?php echo $report['type'] ?>
                            </td>
                            <td class="internal-table-item internal-table-thin-cell">
                                <?php echo $report['reason'] ?>
                            </td>
                            <td class="internal-table-item">
                                <?php echo time_ago($report_data['miscellaneous']['creation_time']) ?>
                            </td>
                            <td class="internal-table-item internal-table-wide-cell">
                                <?php echo $report['description'] ?>
                            </td>
                            <td class="internal-table-item">
                                <ul class="internal-table-list">
                                    <?php
                                    switch ($report['type']) {
                                        case 'user':
                                            $author = $report['pid'];
                                            break;
                                        case 'post':
                                            $author = $database->preparedQuery('SELECT author FROM posts WHERE id = ?', [$report['pid']])->fetch(PDO::FETCH_ASSOC)['author'];
                                            $author = get_user_info_from_id($author)['identifier'];
                                            break;
                                        case 'reply':
                                            $author = $database->preparedQuery('SELECT author FROM subreplies WHERE id = ?', [$report['pid']])->fetch(PDO::FETCH_ASSOC)['author'];
                                            $author = get_user_info_from_id($author)['identifier'];
                                            break;
                                        default:
                                            $author = $database->preparedQuery('SELECT author FROM replies WHERE id = ?', [$report['pid']])->fetch(PDO::FETCH_ASSOC)['author'];
                                            $author = get_user_info_from_id($author)['identifier'];
                                            $reply = $database->preparedQuery('SELECT post FROM replies WHERE id = ?', [$report['pid']])->fetch(PDO::FETCH_ASSOC)['post'];
                                            break;
                                    }
                                    ?>
                                    <li>
                                        <a href="<?php echo ROOT_LINK ?>/users/<?php echo get_user_info_from_id($report_data['reporter'])['identifier'] ?>">
                                            <button class="lb-button internal-table-button">
                                                <img src="<?php echo STATIC_LINK ?>/img/icons/interface/user-add.svg" title="Reporter user profile" alt="User add icon">
                                            </button>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo ROOT_LINK ?>/users/<?php echo $author ?>">
                                            <button class="lb-button internal-table-button">
                                                <img src="<?php echo STATIC_LINK ?>/img/icons/interface/user-remove.svg" title="Reported user profile" alt="User remove icon">
                                            </button>
                                        </a>
                                    </li>
                                    <?php if ($report['type'] == 'post') : ?>
                                        <li>
                                            <a href="<?php echo ROOT_LINK ?>/posts/<?php echo $report['pid'] ?>">
                                                <button class="lb-button internal-table-button">
                                                    <img src="<?php echo STATIC_LINK ?>/img/icons/interface/message.svg" alt="Comment icon">
                                                </button>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($report['type'] == 'user') : ?>
                                        <li>
                                            <a href="<?php echo ROOT_LINK ?>/internal/users/<?php echo $report['pid'] ?>/ban">
                                                <button class="lb-button internal-table-button">
                                                    <img src="<?php echo STATIC_LINK ?>/img/icons/interface/ban.svg" alt="Ban icon">
                                                </button>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($report['type'] == 'reply' || $report['type'] == 'comment') : ?>
                                        <li>
                                            <a href="<?php echo ROOT_LINK ?>/posts/<?php echo $reply ?>#<?php echo $report['pid'] ?>">
                                                <button class="lb-button internal-table-button">
                                                    <img src="<?php echo STATIC_LINK ?>/img/icons/interface/message-alt.svg" alt="Comment icon">
                                                </button>
                                            </a>
                                        </li>
                                    <?php endif; ?>
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
                                        <</a>
                                </li>
                            <?php endif;
                            generate_paginator_3($pg, 1, $user, 'reports');
                            generate_paginator_3($pg, 2,$user, 'reports');
                            generate_paginator_3($pg, 3,$user, 'reports');
                            generate_paginator_4($pg, $paginator_count - 3,$user, 'reports');
                            generate_paginator_4($pg, $paginator_count - 2,$user, 'reports');
                            generate_paginator_4($pg, $paginator_count - 1,$user, 'reports');
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