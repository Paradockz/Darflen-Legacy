<?php
if (empty($_GET['u']) || !$user = get_user_info_from_identifier($_GET['u'])) {
    http_response_code(404);
    include_once(DOCUMENT_ROOT . '/errors/404.php');
    exit;
}
$id = $user['id'];
$badges = $database->preparedQuery("SELECT badge FROM badges WHERE account = ?", [$id])->fetchAll(PDO::FETCH_ASSOC);
head('Badges', 'en', 'profile.css', true);
?>

<div id="content">
    <h1><?php echo json_decode($user['data'], true)['username'] ?>'s Achievements</h1>
    <ul id="badges-container-bigger">
        <?php foreach ($badges as $key => $value) : ?>
            <li class="badge-content">
                <a href="<?php echo ROOT_LINK ?>/achievements#<?php echo $value["badge"] ?>">
                    <img src="<?php echo STATIC_LINK ?>/img/icons/achievements/<?php echo $value["badge"] ?>.svg" alt="Badge image" class="badge-content-image">
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php footer() ?>