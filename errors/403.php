<?php head('Forbidden! 403', 'en', 'errors.css',true, '', '', 'Darflen', false); ?>

<div id="content">
    <div id="errors">
        <div class="errors-section">
            <span class="errors-big-title">403</span>
            <h1>Access Forbidden</h1>
            <p>Look like you do not have the permissions to access this page.</p>
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