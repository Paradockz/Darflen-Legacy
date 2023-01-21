function mode(element) {
    let url = window.location.link + "/includes/php/explore.php";
    let request = new XMLHttpRequest();
    let body = new FormData;
    try {
        body.append('type', 'mode');
        body.append('limit', 10);
        body.append('offset', 0);
        body.append('mode', element.value);
        request.open('POST', url, true);
        request.send(body);
    } catch (error) {
        createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
    }
    request.onload = function () {
        if (this.status == 200) {
            try {
                const result = JSON.parse(request.responseText);
                switch (result.code) {
                    case 'ready':
                        break;
                    case 'success':
                        document.getElementById('explore-posts').innerHTML = '';
                        renderHTML(result.posts, 'explore-posts');
                        break;
                    default:
                        createModal('A server error occured', result.error, 'Go back', true, () => { }, 'content');
                        break;
                }
            } catch (error) {
                createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
            }
        } else {
            createModal('A client error occured', 'Request failed: Server replied with code ' + this.status, 'Go back', true, () => { }, 'content');
        }
    }
}

function load_more(element,offset) {
    let oldText = element.innerText;
    element.innerHTML = '<span>...</span>';
    let url = window.location.link + "/includes/php/explore.php";
    let request = new XMLHttpRequest();
    let body = new FormData;
    try {
        body.append('type','explore');
        body.append('limit',10);
        body.append('offset',offset);
        request.open('POST', url, true);
        request.send(body);
    } catch (error) {
        createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
        element.innerText = oldText;
    }
    let end = false;
    request.onload = function () {
        if (this.status == 200) {
            try {
                const result = JSON.parse(request.responseText);
                switch (result.code) {
                    case 'ready':
                        end = true;
                        break;
                    case 'success':
                        if (document.getElementById('explore-posts-wide')) {
                            renderHTML(result.posts, 'explore-posts-wide');
                        } else {
                            renderHTML(result.posts, 'explore-posts');
                        }
                        break;
                    default:
                        createModal('A server error occured', result.error, 'Go back', true, () => { }, 'content');
                        break;
                }
            } catch (error) {
                createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
            }
        } else {
            createModal('A client error occured', 'Request failed: Server replied with code ' + this.status, 'Go back', true, () => { }, 'content');
        }
        if (end) {
            element.innerText = 'You\'ve reached the end';
            setTimeout(() => {
                element.remove();
            }, 2000);
        } else {
            element.innerText = oldText;
        }
    }
}