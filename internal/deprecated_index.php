<?php
    redirect_if_not_logged('https://www.darflen.com');
    $token = $_COOKIE['token'];
    $database = prepare_database();
    $user = get_user_info_from_token($token);
    if (!json_decode($user['data'],true)['miscellaneous']['administrator']) {
        http_response_code(404);
        include_once(DOCUMENT_ROOT . '/errors/404.php');
        exit;
    }
    head('Admin', 'en', 'internal.css', true,'','','Darflen',false); 
    $data = $database->preparedQuery('SELECT COUNT(id) AS count, id, JSON_VALUE(data,"$.ip") AS ip, JSON_VALUE(data,"$.agent") AS agent, JSON_VALUE(data,"$.miscellaneous.country") AS country, JSON_VALUE(data,"$.miscellaneous.creation_time") AS creation FROM access GROUP BY ip, agent ORDER BY count DESC',[])->fetchAll(PDO::FETCH_ASSOC);

    function load($item) {
        echo sprintf('<p style="line-height: 1.66;">'.'Count: %d | IP: %s | Agent: %s | Country: %s | Time: %s',$item['count'],$item['ip'],$item['agent'],$item['country'],time_ago($item['creation'])).'</p>';
    }
?>

<div id="content">
    <h1>Your beautiful admin page</h1>
    <?php
    foreach ($data as $item) {
        load($item);
    }
    ?>
</div>