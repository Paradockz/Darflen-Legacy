<?php head('Bad Request! 400', 'en', 'errors.css', true, '', '', 'Darflen', false); ?>

<div id="content">
    <div id="errors">
        <div class="errors-section">
            <span class="errors-big-title">400</span>
            <h1>Bad Request</h1>
            <p>Look like the server was unable to handle the browser request.</p>
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