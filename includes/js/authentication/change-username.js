let order = ["username","password"];
let submit = document.getElementById('form-submit');
let oldText = submit.innerText;
let text = {
    "empty": "This field is required",
    "username": {
        "name": "Username",
        "length": "Must be between 2 and 32 characters in length",
        "malformated": "Must only contain alphanumeric letters - and _",
        "copy": "Must not be the same as your current password"
    },
    "password": {
        "name": "Password",
        "invalid": "Invalid password",
    }
};

function form(form) {
    submit.innerHTML = '<span>...</span>';
    let url = window.location.link + "/includes/php/authentication/change-username.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    try {
        body.append('username', data.get('username'));
        body.append('password', data.get('password'));
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