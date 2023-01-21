function seconds_to_time(duration) {
    // Hours, minutes and seconds
    var hrs = ~~(duration / 3600);
    var mins = ~~((duration % 3600) / 60);
    var secs = ~~duration % 60;
    if (hrs < 1) {
        return mins + ":" + String(secs < 10 ? 0 + String(secs) : secs);
    } else {
        return hrs + ":" + mins + ":" + String(secs < 10 ? 0 + String(secs) : secs);
    }
}

document.querySelectorAll('.post-video-controls').forEach((element, index) => {
    let player = document.getElementsByClassName('post-video-controls')[index];
    let video = player.getElementsByClassName('post-video')[0];
    let video_container = player.getElementsByClassName('post-video-controls-container')[0];
    let video_content = video_container.getElementsByClassName('post-video-controls-content')[0];
    let seek = player.getElementsByClassName('video-seek')[0];
    let play = video_content.getElementsByClassName('play-button')[0];
    let volume = video_content.getElementsByClassName('video-volume')[0];
    let mute = video_content.getElementsByClassName('volume-button')[0];
    let screen = video_content.getElementsByClassName('screen-button')[0];
    let timestamp = video_content.getElementsByClassName('post-video-timestamp')[0];
    let playback_container = player.getElementsByClassName('post-video-playback-container')[0];
    let playback_icon = playback_container.getElementsByClassName('post-video-playback-icon')[0];
    let banned_area_clicked = false;
    let buffering = null;
    let buffering_threshold = 333; //ms after which user perceives buffering

    let video_test = !!document.createElement('video').canPlayType;
    if (video_test) {
        video.controls = false;
    }

    function update_things() {
        percentage = (video.currentTime / video.duration * 100);
        seek.value = percentage;
        seek.style.backgroundSize = seek.value + '% 100%';
        volume.style.backgroundSize = volume.value + '% 100%';
        timestamp.innerText = seconds_to_time(video.currentTime) + ' / ' + seconds_to_time(video.duration);
    }
    update_things();

    function update_mute() {
        video.muted = video.muted != false ? false : true;
        if (mute.getElementsByClassName('post-video-controls-icon')[0].src == window.location.flink +  '/img/icons/interface/volume-up.svg') {
            mute.getElementsByClassName('post-video-controls-icon')[0].src = window.location.flink +  '/img/icons/interface/volume-off.svg';
        } else {
            mute.getElementsByClassName('post-video-controls-icon')[0].src = window.location.flink +  '/img/icons/interface/volume-up.svg';
        }
    }

    function open_full_screen() {
        if (player.requestFullscreen) {
            player.requestFullscreen();
        } else if (player.webkitRequestFullscreen) {
            /* Safari */
            player.webkitRequestFullscreen();
        } else if (player.msRequestFullscreen) {
            /* IE11 */
            player.msRequestFullscreen();
        }
    }

    function close_full_screen() {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            /* Safari */
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            /* IE11 */
            document.msExitFullscreen();
        }
    }

    function update_screen() {
        if (screen.getElementsByClassName('post-video-controls-icon')[0].src == window.location.flink +  '/img/icons/interface/expand.svg') {
            if (!window.fullScreen || !(window.innerWidth == screen.width && window.innerHeight == screen.height)) {
                open_full_screen();
            }
        } else {
            if (window.fullScreen || (window.innerWidth == screen.width && window.innerHeight == screen.height)) {
                close_full_screen();
            }
        }
    }

    function change_play_state() {
        if (!!(video.currentTime > 0 && !video.paused && !video.ended && video.readyState > 2)) {
            video.pause();
            play.getElementsByClassName('post-video-controls-icon')[0].src = window.location.flink +  '/img/icons/interface/play.svg';
            playback_icon.src = window.location.flink +  '/img/icons/interface/pause.svg';
            do_your_things();
        } else if (!video.ended) {
            video.play();
            play.getElementsByClassName('post-video-controls-icon')[0].src = window.location.flink +  '/img/icons/interface/pause.svg';
            playback_icon.src = window.location.flink +  '/img/icons/interface/play.svg';
            do_your_things();
        } else {
            video.currentTime = 0;
            video.play();
            play.getElementsByClassName('post-video-controls-icon')[0].src = window.location.flink +  '/img/icons/interface/pause.svg';
            playback_icon.src = window.location.flink +  '/img/icons/interface/undo.svg';
            do_your_things();
        }
    }

    function animate_playback_container() {
        playback_container.animate(
            [{
                width: '4rem',
                height: '4rem',
                opacity: 1
            },
            {
                width: '6rem',
                height: '6rem',
                opacity: 0
            },
            ], {
            duration: 1000,
        }
        );
    }

    function animate_playback_icon() {
        playback_icon.animate(
            [{
                width: '2rem',
                height: '2rem',
                opacity: 1
            },
            {
                width: '3rem',
                height: '3rem',
                opacity: 0
            },
            ], {
            duration: 1000,
        }
        );
    }

    function do_your_things() {
        animate_playback_container();
        animate_playback_icon();
    }

    function skip_to() {
        video.currentTime = seek.value * video.duration / 100;
    }

    seek.addEventListener("input", () => {
        skip_to();
        if (play.getElementsByClassName('post-video-controls-icon')[0].src == window.location.flink +  '/img/icons/interface/undo.svg') {
            play.getElementsByClassName('post-video-controls-icon')[0].src = window.location.flink +  '/img/icons/interface/play.svg';
        }
    });

    volume.addEventListener("input", () => {
        video.volume = volume.value / 100;
    });

    video_container.addEventListener("click", () => {
        if (banned_area_clicked != true && buffering == null) {
            change_play_state();
        }
        banned_area_clicked = false;
    });

    video_content.addEventListener("click", () => {
        banned_area_clicked = true;
    });


    video.addEventListener('waiting', () => {
        playback_icon.src = window.location.flink +  '/img/icons/interface/repeat.svg';
        buffering = setTimeout(() => {
            playback_icon.classList.add('post-video-playback-visible');
            playback_icon.classList.add('post-video-playback-buffer');
            playback_container.classList.add('post-video-playback-visible');
        }, buffering_threshold);
    });
    
    video.addEventListener('playing', () => {
        if (buffering != null) {
            clearTimeout(buffering);
            buffering = null;
        }
        playback_icon.classList.remove('post-video-playback-visible');
        playback_icon.classList.remove('post-video-playback-buffer');
        playback_container.classList.remove('post-video-playback-visible');
    });


    setInterval(function () {
        update_things(); // will get you a lot more updates.
    }, 1);

    video.addEventListener("ended", () => {
        video.pause();
        play.getElementsByClassName('post-video-controls-icon')[0].src = window.location.flink +  '/img/icons/interface/undo.svg';
    });

    play.addEventListener("click", () => {
        if (buffering == null) {
            change_play_state();
        }
    });

    mute.addEventListener("click", () => {
        update_mute();
    });

    screen.addEventListener("click", () => {
        update_screen();
    });

    addEventListener('fullscreenchange', (event) => {
        if (window.fullScreen || (window.innerWidth == screen.width && window.innerHeight == screen.height)) {
            screen.getElementsByClassName('post-video-controls-icon')[0].src = window.location.flink +  '/img/icons/interface/minimize.svg';
        } else {
            screen.getElementsByClassName('post-video-controls-icon')[0].src = window.location.flink +  '/img/icons/interface/expand.svg';
        }
    });

    player.addEventListener("keyup", (event) => {
        key = event.key;
        switch (key) {
            case 'k':
            case ' ':
                change_play_state();
                break;
            case 'm':
                update_mute();
                break;
            case 'f':
                update_screen();
                break;
        }
    });
});


document.querySelectorAll('.post-audio-controls').forEach((element, index) => {
    let player = document.getElementsByClassName('post-audio-controls')[index];
    let audio = player.getElementsByClassName('post-audio')[0];
    let seek = player.getElementsByClassName('audio-seek')[0];
    let audio_container = player.getElementsByClassName('post-audio-controls-container')[0];
    let audio_content = audio_container.getElementsByClassName('post-audio-controls-content')[0];
    let volume = audio_content.getElementsByClassName('audio-volume')[0];
    let play = audio_content.getElementsByClassName('audio-play-button')[0];
    let mute = audio_content.getElementsByClassName('audio-volume-button')[0];
    let timestamp = audio_content.getElementsByClassName('post-audio-timestamp')[0];

    audio.controls = false;
    function update_things() {
        percentage = (audio.currentTime / audio.duration * 100);
        seek.value = percentage;
        seek.style.backgroundSize = seek.value + '% 100%';
        volume.style.backgroundSize = volume.value + '% 100%';
        timestamp.innerText = seconds_to_time(audio.currentTime) + ' / ' + seconds_to_time(audio.duration);
    }
    update_things();

    function update_mute() {
        audio.muted = audio.muted != false ? false : true;
        if (mute.getElementsByClassName('post-audio-controls-icon')[0].src == window.location.flink +  '/img/icons/interface/volume-up.svg') {
            mute.getElementsByClassName('post-audio-controls-icon')[0].src = window.location.flink +  '/img/icons/interface/volume-off.svg';
        } else {
            mute.getElementsByClassName('post-audio-controls-icon')[0].src = window.location.flink +  '/img/icons/interface/volume-up.svg';
        }
    }

    function change_play_state() {
        if (!!(audio.currentTime > 0 && !audio.paused && !audio.ended && audio.readyState > 2)) {
            audio.pause();
            play.getElementsByClassName('post-audio-controls-icon')[0].src = window.location.flink +  '/img/icons/interface/play.svg';
        } else if (!audio.ended) {
            audio.play();
            play.getElementsByClassName('post-audio-controls-icon')[0].src = window.location.flink +  '/img/icons/interface/pause.svg';
        } else {
            audio.currentTime = 0;
            audio.play();
            play.getElementsByClassName('post-audio-controls-icon')[0].src = window.location.flink +  '/img/icons/interface/pause.svg';
        }
    }

    function skip_to() {
        audio.currentTime = seek.value * audio.duration / 100;
    }

    mute.addEventListener("click", () => {
        update_mute();
    });

    seek.addEventListener("input", () => {
        skip_to();
        if (play.getElementsByClassName('post-audio-controls-icon')[0].src == window.location.flink +  '/img/icons/interface/undo.svg') {
            play.getElementsByClassName('post-audio-controls-icon')[0].src = window.location.flink +  '/img/icons/interface/play.svg';
        }
    });

    play.addEventListener("click", () => {
        change_play_state();
    });

    volume.addEventListener("input", () => {
        audio.volume = volume.value / 100;
    });

    audio.addEventListener("ended", () => {
        audio.pause();
        play.getElementsByClassName('post-audio-controls-icon')[0].src = window.location.flink +  '/img/icons/interface/undo.svg';
    });

    setInterval(function () {
        update_things(); // will get you a lot more updates.
    }, 1);

    player.addEventListener("keyup", (event) => {
        key = event.key;
        switch (key) {
            case 'k':
            case ' ':
                change_play_state();
                break;
            case 'm':
                update_mute();
                break;
        }
    });
});