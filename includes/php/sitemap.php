<?xml version="1.0" encoding="UTF-8"?>

<?php 
    header('Content-type: text/xml');
    $databsae = prepare_database();

    $users_priority = 0.75;
    $users_things_priority = 0.51;
    $hashtags_priority = 0.65;
    $posts_priority = 0.81;
    $posts_priority_pages = 0.65;
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<url>
<loc>https://darflen.com</loc>
<lastmod>2023-11-11T11:37:00+00:00</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>
<url>
<loc>https://darflen.com/terms</loc>
<lastmod>2023-11-11T11:37:00+00:00</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>
<url>
<loc>https://darflen.com/privacy</loc>
<lastmod>2023-11-11T11:37:00+00:00</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>
<url>
<loc>https://darflen.com/login</loc>
<lastmod>2023-11-11T11:37:00+00:00</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>
<url>
<loc>https://darflen.com/register</loc>
<lastmod>2023-11-11T11:37:00+00:00</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>
<url>
<loc>https://darflen.com/explore</loc>
<lastmod>2023-11-11T11:37:00+00:00</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>
<url>
<loc>https://darflen.com/hashtags</loc>
<lastmod>2023-11-11T11:37:00+00:00</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>
<url>
<loc>https://darflen.com/contact</loc>
<lastmod>2023-11-11T11:37:00+00:00</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>
<url>
<loc>https://darflen.com/achievements</loc>
<lastmod>2023-11-11T11:37:00+00:00</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>

<?php
$users = $databsae->preparedQuery("SELECT id, identifier FROM users", [])->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
        $total_posts = get_user_total_posts($user["id"]);
        $paginator = 10;
        $paginator_count = ceil($total_posts / $paginator) + 1;
    ?>
    <url>
    <loc>https://darflen.com/users/<?php echo $user["identifier"] ?></loc>
    <lastmod>2023-11-11T11:37:00+00:00</lastmod>
    <changefreq>daily</changefreq>
    <priority><?php echo $users_priority ?></priority>
    </url>
    <url>
    <loc>https://darflen.com/users/<?php echo $user["identifier"] ?>/achievements</loc>
    <lastmod>2023-11-11T11:37:00+00:00</lastmod>
    <changefreq>daily</changefreq>
    <priority><?php echo $users_things_priority ?></priority>
    </url>
    <url>
    <loc>https://darflen.com/users/<?php echo $user["identifier"] ?>/hearts</loc>
    <lastmod>2023-11-11T11:37:00+00:00</lastmod>
    <changefreq>daily</changefreq>
    <priority><?php echo $users_things_priority ?></priority>
    </url>
    <url>
    <loc>https://darflen.com/users/<?php echo $user["identifier"] ?>/followers</loc>
    <lastmod>2023-11-11T11:37:00+00:00</lastmod>
    <changefreq>daily</changefreq>
    <priority><?php echo $users_things_priority ?></priority>
    </url>
    <url>
    <loc>https://darflen.com/users/<?php echo $user["identifier"] ?>/following</loc>
    <lastmod>2023-11-11T11:37:00+00:00</lastmod>
    <changefreq>daily</changefreq>
    <priority><?php echo $users_things_priority ?></priority>
    </url>
    <url>
    <loc>https://darflen.com/posts/<?php echo $user["identifier"] ?>/user</loc>
    <lastmod>2023-11-11T11:37:00+00:00</lastmod>
    <changefreq>daily</changefreq>
    <priority><?php echo $posts_priority ?></priority>
    </url>
    <?php
    for ($index = 1; $index < $paginator_count; $index++) { 
        ?>
        <url>
        <loc>https://darflen.com/posts/<?php echo $user["identifier"] ?>/user?page=<?php echo $index ?></loc>
        <lastmod>2023-11-11T11:37:00+00:00</lastmod>
        <changefreq>daily</changefreq>
        <priority><?php echo $posts_priority_pages ?></priority>
        </url>
        <?php
    }
}
$posts = $databsae->preparedQuery("SELECT id FROM posts", [])->fetchAll(PDO::FETCH_ASSOC);
foreach ($posts as $post) {
    ?>
    <url>
    <loc>https://darflen.com/posts/<?php echo $post["id"] ?></loc>
    <lastmod>2023-11-11T11:37:00+00:00</lastmod>
    <changefreq>daily</changefreq>
    <priority><?php echo $posts_priority ?></priority>
    </url>
    <?php
}
$total = count($database->rawQuery('SELECT count(hashtag) as result FROM hashtags  GROUP BY hashtag')->fetchAll(PDO::FETCH_ASSOC));
$paginator = 24;
$paginator_count = ceil($total / $paginator) + 1;
for ($index = 1; $index < $paginator_count; $index++) { 
    ?>
    <url>
    <loc>https://darflen.com/hashtags/?page=<?php echo $index ?></loc>
    <lastmod>2023-11-11T11:37:00+00:00</lastmod>
    <changefreq>daily</changefreq>
    <priority><?php echo $posts_priority_pages ?></priority>
    </url>
    <?php
}
$hashtags = recommend_hashtags("hashtags");
foreach ($hashtags as $hashtag) {
    ?>
    <url>
    <loc>https://darflen.com/hashtags/<?php echo $hashtag["hashtag"] ?></loc>
    <lastmod>2023-11-11T11:37:00+00:00</lastmod>
    <changefreq>daily</changefreq>
    <priority><?php echo $posts_priority_pages ?></priority>
    </url>
    <?php
}
?>



</urlset>
