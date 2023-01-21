let order = ["new-email","password"];
let submit = document.getElementById('form-submit');
let oldText = submit.innerText;
let text = {
    "empty": "This field is required",
    "new-email": {
        "name": "New Email",
        "length": "Must be 255 or fewer in length",
        "malformated": "Not a well formed email address",
        "invalid": "Invalid email address",
        "used": "Email is already registered"
    },
    "password": {
        "name": "Password",
        "invalid": "Invalid password",
    }
};

let executing = false;
function form(form) {
    let url = window.location.link + "/includes/php/authentication/change-email.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    if (!executing) {
        submit.innerHTML = '<span>...</span>';
        executing = true;
        try {
            body.append('new-email', data.get('new-email'));
            body.append('password', data.get('password'));
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
        if (updateForm(text, request, submit, window.location.link + '/settings/', order) == 'ready') {
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