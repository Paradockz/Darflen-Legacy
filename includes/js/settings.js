let order = ["banner", "icon", "description"];
let submit = document.getElementById('form-submit');
let submit2 = document.getElementById('logout-devices-submit');
let submit3 = document.getElementById('link-generate');
let oldText = submit.innerText;
let oldText2 = submit2.innerText;
let oldText3 = submit3.innerText;
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
    }
};

function verify() {
    let url = window.location.link + "/includes/php/authentication/verify-email.php";
    let request = new XMLHttpRequest();
    let body = new FormData;
    try {
        body.append('type', 'send');
        request.open('POST', url, true);
        request.send(body);
    } catch (error) {
        noFormErrors(text, order);
        createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
    }
    request.onload = function () { // request successful
        var result = JSON.parse(request.responseText);
        if (result.code == 'ready') {
            window.location.replace(window.location.link + "/email/verify");
        }
    };
}

let executing = false;
function profile(form) {
    submit.innerHTML = '<span>...</span>';
    let url = window.location.link + "/includes/php/settings.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    if (!executing) {
        executing = true;
        try {
            body.append('banner', data.get('banner'));
            body.append('icon', data.get('icon'));
            body.append('description', data.get('description'));
            body.append('type', 'profile');
            request.open('POST', url, true);
            request.send(body);
        } catch (error) {
            noFormErrors(text, order);
            createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
            submit.innerText = oldText;
        }
    } else {
        return false;
    }

    request.onload = function () {
        if (this.status == 200) {
            if (updateForm(text, request, submit, window.location.link + '/settings/', order, false, 'content')) {
                submit.innerText = 'Everything successfully saved.';
            }
            setTimeout(() => {
                submit.innerText = oldText;
                executing = false;
            }, 2000);
        } else {
            createModal('A server error occured', 'Server replied with: ' + this.status, 'Continue', true, () => { }, 'content');
            executing = false;
        }
    }

    request.onerror = function () {
        noFormErrors(text, order);
        createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'content');
        submit.innerText = oldText;
        executing = false;
    };
}

function theme(form) {
    let url = window.location.link + "/includes/php/settings.php";
    let request = new XMLHttpRequest();
    let body = new FormData;
    try {
        body.append('theme', form.value);
        body.append('type', 'theme');
        request.open('POST', url, true);
        request.send(body);
    } catch (error) {
        createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
    }

    request.onload = function () {
        if (this.status == 200) {
            try {
                const result = JSON.parse(request.responseText);
                switch (result.code) {
                    case "success":
                        html = document.documentElement;
                        html.setAttribute("theme", form.value);
                        break;
                    default:
                        createModal('A server error occured', result.error, 'Continue', true, () => { }, 'content');
                        break;
                }
            } catch (error) {
                createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
            }
        } else {
            createModal('A server error occured', 'Server replied with: ' + this.status, 'Continue', true, () => { }, 'content');
        }
    }

    request.onerror = function () {
        createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'content');
    };
}

function logout_device(button, token) {
    createModal('Log Out Device', 'Are you sure you want to log out this device?', 'Log Out Device', true, () => {
        let url = window.location.link + "/includes/php/settings.php";
        let request = new XMLHttpRequest();
        let body = new FormData;
        try {
            body.append('token', token);
            body.append('type', 'logout_device');
            request.open('POST', url, true);
            request.send(body);
        } catch (error) {
            createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
        }

        request.onload = function () {
            try {
                const result = JSON.parse(request.responseText);
                switch (result.code) {
                    case "success":
                        button.parentNode.remove();
                        break;
                    default:
                        createModal('A server error occured', result.error, 'Continue', true, () => { }, 'content');
                        break;
                }
            } catch (error) {
                createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
            }
        }

        request.onerror = function () {
            createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'content');
        };
    }, 'content');
}

function logout_devices() {
    createModal('Log Out Devices', 'Are you sure you want to log out all known devices?', 'Log Out All Known Devices', true, () => {
        submit2.innerHTML = '<span>...</span>';
        let url = window.location.link + "/includes/php/settings.php";
        let request = new XMLHttpRequest();
        let body = new FormData;
        try {
            body.append('type', 'logout_devices');
            request.open('POST', url, true);
            request.send(body);
        } catch (error) {
            createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
            submit2.innerText = oldText2;
        }

        request.onload = function () {
            try {
                const result = JSON.parse(request.responseText);
                switch (result.code) {
                    case "success":
                        document.querySelectorAll('.settings-logged-device').forEach(function (item,index) {
                            if(index > 0) {
                                item.remove();
                            }
                        });
                        break;
                    default:
                        createModal('A server error occured', result.error, 'Continue', true, () => { }, 'content');
                        break;
                }
                submit2.innerText = oldText2;
            } catch (error) {
                createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
                submit2.innerText = oldText2;
            }
        }

        request.onerror = function () {
            createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'content');
            submit2.innerText = oldText2;
        };
    }, 'content');
}

function generate_link() {
    createModal('Generate New Link', 'Are you sure you want to generate a new invite link?', 'Generate New Link', true, () => {
        submit3.innerHTML = '<span>...</span>';
        let url = window.location.link + "/includes/php/settings.php";
        let invite_url = document.getElementById('link_invite');
        let request = new XMLHttpRequest();
        let body = new FormData;
        try {
            body.append('type', 'generate_link');
            request.open('POST', url, true);
            request.send(body);
        } catch (error) {
            createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
            submit3.innerText = oldText3;
        }

        request.onload = function () {
            try {
                const result = JSON.parse(request.responseText);
                switch (result.code) {
                    case "success":
                        invite_url.value = window.location.link + "/join?ref=" + result.referrer;
                        break;
                    default:
                        createModal('A server error occured', result.error, 'Continue', true, () => { }, 'content');
                        break;
                }
                submit3.innerText = oldText3;
            } catch (error) {
                createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
                submit3.innerText = oldText3;
            }
        }

        request.onerror = function () {
            createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'content');
            submit3.innerText = oldText3;
        };
    }, 'content');
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

let markdowns = ['**', '*', '__', '~~', '`', '||'];
let textarea = document.getElementById('description');
document.querySelectorAll('.form-markdown').forEach((element, index) => {
    element.addEventListener("click", function () {
        highlightSelection(markdowns[index]);
    });
});