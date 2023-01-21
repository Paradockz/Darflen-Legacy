<?php if ($controls) : ?>
    <div class="post-audio-controls post-audio-container" tabindex="0">
        <audio preload="metadata" class="post-audio">
            <source src="<?php echo $audio ?>" type="<?php echo $mime ?>">
        </audio>
        <div class="post-audio-controls-container">
            <div class="post-audio-controls-content">
                <ul class="post-audio-controls-list-items">
                    <li class="post-audio-controls-list-item">
                        <button class="audio-play-button">
                            <img class="post-audio-controls-icon" src="<?php echo STATIC_LINK ?>/img/icons/interface/play.svg" alt="Play/Pause icon">
                        </button>
                    </li>
                    <li class="post-audio-controls-list-item">
                        <input type="range" name="audio-seek" class="audio-seek" min="0" max="100" step="1" value="3">
                    </li>
                    <li class="post-audio-controls-list-item">
                        <span class="post-audio-timestamp">
                            --:-- / --:--
                        </span>
                    </li>
                    <li class="post-audio-controls-list-item">
                        <div class="audio-volume-button-container">
                            <button class="audio-volume-button">
                                <img class="post-audio-controls-icon" src="<?php echo STATIC_LINK ?>/img/icons/interface/volume-up.svg" alt="Volume icon">
                            </button>
                            <input type="range" name="audio-volume" class="audio-volume" min="0" max="100" value="50">
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>