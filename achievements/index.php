<?php
head('Badges', 'en', 'badges.css', true);
$badges = $database->preparedQuery("SELECT badge, count(id) AS result FROM badges GROUP BY badge", [])->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);

$items = [
    "Profile", "Invite"
];

$data = [
    "Profile" => [
        "Posts Pundit" => "Post 10 public posts",
        "Postmaster" => "Post 50 public posts",
        "Posts Publisher" => "Post 100 public posts",
        "Just a Bunch of Followers" => "Have 10 followers on your profile",
        "Followers Riser" => "Have 50 followers on your profile",
        "Followers Influencer" => "Have 100 followers on your profile",
        "Your First Love" => "Get your first heart on your public profile posts",
        "Loveable" => "Have 10 hearts on your public posts",
        "Hearts Butterly" => "Have 50 hearts on your public posts",
        "Hearts Robber" => "Have 100 hearts on your public posts",
        "Viral Sensation" => "Have 500 hearts on your public posts",
        "Viewfinder" => "Have 100 views on your public posts",
        "Views Chaser" => "Have 250 views on your public posts",
        "Views Star" => "Have 500 views on your public posts",
        "Views Master" => "Have 1000 views on your public posts",
        "Views Champion" => "Have 5000 views on your public posts",
        "Views Supreme" => "Have 10000 views on your public posts",
    ],
    "Invite" => [
        "Follower" => "Make an user join with your invite link",
        "Invitationist" => "Have 3 users join with your invite link",
        "Popular Inviter" => "Have 6 users join with your invite link",
        "Active Follower" => "Have 1 of the invited users post something",
        "Active Invitationist" => "Have 3 of the invited users post something",
        "Active Inviter" => "Have 6 of the invited users post something",
    ]
];

$data_images = [
    "Profile" => [
        "message-10",
        "message-50",
        "message-100",
        "user-10",
        "user-50",
        "user-100",
        "heart-1",
        "heart-10",
        "heart-50",
        "heart-100",
        "heart-500",
        "view-100",
        "view-250",
        "view-500",
        "view-1000",
        "view-5000",
        "view-10000",
    ],
    "Invite" => [
        "invite-1",
        "invite-3",
        "invite-6",
        "activated-1",
        "activated-3",
        "activated-6",
    ]
]

?>

<div id="content">
    <h1>Achievements </h1>
    <div id="badges-container">
        <?php
        foreach ($items as $value2) {
        ?>
            <h2><?php echo $value2 ?> achievements</h2>
            <ul id="badges-items">
                <?php
                $index = 0;
                foreach ($data[$value2] as $key => $value) {
                ?>
                    <li id="<?php echo $data_images[$value2][$index] ?>" class="badge-item">
                        <div class="badge-item-section">
                            <img src="<?php echo STATIC_LINK ?>/img/icons/achievements/<?php echo $data_images[$value2][$index] ?>.svg" alt="<?php echo $key ?> Badge" title="<?php echo $key ?> Badge" class="badge-item-image">
                            <div class="badge-item-info">
                                <h3 class="badge-item-title"><?php echo $key ?></h3>
                                <p class="badge-item-description"><?php echo $value ?></p>
                            </div>
                        </div>
                        <p class="badge-item-awarded"><?php echo $badges[$data_images[$value2][$index]]["result"] ?? 0 ?> Awarded</p>
                    </li>
                <?php
                    $index += 1;
                }
                ?>
            </ul>
        <?php
        }
        ?>
    </div>
</div>

<?php footer() ?>