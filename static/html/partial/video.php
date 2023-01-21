<div class="post-video-controls post-video-container" tabindex="0">
    <video buffered class="post-video" preload="metadata" poster="<?php echo $poster ?>">
        <source src="<?php echo $video ?>" type="<?php echo $mime ?>">
    </video>
    <?php if ($controls): ?>
    <div class="post-video-playback-container post-video-playback-visible">
        <img class="post-video-playback-icon post-video-playback-visible" src="<?php echo STATIC_LINK ?>/img/icons/interface/play.svg" alt="Playback icon">
    </div>
    <div class="post-video-controls-container">
        <div class="post-video-controls-content">
            <div class="post-video-controls-progress">
                <input type="range" name="video-seek" class="video-seek" min="0" max="100" step="1" value="3">
            </div>
            <div class="post-video-controls-items">
                <div class="post-video-controls-section">
                    <ul class="post-video-controls-list-items">
                        <li class="post-video-controls-list-item">
                            <button class="play-button">
                                <img class="post-video-controls-icon" src="<?php echo STATIC_LINK ?>/img/icons/interface/play.svg" alt="Play/Pause icon">
                            </button>
                        </li>
                        <li class="post-video-controls-list-item">
                            <div class="volume-button-container">
                                <button class="volume-button">
                                    <img class="post-video-controls-icon" src="<?php echo STATIC_LINK ?>/img/icons/interface/volume-up.svg" alt="Volume icon">
                                </button>
                                <input type="range" name="video-volume" class="video-volume" min="0" max="100" value="50">
                            </div>
                        </li>
                        <li class="post-video-controls-list-item">
                            <span class="post-video-timestamp">
                                --:-- / --:--
                            </span>
                        </li>
                    </ul>
                </div>
                <div class="post-video-controls-section">
                    <li class="post-video-controls-list-item">
                        <button class="screen-button">
                            <img class="post-video-controls-icon" src="<?php echo STATIC_LINK ?>/img/icons/interface/expand.svg" alt="Minimize/Maximize icon">
                        </button>
                    </li>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>