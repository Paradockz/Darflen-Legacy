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
$things = ['users' => 'users', 'bans' => 'ban', 'loves' => 'heart', 'posts' => 'message', 'reports' => 'warning'];
foreach ($things as $key => $value) {
    $data[$key] = $database->rawQuery('SELECT COUNT(id) AS count FROM ' . $key)->fetch(PDO::FETCH_ASSOC)['count'];
    $images[$key] = sprintf('%s.svg', $value);
}
head('Internal', 'en', 'internal.css', true, '', '', WEBSITE, false);
?>

<script src="<?php echo ROOT_LINK ?>/includes/js/explore.js" async defer></script>
<script src="<?php echo ROOT_LINK ?>/includes/js/posts.js" async defer></script>

<div id="content">
    <div id="internal">
        <h1 id="internal-title">Internal page</h1>
        <div class="internal-section">
            <ul class="internal-micro-stats">
                <?php
                foreach ($data as $key => $value) {
                    internalMicroStat(ucfirst($key), $data[$key], $images[$key], $key == 'loves' ? '' : $key);
                }
                ?>
            </ul>
        </div>
        <div class="internal-section">
            <a href="<?php echo ROOT_LINK ?>/internal/deprecated_index.php">Go to old admin panel</a>
            <a href="<?php echo ROOT_LINK ?>/errors/404.php">404 Page</a>
            <a href="<?php echo ROOT_LINK ?>/errors/offline.php">Offline Page</a>
            <a href="<?php echo ROOT_LINK ?>/matomo/">Analytics</a>
        </div>
    </div>
</div>

<?php footer() ?>