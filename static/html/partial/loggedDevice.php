<?php
$data = json_decode($data, true);
if (isset($data['agent'])) {
    $agent = get_browser($data['agent'], true);
    $title = $agent['platform'] . ' - ' . $agent['parent'];
    $description = time_ago($data['miscellaneous']['creation_time']);
    switch ($agent['device_type']) {
        case 'Mobile Phone':
            $icon = 'mobile.svg';
            break;
        case 'Tablet':
            $icon = 'tablet.svg';
            break;
        default:
            $icon = 'computer.svg';
            break;
    }
    $device_type = $agent['device_type'];
} else {
    $title = 'Unknown';
    $description = 'Unknown';
}
?>


<<?php echo $tag ?> class="settings-logged-device">
    <div class="settings-logged-device-content">
        <img class="settings-logged-device-icon" src="<?php echo STATIC_LINK ?>/img/icons/interface/<?php echo $icon ?>" alt="<?php echo $device_type ?>" title="<?php echo $device_type ?> Icon">
        <div class="settings-logged-device-text">
            <span class="settings-logged-device-title"><?php echo $title ?></span>
            <span class="settings-logged-device-description"><?php echo $description ?></span>
        </div>
    </div>
    <?php if($_COOKIE['token'] != $token): ?>
    <button onclick="logout_device(this,'<?php echo $token ?>')" class="lb-button settings-logged-remove">×</button>
    <?php endif; ?>
</<?php echo $tag ?>>