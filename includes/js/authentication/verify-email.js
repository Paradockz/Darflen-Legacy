let order = ["tCode"];
let submit = document.getElementById('form-submit');
let rconfirm = document.getElementById('form-first-link');
let oldText = submit.innerText;
let oldText2 = rconfirm.innerText;
let text = {
    "empty": "This field is required",
    "tCode": {
        "name": "Code",
        "invalid": "Invalid code",
    },
};

let executing = false;
function form(form) {
    let url = window.location.link + "/includes/php/authentication/verify-email.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    if (!executing) {
        executing = true;
        submit.innerHTML = '<span>...</span>';
        try {
            body.append('tCode', data.get('tCode'));
            body.append('type', 'confirm');
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

function resend() {
    let url = window.location.link + "/includes/php/authentication/verify-email.php";
    let request = new XMLHttpRequest();
    let body = new FormData;
    if (!executing) {
        executing = true;
        rconfirm.innerText = 'Sending...';
        try {
            body.append('type', 'send');
            request.open('POST', url, true);
            request.send(body);
        } catch (error) {
            noFormErrors(text, order);
            createModal('A client error occured', error, 'Go back', true, () => { }, 'page-form');
            rconfirm.innerText = oldText2;
        }
    } else {
        return false;
    }

    request.onload = function () {
        if (this.status != 200) {
            createModal('A server error occured', 'Server replied with: ' + this.status, 'Continue', true, () => { }, 'content');
            element.children[1].innerText = oldText;
            executing = false;
        } else {
            createModal('Success!', 'A verification code has been sent to your mailbox.', 'Continue', true, () => { }, 'page-form');
        }
        rconfirm.innerText = oldText2;
    }

    request.onerror = function () {
        noFormErrors(text, order);
        createModal('A client error occured', 'Request failed', 'Go back', true, () => { }, 'page-form');
        rconfirm.innerText = oldText2;
        executing = false;
    };
}