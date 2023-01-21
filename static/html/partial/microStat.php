<li class="internal-micro-stat">
    <<?php echo empty($link) ? 'div' : 'a href="'.ROOT_LINK.'/internal/' . $link . '"' ?>>
        <div class="internal-micro-stat-section">
            <?php if (!empty($image)) { ?>
                <img src="<?php echo STATIC_LINK ?>/img/icons/interface/<?php echo $image ?>" class="internal-micro-stat-image" alt="Image">
            <?php } ?>
        </div>
        <div class="internal-micro-stat-section">
            <span class="internal-micro-stat-description"><?php echo $desc ?></span>
            <span class="internal-micro-stat-title"><?php echo $title ?></span>
        </div>
    </<?php echo empty($link) ? 'div' : 'a' ?>>
</li>