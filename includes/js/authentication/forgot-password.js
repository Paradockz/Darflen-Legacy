let order = ["password", "confirm-password"];
let submit = document.getElementById('form-submit');
let oldText = submit.innerText;
let text = {
    "empty": "This field is required",
    "password": {
        "name": "Password",
        "length": "Must be between 6 and 255 characters in length",
        "malformated": "Must be more complex",
        "copy": "Must not be the same as your current password"
    },
    "confirm-password": {
        "name": "Confirm New Password",
        "invalid": "Must match the new password",
    }
};

let executing = false;
function form(form) {
    let url = window.location.link + "/includes/php/authentication/forgot-password.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;

    if (!executing) {
        submit.innerHTML = '<span>...</span>';
        executing = true;
        try {
            body.append('password', data.get('password'));
            body.append('confirm-password', data.get('confirm-password'));
            body.append('type','confirm');
            let token = new URLSearchParams(window.location.search);
            token = token.get('q');
            body.append('token',token);
            request.open('POST', url, true);
            request.send(body);
        } catch (error) {
            noFormErrors(text, order);
            createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
            submit.innerText = oldText;
        }
    } else {
        return false;
    }

    request.onload = function () {
        if (updateForm(text, request, submit, window.location.link + '/login/', order) == 'ready') {
            executing = false;
        }
        request.onerror = function () {
            noFormErrors(text, order);
            createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'page-form');
            executing = false;
        };
        submit.innerText = oldText;
    }
}