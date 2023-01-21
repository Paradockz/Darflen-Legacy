let submit = document.getElementById('form-submit');
let counter = document.getElementById('form-text-length');
let oldText = submit.innerText;
let text = {
    "empty": "This field is required",
    "textarea": {
        "name": "Post",
        "length": "Must be 1024 or lower in length"
    },
    "time": {
        "name": "Time",
        "invalid": "Invalid time value"
    },
    "reason": {
        "name": "Reason",
        "invalid": "Invalid value"
    }
}

function user_ban(form, id) {
    order = ["reason", "time", "textarea"];
    submit.innerHTML = '<span>...</span>';
    let url = window.location.link + "/includes/php/internal/bans.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    try {
        body.append('textarea', data.get('textarea'));
        body.append('reason', data.get('reason'));
        body.append('time', data.get('time'));
        body.append('id', id);
        body.append('type', 'ban');
        request.open('POST', url, true);
        request.send(body);
    } catch (error) {
        createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
        submit.innerText = oldText;
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
                        break;
                    case 'success':
                        setTimeout(() => {
                            window.history.back();
                        }, 1500);
                        submit.innerText = 'User is banned';
                        break;
                    default:
                        createModal('A server error occured', result.error, 'Go back', true, () => { }, 'page-form');
                        submit.innerText = oldText;
                        break;
                }
            } catch (error) {
                createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
                submit.innerText = oldText;
            }
        } else {
            createModal('A client error occured', 'Request failed: Server replied with code ' + this.status, 'Go back', true, () => { }, 'page-form');
            submit.innerText = oldText;
        }
    }

    request.onerror = function () {
        createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'page-form');
        submit.innerText = oldText;
    }
}

function edit_profile(form,id) {
    order = ["email", "username", "password", "banner", "icon", "description", "administrator", "email_verified", "user_verified"];
    let text = {
        "empty": "This field is required",
        "banner": {
            "name": "Banner",
            "upload": "Upload failed",
            "size": "File size must be 8MB or lower",
            "type": "Not supported file type"
        },
        "icon": {
            "name": "Icon",
            "upload": "Upload failed",
            "size": "File size must be 8MB or lower",
            "type": "Not supported file type"
        },
        "description": {
            "name": "Description",
            "invalid": "Invalid description",
            "length": "Must be 1024 or lower in length"
        },
        "email": {
            "name": "Email",
            "length": "Must be 255 or fewer in length",
            "malformated": "Not a well formed email address",
            "invalid": "Invalid email address",
            "used": "Email is already registered"
        },
        "username": {
            "name": "Username",
            "length": "Must be between 2 and 32 characters in length",
            "malformated": "Must only contain alphanumeric letters - and _"
        },
        "password": {
            "name": "Password",
            "length": "Must be between 6 and 255 characters in length",
            "malformated": "Must be more complex"
        },
        "administrator": {
            "name": "Administrator",
            "invalid": "Invalid administrator value"
        },
        "email_verified": {
            "name": "Email verification",
            "invalid": "Invalid email verification value"
        },
        "user_verified": {
            "name": "Account verification",
            "invalid": "Invalid account verification value"
        }
    };
    submit.innerHTML = '<span>...</span>';
    let url = window.location.link + "/includes/php/internal/users.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    try {
        body.append('id', id);
        body.append('banner', data.get('banner'));
        body.append('icon', data.get('icon'));
        body.append('description', data.get('description'));
        body.append('username', data.get('username'));
        body.append('email', data.get('email'));
        body.append('password', data.get('password'));
        body.append('administrator', data.get('administrator'));
        body.append('email_verified', data.get('email_verified'));
        body.append('user_verified', data.get('user_verified'));
        body.append('type', 'edit');
        request.open('POST', url, true);
        request.send(body);
    } catch (error) {
        noFormErrors(text, order);
        createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
        submit.innerText = oldText;
    }

    request.onload = function () {
        if (this.status == 200) {
            if (updateForm(text, request, submit, window.location.link + '/settings/', order, false, 'content')) {
                submit.innerText = 'Everything successfully saved.';
            }
            setTimeout(() => {
                submit.innerText = oldText;
            }, 2000);
        } else {
            createModal('A server error occured', 'Server replied with: ' + this.status, 'Continue', true, () => { }, 'content');
        }
    }

    request.onerror = function () {
        noFormErrors(text, order);
        createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'content');
        submit.innerText = oldText;
    };
}

// Crappy code
q = document.querySelectorAll('.settings-file-selector');
q.forEach(button => {
    button.addEventListener("change", () => {
        c = button.children[1]
        a = button.children[0];
        if (a.files && a.files[0]) {
            c.innerText = a.files[0].name;
            c.title = a.files[0].name;
        }
    });
});

document.querySelectorAll('.form-markdown').forEach((element, index) => {
    let markdowns = ['**', '*', '__', '~~', '`', '||'];
    let textarea = document.getElementById('description');
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

// THIS IS A MESS!
textarea.addEventListener("keyup", () => {
    updateCounter();
});

textarea.addEventListener("keydown", () => {
    updateCounter();
});

function edit(form, id, type = 'edit') {
    order = ["textarea", "coverage", "images-container"];
    submit.innerHTML = '<span>...</span>';
    let url = window.location.link + "/includes/php/internal/posts.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    try {
        body.append('textarea', data.get('textarea'));
        body.append('coverage', data.get('coverage') ?? 'null');
        data.getAll('images[]').forEach((element, index) => {
            body.append(index, element);
        });
        body.append('id', id);
        body.append('type', type);
        request.open('POST', url, true);
        request.send(body);
    } catch (error) {
        createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
        submit.innerText = oldText;
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
                        break;
                    case 'success':
                        window.history.back();
                        break;
                    default:
                        createModal('A server error occured', result.error, 'Go back', true, () => { }, 'page-form');
                        break;
                }
            } catch (error) {
                createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
                submit.innerText = oldText;
            }
        } else if (this.status == 413) {
            formError(text['images-container'].name + ': ' + text['images-container']['tSize']);
        } else {
            createModal('A client error occured', 'Request failed: Server replied with code ' + this.status, 'Go back', true, () => { }, 'page-form');
        }
        submit.innerText = oldText;
    }

    request.onerror = function () {
        createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'page-form');
        submit.innerText = oldText;
    }
}

function disappear2(id, thing = 'Post') {
    createModal('Delete ' + thing, 'Are you sure you want to delete this ' + thing.toLowerCase()+'?', 'Delete', true, () => {
        let url = window.location.link + "/includes/php/internal/posts.php";
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
                        window.location.reload(true);
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

function unban(id) {
    createModal('Pardon', 'Are you sure you want to pardon this user?', 'Pardon user', true, () => {
        let url = window.location.link + "/includes/php/internal/bans.php";
        let request = new XMLHttpRequest();
        let body = new FormData;
        try {
            body.append('type', 'delete_post');
            body.append('id', id);
            body.append('type', 'unban');
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
                        window.location.reload(true);
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
