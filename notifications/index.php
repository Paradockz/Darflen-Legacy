<?php
redirect_if_not_logged(ROOT_LINK);
session_start();
$_SESSION['offset'] = 0;
head('Notifications', 'en', 'notifications.css',true, '', '', WEBSITE, false);
$database = prepare_database();
$yourself = [];
if (isset($_COOKIE['token']) && check_token_validity($_COOKIE['token'])) {
    $yourself = get_user_info_from_token($_COOKIE['token']);
}
$yourself['id'] = isset($yourself['id']) ? $yourself['id'] : 0;
$unread = $database->preparedQuery('SELECT count(id) AS result FROM notifications WHERE JSON_VALUE(data,"$.miscellaneous.read") = ? AND user = ?', [0,$yourself['id']])->fetch(PDO::FETCH_ASSOC)['result'];
$database->preparedQuery('UPDATE notifications SET data = JSON_SET(data,"$.miscellaneous.read",?) WHERE user = ?',[true,$yourself['id']]);
$notifications = $database->preparedQuery('SELECT user,priority,data FROM notifications WHERE user = ? ORDER BY priority DESC, id DESC LIMIT ?,?', [$yourself['id'], $_SESSION['offset'], 15])->fetchAll(PDO::FETCH_ASSOC);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/notifications.js" async defer></script>

<div id="content">
    <div id="user-notifications-container">
        <h1>
            Notifications
            <?php
                echo $unread > 0 ? '('.$unread.')' : '';
            ?>
        </h1>
        <div id="user-notifications">
            <?php
            foreach ($notifications as $notification) {
                $data = json_decode($notification['data'], true);
                echo notification($data['icon'], $data['html'], $data['miscellaneous']['creation_time']);
            }
            ?>
        </div>
        <?php if (count($notifications) > 9) { ?>
            <button class="lb-button" id="notifications-load-more" onclick="load_more(this,<?php echo $_SESSION['offset'] ?>)">See more notifications</button>
        <?php } elseif (count($notifications) < 1) { ?>
            <p id="empty-explore">Nothing found.</p>
        <?php } ?>
    </div>
</div>

<?php footer() ?>