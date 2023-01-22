<?php
head('Whoops! 404', 'en', 'errors.css',true, '', '', WEBSITE, false);
?>

<div id="content">
    <div id="errors">
        <div class="errors-section">
            <span class="errors-big-title">404</span>
            <h1>Page Not Found</h1>
            <p>Look like this page does not exist or is no longer available.</p>
        </div>
        <div class="errors-section">
            <form class="nav-search" action="<?php echo ROOT_LINK ?>/explore/search" method="GET" autocomplete="off" autocapitalize="off">
                <input type="search" name="s" placeholder="Search" value="<?php echo htmlspecialchars(isset($_GET['s']) ? $_GET['s'] : '', ENT_QUOTES) ?>" minlength="1">
                <button type="submit" tabindex="-1">
                    <img src="<?php echo STATIC_LINK ?>/img/icons/interface/search.svg" alt="Search submit icon">
                </button>
            </form>
        </div>
    </div>
</div>

<?php footer() ?>