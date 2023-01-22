let order = ["email","username","password","birthdate"];
let submit = document.getElementById('form-submit');
let oldText = submit.innerText;
let text = {
    "empty": "This field is required",
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
    "birthdate": {
        "name": "Birthdate",
        "length": "Must have a valid birth date",
        "incomplete": "Must be completely filled",
        "young": "You must be 13 years or older to use this"
    }
}

let executing = false;
function form(form) {
    submit.innerHTML = '<span>...</span>';
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    let invite = urlParams.has('ref') ? urlParams.get('ref') : '';
    let url = window.location.link + "/includes/php/authentication/join.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    if (!executing) {
        executing = true;
        try {
            body.append('username', data.get('username'));
            body.append('email', data.get('email'));
            body.append('password', data.get('password'));
            body.append('day', data.get('day'));
            body.append('month', data.get('month'));
            body.append('year', data.get('year'));
            body.append('invite', invite);
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
        if (updateForm(text, request, submit, window.location.link + '/explore/', order)) {
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