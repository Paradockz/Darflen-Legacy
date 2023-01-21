/* Freakin Yandare Simulator code */
function follow(element,id) {
    let oldText = element.children[1].innerText;
    element.children[1].innerText = '...';
    let flc = document.getElementById('profile-stats').children[1].children[0].children[1];
    let flcb = document.getElementById('profile-banner-stats').children[1].children[0].children[1];
    let url = window.location.link + "/includes/php/profile.php";
    let body = 'id=' + id;
    let request = new XMLHttpRequest();
    try {
        /* Crappy server code. */
        request.open('POST', url, true);
        request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        request.send(body);
    } catch (error) {
        element.children[1].innerText = oldText;
        createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
    }
    request.onload = function () {
        if (this.status != 200) {
            createModal('A server error occured', 'Server replied with: ' + this.status, 'Continue', true, () => { }, 'content');
            element.children[1].innerText = oldText;
        } else {
            try {
                const result = JSON.parse(request.responseText);
                switch (result.code) {
                    case "fail":
                        element.children[1].innerText = oldText;
                        createModal('A server error occured', result.error, 'Continue', true, () => { }, 'content');
                        break;
                    default:
                        if (element.id == 'profile-follow-button') {
                            element.id = 'profile-follow-button-followed';
                            element.children[1].innerText = 'Unfollow';
                            element.children[0].src = window.location.protocol + '//static.' + window.location.host + '/img/icons/interface/user-remove.svg';
                            flc.innerText = result.newCount;
                            flcb.innerText = result.newCount;
                        } else {
                            element.id = 'profile-follow-button';
                            element.children[1].innerText = 'Follow';
                            element.children[0].src = window.location.protocol + '//static.' + window.location.host + '/img/icons/interface/user-add.svg';
                            flc.innerText = result.newCount;
                            flcb.innerText = result.newCount;
                        }
                        break;
                }
            } catch (error) {
                element.children[1].innerText = oldText;
                createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
            }
        }
    }
    request.onerror = function () {
        createModal('A client error occured', 'Request failed', 'Continue', true, () => { }, 'content');
    };
}

function report_profile(form, id) {
    let order = ["textarea", "reason"];
    let submit = document.getElementById('form-submit');
    let oldText = submit.innerText;
    submit.innerHTML = '<span>...</span>';
    let url = window.location.link + "/includes/php/reports.php";
    let request = new XMLHttpRequest();
    let data = new FormData(form);
    let body = new FormData;
    let text = {
        "empty": "This field is required",
        "textarea": {
            "name": "Profile",
            "length": "Must be 1024 or lower in length"
        },
        "reason": {
            "name": "Reason",
            "invalid": "Invalid value"
        }
    }
    try {
        body.append('textarea', data.get('textarea'));
        body.append('reason', data.get('reason'));
        body.append('id', id);
        body.append('type', 'users');
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
                        order.forEach((element, index) => {
                            let input = document.getElementById(element);
                            input.classList.add("form-incorrect-input");
                            switch (result[element]) {
                                case "success":
                                    input.classList.remove("form-incorrect-input");
                                    if (document.querySelectorAll('.error-box-container')[index] != undefined) {
                                        document.querySelectorAll('.error-box-container')[index].remove();
                                    }
                                    break;
                                case "empty":
                                    if (document.querySelectorAll('.error-box-container')[index] == undefined) {
                                        formError(text[element].name + ': ' + text.empty);
                                        errors++
                                    } else {
                                        document.querySelectorAll('.error-box-container')[index].getElementsByClassName("error-box-text")[0].innerText = text[element].name + ': ' + text.empty;
                                        errors++
                                    }
                                    break;
                                default:
                                    if (document.querySelectorAll('.error-box-container')[index] == undefined) {
                                        formError(text[element].name + ': ' + text[element][result[element]]);
                                        errors++
                                    } else {
                                        document.querySelectorAll('.error-box-container')[index].getElementsByClassName("error-box-text")[0].innerText = text[element].name + ': ' + text[element][result[element]];
                                        errors++
                                    }
                                    break;
                            }
                        });
                        while (document.querySelectorAll('.error-box-container').length > errors) {
                            document.querySelectorAll('.error-box-container')[1].remove();
                        }
                        submit.innerText = oldText;
                        break;
                    case 'success':
                        setTimeout(() => {
                            window.location.replace(window.location.link + '/users/' + result.profile);
                        }, 1500);
                        submit.innerText = 'Profile has been reported';
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