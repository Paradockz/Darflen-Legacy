let markdowns = ['**', '*', '__', '~~', '`', '||'];
let order = ["textarea", "coverage", "images-container"];
let textarea = document.getElementById('textarea');
let counter = document.getElementById('form-text-length');
let ubutton = document.getElementById('images');
let utext = document.getElementsByClassName('images-upload-text')[0];
let submit = document.getElementById('form-submit');
if (submit != null) {
    oldText = submit.innerText;
}
let text = {
    "empty": "This field is required",
    "textarea": {
        "name": "Post",
        "length": "Must be 1024 or lower in length"
    },
    "coverage": {
        "name": "Coverage",
        "invalid": "Invalid value"
    },
    "images-container": {
        "name": "Files",
        "upload": "Upload failed",
        "tSize": "Files sizes must be 100MB or lower",
        "eSize": "File size must be 8MB or lower",
        "length": "Must have less than 10 files",
        "type": "Not supported file type"
    },
    "reason": {
        "name": "Reason",
        "invalid": "Invalid value"
    }
}
document.querySelectorAll('.form-markdown').forEach((element, index) => {
    element.addEventListener("click", function () {
        highlightSelection(markdowns[index]);
    });
});

function updateCounter() {
    if (counter != null) {
        counter.innerText = 1024 - textarea.value.length;
    }
}

updateCounter();


function updateSubCounter() {
    if (sub_counter != null) {
        sub_counter.innerText = 1024 - sub_textarea.value.length;
    }
}


// NEEDS TO BE BUILD FROM SCRATCH!
let executing = false;
function form(form) {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    let reshare = urlParams.has('r') ? urlParams.get('r') : '';
    let url = window.location.link + "/includes/php/posts.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    if (!executing) {
        executing = true;
        submit.innerHTML = '<span>...</span>';
        try {
            body.append('textarea', data.get('textarea'));
            body.append('coverage', data.get('coverage'));
            body.append('reshare', reshare);
            data.getAll('images[]').forEach((element, index) => {
                body.append(index, element);
            });
            body.append('type', 'post');
            request.open('POST', url, true);
            request.send(body);
        } catch (error) {
            createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
            submit.innerText = oldText;
        }
    } else {
        return false;
    }
    
    request.onload = function () {
        if (this.status == 200) {
            try {
                let errors = 0
                const result = JSON.parse(request.responseText);
                switch (result.code) {
                    case 'ready':
                        order.forEach((element, index) => {
                            let input = document.getElementById(element);
                            input.classList.add("form-incorrect-input");
                            formErrorSystem(order, submit, errors, result);
                        });
                        executing = false;
                        break;
                    case 'success':
                        window.location.replace(window.location.link + '/posts/'+result.post);
                        break;
                    default:
                        executing = false;
                        createModal('A server error occured', result.error, 'Go back', true, () => { }, 'page-form');
                        break;
                }
            } catch (error) {
                createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
                executing = false;
                submit.innerText = oldText;
            }
        } else if (this.status == 413) {
            formError(text['images-container'].name + ': ' + text['images-container']['tSize']);
            executing = false;
            submit.innerText = oldText;
        } else {
            createModal('A client error occured', 'Request failed: Server replied with code ' + this.status, 'Go back', true, () => { }, 'page-form');
            executing = false;
        }
        submit.innerText = oldText;
    }

    request.onerror = function () {
        createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'page-form');
        submit.innerText = oldText;
        executing = false;
    }
}

function reply(form,id) {
    order = ["textarea", "images-container"];
    let url = window.location.link + "/includes/php/posts.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    if (!executing) {
        executing = true;
        submit.innerHTML = '<span>...</span>';
        try {
            body.append('textarea', data.get('textarea'));
            data.getAll('images[]').forEach((element, index) => {
                body.append(index, element);
            });
            body.append('id', id);
            body.append('type', 'reply');
            request.open('POST', url, true);
            request.send(body);
        } catch (error) {
            createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
            submit.innerText = oldText;
        }
    } else {
        return false;
    }

    request.onload = function () {
        if (this.status == 200) {
            try {
                let errors = 0
                const result = JSON.parse(request.responseText);
                console.log(result);
                switch (result.code) {
                    case 'ready':
                        order.forEach((element, index) => {
                            let input = document.getElementById(element);
                            input.classList.add("form-incorrect-input");
                            formErrorSystem(order, submit, errors, result);
                        });
                        executing = false;
                        break;
                    case 'success':
                        window.location.reload();
                        break;
                    default:
                        createModal('A server error occured', result.error, 'Go back', true, () => { }, 'page-form');
                        executing = false;
                        break;
                }
            } catch (error) {
                createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
                submit.innerText = oldText;
                executing = false;
            }
        } else if (this.status == 413) {
            formError(text['images-container'].name + ': ' + text['images-container']['tSize']);
            submit.innerText = oldText;
            executing = false;
        } else {
            createModal('A client error occured', 'Request failed: Server replied with code ' + this.status, 'Go back', true, () => { }, 'page-form');
            executing = false;
        }
        submit.innerText = oldText;
    }

    request.onerror = function () {
        createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'page-form');
        submit.innerText = oldText;
        executing = false;
    }
}

function report_post(form, id) {
    order = ["textarea", "reason"];
    let url = window.location.link + "/includes/php/reports.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    if (!executing) {
        executing = true;
        submit.innerHTML = '<span>...</span>';
        try {
            body.append('textarea', data.get('textarea'));
            body.append('reason', data.get('reason'));
            body.append('id', id);
            body.append('type', 'posts');
            request.open('POST', url, true);
            request.send(body);
        } catch (error) {
            createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
            submit.innerText = oldText;
        }
    } else {
        return false;
    }
    request.onload = function () {
        if (this.status == 200) {
            try {
                let errors = 0
                const result = JSON.parse(request.responseText);
                console.log(result);
                switch (result.code) {
                    case 'ready':
                        formErrorSystem(order, submit, errors, result);
                        submit.innerText = oldText;
                        executing = false;
                        break;
                    case 'success':
                        setTimeout(() => {
                            window.location.replace(window.location.link + '/posts/' + result.post);
                        }, 1500);
                        submit.innerText = 'Post has been reported';
                        break;
                    default:
                        createModal('A server error occured', result.error, 'Go back', true, () => { }, 'page-form');
                        submit.innerText = oldText;
                        executing = false;
                        break;
                }
            } catch (error) {
                createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
                submit.innerText = oldText;
                executing = false;
            }
        } else {
            createModal('A client error occured', 'Request failed: Server replied with code ' + this.status, 'Go back', true, () => { }, 'page-form');
            submit.innerText = oldText;
            executing = false;
        }
    }

    request.onerror = function () {
        createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'page-form');
        submit.innerText = oldText;
        executing = false;
    }
}

function edit(form, id) {
    order = ["textarea", "coverage", "images-container"];
    let url = window.location.link + "/includes/php/posts.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    if (!executing) {
        executing = true;
        submit.innerHTML = '<span>...</span>';
        try {
            body.append('textarea', data.get('textarea'));
            body.append('coverage', data.get('coverage') ?? 'null');
            data.getAll('images[]').forEach((element, index) => {
                body.append(index, element);
            });
            body.append('id', id);
            body.append('type', 'edit');
            request.open('POST', url, true);
            request.send(body);
        } catch (error) {
            createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
            submit.innerText = oldText;
        }
    } else {
        return false;
    }

    request.onload = function () {
        if (this.status == 200) {
            try {
                let errors = 0
                const result = JSON.parse(request.responseText);
                console.log(result);
                if (result.mode == 'reply') {
                    delete order[1];
                }
                switch (result.code) {
                    case 'ready':
                        order.forEach((element, index) => {
                            let input = document.getElementById(element);
                            input.classList.add("form-incorrect-input");
                            formErrorSystem(order, submit, errors, result);
                        });
                        executing = false;
                        break;
                    case 'success':
                        window.location.replace(window.location.link + '/posts/' + result.post);
                        break;
                    default:
                        createModal('A server error occured', result.error, 'Go back', true, () => { }, 'page-form');
                        executing = false;
                        break;
                }
            } catch (error) {
                createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
                submit.innerText = oldText;
                executing = false;
            }
        } else if (this.status == 413) {
            formError(text['images-container'].name + ': ' + text['images-container']['tSize']);
            submit.innerText = oldText;
            executing = false;
        } else {
            createModal('A client error occured', 'Request failed: Server replied with code ' + this.status, 'Go back', true, () => { }, 'page-form');
            executing = false;
        }
        submit.innerText = oldText;
    }

    request.onerror = function () {
        createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'page-form');
        submit.innerText = oldText;
        executing = false;
    }
}

function disappear(id) {
    createModal('Delete Post', 'Are you sure you want to delete this post?', 'Delete', true, () => {
        let url = window.location.link + "/includes/php/posts.php";
        let request = new XMLHttpRequest();
        let body = new FormData;
        try {
            body.append('type', 'delete_post');
            body.append('id', id);
            request.open('POST', url, true);
            request.send(body);
        } catch (error) {
            createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
            submit.innerText = oldText;
        }
        request.onload = function () {
            try {
                const result = JSON.parse(request.responseText);
                switch (result.code) {
                    case 'success':
                        window.location.replace(window.location.link + '/'+result.page);
                        break;
                    default:
                        createModal('A server error occured', result.error, 'Go back', true, () => { }, 'content');
                        break;
                }
            } catch (error) {
                createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
            }
        }
    }, 'content');
}

// THIS IS A MESS!
if(!!textarea) {
    textarea.addEventListener("keyup", () => {
        updateCounter();
    });

    textarea.addEventListener("keydown", () => {
        updateCounter();
    });
}

if(!!ubutton) {
    ubutton.addEventListener("change", () => {
        if (ubutton.files && ubutton.files[0]) {
            utext.innerText = ubutton.files[0].name;
            files = 0;
            utext.title = '';
            while (files < ubutton.files.length) {
                utext.title += ubutton.files[files].name + ' ';
                files++
            }
            if (files - 1 > 0) {
                utext.innerText += ' + ' + (files - 1) + (files - 1 > 1 ? ' files' : ' file');
            }
            utext.title.trim();
        }
    });
}

function heart(element,id) {
    let url = window.location.link + "/includes/php/posts.php";
    let request = new XMLHttpRequest();
    let body = new FormData;
    if(!!document.getElementById('profile-stats')) {
        var hca = document.getElementById('profile-stats').children[3].children[0].children[1];
        var hcb = document.getElementById('profile-banner-stats').children[2].children[0].children[1];
    }
    let heartText = element.children[1];
    let oldText = heartText.innerText;
    heartText.innerText = '...';
    try {
        body.append('id', id);
        body.append('type', 'heart_post');
        request.open('POST', url, true);
        request.send(body);
    } catch (error) {
        createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
        heartText.innerText = oldText;
    }
    request.onload = function () {
        formErrorSystem2(this, 'post', heartText, hca, hcb, request, element, oldText);
    }
    request.onerror = function () {
        createModal('A client error occured', 'Request failed', 'Continue', true, () => { }, 'content');
        heartText.innerText = oldText;
    };
}

function heart_reply(element,id) {
    let url = window.location.link + "/includes/php/posts.php";
    let request = new XMLHttpRequest();
    let body = new FormData;
    if(!!document.getElementById('profile-stats')) {
        var hca = document.getElementById('profile-stats').children[3].children[0].children[1];
        var hcb = document.getElementById('profile-banner-stats').children[2].children[0].children[1];
    }
    let heartText = element.children[1];
    let oldText = heartText.innerText;
    heartText.innerText = '...';
    try {
        body.append('id', id);
        body.append('type', 'heart_reply');
        request.open('POST', url, true);
        request.send(body);
    } catch (error) {
        createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
        heartText.innerText = oldText;
    }
    request.onload = function () {
        formErrorSystem2(this, 'reply', heartText, hca, hcb, request, element, oldText);
    }
    request.onerror = function () {
        createModal('A client error occured', 'Request failed', 'Continue', true, () => { }, 'content');
        heartText.innerText = oldText;
    };
}

function heart_subreply(element, id) {
    let url = window.location.link + "/includes/php/subreplies.php";
    let request = new XMLHttpRequest();
    let body = new FormData;
    if (!!document.getElementById('profile-stats')) {
        var hca = document.getElementById('profile-stats').children[3].children[0].children[1];
        var hcb = document.getElementById('profile-banner-stats').children[2].children[0].children[1];
    }
    let heartText = element.children[1];
    let oldText = heartText.innerText;
    heartText.innerText = '...';
    try {
        body.append('id', id);
        body.append('type', 'heart_subreply');
        request.open('POST', url, true);
        request.send(body);
    } catch (error) {
        createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
        heartText.innerText = oldText;
    }
    request.onload = function () {
        formErrorSystem2(this, 'reply', heartText, hca, hcb, request, element, oldText);
    }
    request.onerror = function () {
        createModal('A client error occured', 'Request failed', 'Continue', true, () => { }, 'content');
        heartText.innerText = oldText;
    };
}

function load_subreply(form,id, textarea = '') {
    if (typeof textply !== 'undefined' && typeof oldText !== 'undefined') {
        textply.innerText = oldText;
    }
    let url = window.location.link + "/includes/php/subreplies.php";
    let request = new XMLHttpRequest();
    let body = new FormData;
    textply = form.getElementsByClassName('profile-post-action-value')[0];
    oldText = textply.innerText;
    try {
        body.append('type', 'load');
        body.append('reply', id);
        request.open('POST', url, true);
        request.send(body);
    } catch (error) {
        createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
        submit.innerText = oldText;
    }
    request.onload = function () {
        try {
            const result = request.responseText;
            node = document.getElementById('subreply-form');
            if (node !== null) {
                document.getElementById('subreply-markdowns').querySelectorAll('.form-markdown').forEach((element, index) => {
                    element.removeEventListener("click");
                });
                sub_textarea.removeEventListener("keyup");
                sub_textarea.removeEventListener("keydown");
                sub_utext.removeEventListener("change");
                node.remove();
            }
            if (typeof old_id !== 'undefined' && old_id == form.parentNode.parentNode.parentNode.parentNode.id) {
                old_id = undefined;
                return;
            } else {
                old_id = form.parentNode.parentNode.parentNode.parentNode.id;
                textply.innerText = 'Cancel';
            }
            form.parentNode.parentNode.parentNode.parentNode.insertAdjacentHTML('afterend', result);
            node = document.getElementById('subreply-form');
            sub_textarea = node.querySelectorAll('#textarea')[0];
            sub_counter = node.querySelectorAll('#form-text-length')[0];
            sub_ubutton = node.querySelectorAll('#images')[0];
            sub_utext = node.querySelectorAll('.images-upload-text')[0];
            sub_submit = node.querySelectorAll('#form-submit')[0];
            if (sub_submit != null) {
                sub_oldText = sub_submit.innerText;
            }
            document.getElementById('subreply-markdowns').querySelectorAll('.form-markdown').forEach((element, index) => {
                element.addEventListener("click", function () {
                    highlightSelection(markdowns[index], node.querySelectorAll('#textarea')[0]);
                });
            });
            // THIS IS A MESS!
            sub_textarea.addEventListener("keyup", () => {
                updateSubCounter();
            });
            sub_textarea.addEventListener("keydown", () => {
                updateSubCounter();
            });
            sub_ubutton.addEventListener("change", () => {
                if (sub_ubutton.files && sub_ubutton.files[0]) {
                    sub_utext.innerText = sub_ubutton.files[0].name;
                    files = 0;
                    sub_utext.title = '';
                    while (files < sub_ubutton.files.length) {
                        sub_utext.title += sub_ubutton.files[files].name + ' ';
                        files++
                    }
                    if (files - 1 > 0) {
                        sub_utext.innerText += ' + ' + (files - 1) + (files - 1 > 1 ? ' files' : ' file');
                    }
                    sub_utext.title.trim();
                }
            });
        } catch (error) {
            createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
        }
    }
}

function subreply(form,id) {
    let order = ["textarea", "images-container"];
    let url = window.location.link + "/includes/php/subreplies.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    if (!executing) {
        executing = true;
        sub_submit.innerHTML = '<span>...</span>';
        try {
            body.append('type', 'post');
            body.append('id', id);
            body.append('textarea', data.get('textarea'));
            body.append('coverage', data.get('coverage'));
            data.getAll('images[]').forEach((element, index) => {
                body.append(index, element);
            });
            body.append('type', 'post');
            request.open('POST', url, true);
            request.send(body);
        } catch (error) {
            createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
            sub_submit.innerText = sub_oldText;
        }
    } else {
        return false;
    }

    request.onload = function () {
        if (this.status == 200) {
            try {
                const result = JSON.parse(request.responseText);
                let errors = 0;
                console.log(result);
                switch (result.code) {
                    case 'ready':
                        order.forEach((element, index) => {
                            let input = node.querySelectorAll('#'+element)[0];
                            input.classList.add("form-incorrect-input");
                            formErrorSystem3(order, sub_submit, errors, result, node);
                        });
                        executing = false;
                        break;
                    case 'success':
                        window.location.replace(window.location.link + '/posts/' + result.post);
                        window.location.reload();
                        break;
                    default:
                        createModal('A server error occured', result.error, 'Go back', true, () => { }, 'page-form');
                        executing = false;
                        break;
                }
            } catch (error) {
                createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
                sub_submit.innerText = sub_oldText;
                executing = false;
            }
        } else if (this.status == 413) {
            formError(text['images-container'].name + ': ' + text['images-container']['tSize']);
            sub_submit.innerText = sub_oldText;
            executing = false;
        } else {
            createModal('A client error occured', 'Request failed: Server replied with code ' + this.status, 'Go back', true, () => { }, 'page-form');
            executing = false;
        }
        sub_submit.innerText = sub_oldText;
    }

    request.onerror = function () {
        createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'page-form');
        sub_submit.innerText = sub_oldText;
        executing = false;
    }
}

function sub_edit(form, id) {
    order = ["textarea", "images-container"];
    let url = window.location.link + "/includes/php/subreplies.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    if (!executing) {
        executing = true;
        submit.innerHTML = '<span>...</span>';
        try {
            body.append('textarea', data.get('textarea'));
            data.getAll('images[]').forEach((element, index) => {
                body.append(index, element);
            });
            body.append('id', id);
            body.append('type', 'edit');
            request.open('POST', url, true);
            request.send(body);
        } catch (error) {
            createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
            submit.innerText = oldText;
        }
    } else {
        return false;
    }

    request.onload = function () {
        if (this.status == 200) {
            try {
                let errors = 0
                const result = JSON.parse(request.responseText);
                console.log(result);
                if (result.mode == 'reply') {
                    delete order[1];
                }
                switch (result.code) {
                    case 'ready':
                        order.forEach((element, index) => {
                            let input = document.getElementById(element);
                            input.classList.add("form-incorrect-input");
                            formErrorSystem3(order, submit, errors, result, node);
                            executing = false;
                        });
                        break;
                    case 'success':
                        window.location.replace(window.location.link + '/posts/' + result.post);
                        break;
                    default:
                        createModal('A server error occured', result.error, 'Go back', true, () => { }, 'page-form');
                        executing = false;
                        break;
                }
            } catch (error) {
                createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
                submit.innerText = oldText;
                executing = false;
            }
        } else if (this.status == 413) {
            formError(text['images-container'].name + ': ' + text['images-container']['tSize']);
            submit.innerText = oldText;
            executing = false;
        } else {
            createModal('A client error occured', 'Request failed: Server replied with code ' + this.status, 'Go back', true, () => { }, 'page-form');
            executing = false;
        }
        submit.innerText = oldText;
    }

    request.onerror = function () {
        createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'page-form');
        submit.innerText = oldText;
        executing = false;
    }
}

function sub_disappear(id) {
    createModal('Delete Reply', 'Are you sure you want to delete this reply?', 'Delete', true, () => {
        let url = window.location.link + "/includes/php/subreplies.php";
        let request = new XMLHttpRequest();
        let body = new FormData;
        try {
            body.append('type', 'delete');
            body.append('id', id);
            request.open('POST', url, true);
            request.send(body);
        } catch (error) {
            createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
            submit.innerText = oldText;
        }
        request.onload = function () {
            try {
                const result = JSON.parse(request.responseText);
                switch (result.code) {
                    case 'success':
                        window.location.replace(window.location.link + '/' + result.page);
                        break;
                    default:
                        createModal('A server error occured', result.error, 'Go back', true, () => { }, 'content');
                        break;
                }
            } catch (error) {
                createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
            }
        }
    }, 'content');
}