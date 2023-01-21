let order = ["current-password", "password","confirm-password"];
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
    "current-password": {
        "name": "Current Password",
        "invalid": "Invalid password",
    },
    "confirm-password": {
        "name": "Confirm Password",
        "invalid": "Must match the new password",
    }
};

function form(form) {
    submit.innerHTML = '<span>...</span>';
    let url = window.location.link + "/includes/php/authentication/change-password.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    try {
        body.append('current-password', data.get('current-password'));
        body.append('password', data.get('password'));
        body.append('confirm-password', data.get('confirm-password'));
        request.open('POST', url, true);
        request.send(body);
    } catch (error) {
        noFormErrors(text, order);
        createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
        submit.innerText = oldText;
    }

    request.onload = function () {
        updateForm(text, request, submit, window.location.link + '/settings/', order);
        request.onerror = function () {
            noFormErrors(text, order);
            createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'page-form');
        };
        submit.innerText = oldText;
    }
}