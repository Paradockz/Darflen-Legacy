let order = ["email","password"];
let submit = document.getElementById('form-submit');
let fpsubmit = document.getElementById("form-inside-first-link");
let oldText = submit.innerText;
let oldText2 = fpsubmit.innerText;
let login = true;
let text = {
    "empty": "This field is required",
    "email": {
        "name": "Email",
        "invalid": "Invalid email",
    },
    "password": {
        "name": "Password",
        "invalid": "Invalid password",
    }
};

let executing = false;
function form(form) {
    if(login) {
        login = false;
        let url = window.location.link + "/includes/php/authentication/login.php";
        let request = new XMLHttpRequest();
        let data = new FormData(form);
        let body = new FormData;
        if (!executing) {
            executing = true;
            submit.innerHTML = '<span>...</span>';
            try {
                body.append('email', data.get('email'));
                body.append('password', data.get('password'));
                request.open('POST', url, true);
                request.send(body);
            } catch (error) {
                noFormErrors(text, order);
                createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
                submit.innerText = oldText;
                login = true;
            }
        } else {
            return false;
        }

        request.onload = function () {
            if (updateForm(text, request, submit, window.location.link + '/', order) == 'ready') {
                executing = false;
            }
            request.onerror = function () {
                noFormErrors(text, order);
                createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'page-form');
                executing = false;
            };
            submit.innerText = oldText;
            login = true;
        }
    }
}

function forgot() {
    form = document.getElementsByTagName('form')[0];
    let url = window.location.link + "/includes/php/authentication/forgot-password.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    if (!executing) {
        executing = true;
        fpsubmit.innerText = 'Sending...';
        try {
            body.append('email', data.get('email'));
            request.open('POST', url, true);
            body.append('type', 'send');
            request.send(body);
        } catch (error) {
            noFormErrors(text, order);
            createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
            fpsubmit.innerText = oldText2;
        }
    } else {
        return false;
    }

    request.onload = function () {
        if (updateForm(text, request, submit, window.location.link + '/password/reset/', ['email'], false) == 'success') {
            createModal('Password recovery email sent', 'We sent instructions to change your password to ' + data.get('email') + ', please check both your inbox and spam folder.', 'Continue', true, () => { }, 'page-form');
            executing = false;
        }
        fpsubmit.innerText = oldText2;
    }

    request.onerror = function () {
        noFormErrors(text, order);
        createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'page-form');
        executing = false;
    };
}