<?php
$pretitle = $title;
$title = htmlspecialchars(empty($title) ? 'Darflen' : $title . ' | ' . 'Darflen', ENT_QUOTES);
$description = !empty($description) ? htmlspecialchars($description, ENT_QUOTES) : htmlspecialchars('Darflen, your social media for sharing and connecting with your friends and people you know worldwide.', ENT_QUOTES);
$icon = !empty($icon) ? $icon : STATIC_LINK . '/img/favicons/apple-touch-icon-180x180.png';
$link = $_SERVER["REQUEST_URI"];
$type = !empty($type) ? $type : 'summary';
$author = isset($author) ? $author : 'Darflen';
?>


<!DOCTYPE html>
<html lang='<?php echo $lang; ?>' theme="<?php
                                            $token = isset($_COOKIE['token']) ? $_COOKIE['token'] : 0;
                                            if (check_token_validity($token)) {
                                                $user = get_user_info_from_token($token);
                                                $data = json_decode($user['data'], true);
                                                if (!isset($data['miscellaneous']['theme'])) {
                                                    $database = prepare_database();
                                                    $data['miscellaneous']['theme'] = 'light';
                                                    $data = json_encode($data);
                                                    $database->preparedQuery('UPDATE users SET data = ? WHERE id = ?', [$data, $user['id']]);
                                                }
                                                echo $data['miscellaneous']['theme'];
                                            } else {
                                                echo 'light';
                                            }
                                            ?>">

<head>
    <title><?php echo $title; ?></title>
    <meta name='description' content='<?php echo $description; ?>'>
    <meta name='keywords' content='darflen, sharing, social, social media, posts, media'>
    <meta name='author' content='<?php echo $author; ?>'>
    <meta property='og:title' content='<?php echo $title; ?>'>
    <meta property='og:description' content='<?php echo $description; ?>'>
    <meta property='og:site_name' content='<?php echo $author; ?>'>
    <meta property='og:url' content='<?php echo ROOT_LINK ?><?php echo $link; ?>'>
    <meta property='og:type' content='<?php echo $ogtype; ?>'>
    <?php if ($icon != ' ') : ?>
        <meta property='og:<?php echo $ogtype == 'video' ? 'video.other' : 'image' ?>' content='<?php echo $icon; ?>'>
        <?php if ($ogtype = 'video') : ?>
            <meta property='og:video:url' content='<?php echo $icon; ?>'>
            <meta property='og:video:secure_url' content='<?php echo $icon; ?>'>
            <meta property='og:video:type' content='video/mp4'>
            <meta property='og:video:width' content='1920'>
            <meta property='og:video:height' content='1080'>
            <meta property='og:image' content='<?php echo explode('.mp4', $icon)[0] . 't.jpg' ?>'>
        <?php endif; ?>
    <?php endif; ?>
    <meta property='og:locale' content='<?php echo $lang; ?>'>
    <meta name='twitter:card' content='<?php echo $type ?>'>
    <meta name='twitter:title' content='<?php echo $title; ?>'>
    <meta name='twitter:description' content='<?php echo $description; ?>'>
    <?php if ($icon != ' ') : ?>
        <meta name='twitter:<?php echo $ogtype == 'video' ? 'video.other' : 'image' ?>' content='<?php echo $icon; ?>'>
        <?php if ($ogtype = 'video') : ?>
            <meta name='twitter:image' content='<?php echo explode('.mp4', $icon)[0] . 't.jpg' ?>'>
            <meta name='twitter:video:type' content='video/mp4'>
            <meta name='twitter:video:width' content='1920'>
            <meta name='twitter:video:height' content='1080'>
        <?php endif; ?>
    <?php endif; ?>
    <meta name='twitter:site' content='@SociallyDarflen'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <?php
    if ($index) {
        echo '<meta name="robots" content "index, follow">';
    } else {
        echo '<meta name="robots" content "noindex, nofollow">';
    }
    ?>
    <link rel='preconnect' href='https://fonts.googleapis.com'>
    <link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
    <style>
        <?php
        include_once(DOCUMENT_ROOT . '\static\css\resets.css');
        include_once(DOCUMENT_ROOT . '\static\css\themes.css');
        ?>
    </style>
    <link rel='stylesheet' href='<?php echo STATIC_LINK ?>/css/styles.css'>
    <?php
    if (!empty($css)) {
        echo "<link rel='stylesheet' href='" . STATIC_LINK . '/css/pages/' . $css . "'>";
    }
    ?>
    <link rel='apple-touch-icon' sizes='180x180' href='<?php echo STATIC_LINK ?>/img/favicons/apple-touch-icon.png'>
    <link rel='icon' type='image/png' sizes='32x32' href='<?php echo STATIC_LINK ?>/img/favicons/favicon-32x32.png'>
    <link rel='icon' type='image/png' sizes='16x16' href='<?php echo STATIC_LINK ?>/img/favicons/favicon-16x16.png'>
    <link rel='manifest' href='<?php echo STATIC_LINK ?>/site.webmanifest'>
    <link rel='mask-icon' href='<?php echo STATIC_LINK ?>/img/favicons/safari-pinned-tab.svg' color='#f1284d'>
    <link rel='shortcut icon' href='<?php echo STATIC_LINK ?>/img/favicons/favicon.ico' type='image/x-icon'>
    <meta name='msapplication-TileColor' content='#f1284d'>
    <meta name='msapplication-TileImage' content='<?php echo STATIC_LINK ?>/img/favicons/mstile-144x144.png'>
    <meta name='msapplication-config' content='<?php echo STATIC_LINK ?>/browserconfig.xml'>
    <meta name='theme-color' content='#f1284d'>
    <script src="<?php echo ROOT_LINK ?>/includes/js/main.js" async defer></script>
</head>

<body>

    <div id="website">