let order = ["password"];
let submit = document.getElementById('form-submit');
let oldText = submit.innerText;
let text = {
    "empty": "This field is required",
    "password": {
        "name": "Password",
        "invalid": "Invalid password"
    },
};

function form(form) {
    submit.innerHTML = '<span>...</span>';
    let url = window.location.link + "/includes/php/settings.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    try {
        createModal('Account removal', 'Are you sure that you want to delete your account?', 'Continue', true, () => {
            createModal('Account removal', 'This is your last warning. Deleting your account is a one-way journey. You can not recover anything from your account after doing it.', 'Continue', true, () => {
                body.append('type', 'delete');
                body.append('password', data.get('password'));
                request.open('POST', url, true);
                request.send(body);
            }, 'content');
        }, 'content');
    } catch (error) {
        noFormErrors(text, order);
        createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
    }

    request.onload = function () {
        updateForm(text, request, submit, window.location.link + '/settings/delete/', order);
        request.onerror = function () {
            noFormErrors(text, order);
            createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'page-form');
        };
        submit.innerText = oldText;
    }
}