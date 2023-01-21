<?php
redirect_if_not_logged(ROOT_LINK);
$database = prepare_database();
$token = isset($_COOKIE['token']) ? $_COOKIE['token'] : 0;
if (isset($_COOKIE['token']) && check_token_validity($_COOKIE['token'])) {
    $yourself = get_user_info_from_token($_COOKIE['token']);
}
$yourself['id'] = isset($yourself['id']) ? $yourself['id'] : 0;
if (!check_user_ban($yourself['identifier'])) {
    header('Location: '.ROOT_LINK.'/users/'.$yourself['identifier']);
}
$ban = $database->preparedQuery('SELECT user, time, data FROM bans WHERE user = ?',[$yourself['identifier']])->fetch(PDO::FETCH_ASSOC);
head('Account banned', 'en', 'bans.css', true);
?>

<div id="content">
    <div id="bans">
        <h1>Account banned</h1>
        Your account has been banned. You will be unbanned at <?php echo sprintf('%s (%s). Reason: %s',date('l d F Y',$ban['time']),date('d/m/y',$ban['time']),json_decode($ban['data'],true)['miscellaneous']['reason']); ?>
    </div>
</div>