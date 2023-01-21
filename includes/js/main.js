window.location.link = window.location.protocol + '//' + window.location.host;
(() => {
function links() {
    let url = window.location.link + "/includes/php/data.php";
    let request = new XMLHttpRequest();
    let body = new FormData;
    body.append('type', 'links');
    request.open('POST', url, true);
    request.send(body);
    request.onload = function () {
        try {
            if (request.responseText != '') {
                const result = JSON.parse(request.responseText);
                switch (result.code) {
                    case "success":
                        window.location.link = result.link;
                        window.location.flink = result.static;
                        break;
                    default:
                        createModal('A server error occured', result.error, 'Continue', false, () => { }, 'content');
                        break;
                }
            }
        } catch (error) {
            createModal('A client error occured', error, 'Go back', false, () => { }, 'content');
        }
    }
}

links();

function heartbeat() {
    let url = window.location.link + "/includes/php/heartbeat.php";
    let request = new XMLHttpRequest();
    let body = new FormData;
    body.append('type', 'update');
    request.open('POST', url, true);
    request.send(body);
}

heartbeats = setInterval(() => {
    if (document.visibilityState === 'visible') {
        heartbeat();
    }
}, 120000);
heartbeat();

function worker() {
    let url = window.location.link + "/worker.js";
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register(url);
    }
}

worker();
})();

function createModal(titleText = '', descriptionText = '', buttonText = '', image = true, runAfterClose = () => {}, container, cancelDoesSomething = false) {
    container = document.getElementById(container);
    container.insertAdjacentHTML('beforeend','<div class="lb-form-modal-container"><div class="lb-form-modal"><span class="form-close">&times;</span></div></div>');
    container = document.getElementsByClassName("lb-form-modal")[0];
    if(image) {
        container.insertAdjacentHTML('beforeend', '<img class="lb-form-modal-icon" src="' + window.location.flink +'/img/logo.svg" alt="Darflen logo">');
    }
    if(titleText != '') {
        container.insertAdjacentHTML('beforeend','<h2>'+titleText+'</h2>');
    }
    if(descriptionText != '') {
        container.insertAdjacentHTML('beforeend','<p class="modal-description">'+descriptionText+'</p>');
    }
    if(buttonText != '') {
        container.insertAdjacentHTML('beforeend','<button class="lb-button form-close" type="button">'+buttonText+'</button>');
    }
    document.querySelectorAll(".lb-form-modal-container").forEach((item1) => {
        document.querySelectorAll('.form-close').forEach((item2) => {
            item2.addEventListener('click', (event) => {
                item1.remove();
                if (event.target.tagName == 'BUTTON' || cancelDoesSomething) {
                    runAfterClose();
                }
            });
        });
    });
    document.addEventListener("keyup", (event) => {
        if (event.key === "Escape") {
            document.getElementsByClassName("lb-form-modal-container")[0].remove();
            if (cancelDoesSomething) {
                runAfterClose();
            }
        }
    });
}

function noFormErrors(text,order) {
    order.forEach(element => {
        let label = document.getElementById(element + '-label');
        let input = document.getElementById(element);
        label.classList.remove("form-incorrect-input");
        input.classList.remove("form-incorrect-input");
        label.innerText = text[element]["name"];
    });
}

function updateForm(text,request,submit,success,order,redirect = true,errorAt = 'page-form') {
    try {
        const result = JSON.parse(request.responseText);
        switch (result.code) {
            case "ready":
                order.forEach(element => {
                    let label = document.getElementById(element + '-label');
                    let input = document.getElementById(element);
                    label.classList.add("form-incorrect-input");
                    input.classList.add("form-incorrect-input");
                    switch (result[element]) {
                        case "success":
                            label.classList.remove("form-incorrect-input");
                            label.innerText = text[element]["name"];
                            input.classList.remove("form-incorrect-input");
                            break;
                        case "empty":
                            label.innerHTML = text[element]["name"] + " - <span>" + text.empty + "</span>";
                            break;
                        default:
                            label.innerHTML = text[element]["name"] + " - <span>" + text[element][result[element]] + "</span>";
                            break;
                    }
                });
                return 'ready';
            case "success":
                noFormErrors(text, order);
                if(redirect) {
                    window.location.replace(success);
                } else {
                    return 'success';
                }
                break;
            default:
                noFormErrors(text, order);
                createModal('A server error occured', result.error, 'Go back', true, () => { }, errorAt);
                break;
        }
    } catch (error) {
        createModal('A client error occured', error, 'Go back', true, () => { }, errorAt);
        noFormErrors(text, order);
    }
}

function formError(text, container = 'form-container') {
    container = document.getElementById(container);
    container.insertAdjacentHTML('beforeend', '<div class="error-box-container"><img class="error-box-image" src="https://static.darflen.com/img/icons/interface/warning.svg" alt="Warning"><p class="error-box-text">' + text + '</p></div>');
}

function formErrorSystem(order, submit, errors, result) {
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
}

function formErrorSystem2(thing, item, heartText, hca, hcb, request, element, oldText) {
    if (thing.status != 200) {
        createModal('A server error occured', 'Server replied with: ' + thing.status, 'Continue', true, () => { }, 'content');
        heartText.innerText = oldText;
    } else {
        try {
            const result = JSON.parse(request.responseText);
            switch (result.code) {
                case "fail":
                    createModal('A server error occured', result.error, 'Continue', true, () => { }, 'content');
                    break;
                case "unlogged":
                    createModal('Not logged', 'You need to be logged to heart this ' + item + '.', 'Continue', true, () => { }, 'content');
                    heartText.innerText = oldText;
                    break;
                default:
                    /* profile-post-enabled */
                    if (element.classList.contains('profile-post-enabled')) {
                        element.classList.remove('profile-post-enabled');
                    } else {
                        element.classList.add('profile-post-enabled');
                    }
                    heartText.innerText = result.newCount;
                    if (hca != undefined) {
                        hca.innerText = result.totalCount;
                        hcb.innerText = result.totalCount;
                    }
                    break;
            }
        } catch (error) {
            createModal('A client error occured', error, 'Go back', true, () => { }, 'content');
            heartText.innerText = oldText;
        }
    }
}

function formErrorSystem3(order, submit, errors, result, parent) {
    order.forEach((element, index) => {
        let input = parent.querySelectorAll('#'+element)[0];
        input.classList.add("form-incorrect-input");
        switch (result[element]) {
            case "success":
                input.classList.remove("form-incorrect-input");
                if (parent.querySelectorAll('.error-box-container')[index] != undefined) {
                    parent.querySelectorAll('.error-box-container')[index].remove();
                }
                break;
            case "empty":
                if (parent.querySelectorAll('.error-box-container')[index] == undefined) {
                    formError(text[element].name + ': ' + text.empty,'subreply-form');
                    errors++
                } else {
                    parent.querySelectorAll('.error-box-container')[index].querySelectorAll(".error-box-text")[0].innerText = text[element].name + ': ' + text.empty;
                    errors++
                }
                break;
            default:
                if (parent.querySelectorAll('.error-box-container')[index] == undefined) {
                    formError(text[element].name + ': ' + text[element][result[element]],'subreply-form');
                    errors++
                } else {
                    parent.querySelectorAll('.error-box-container')[index].querySelectorAll(".error-box-text")[0].innerText = text[element].name + ': ' + text[element][result[element]];
                    errors++
                }
                break;
        }
    });
    while (parent.querySelectorAll('.error-box-container').length > errors) {
        parent.querySelectorAll('.error-box-container')[1].remove();
    }
}

function sanitizeHTML(text) {
    var element = document.createElement('div');
    element.innerText = text;
    return element.innerHTML;
}

function renderHTML(html,container) {
    let element = document.getElementById(container);
    element.insertAdjacentHTML('beforeend',(html));
}

function highlightSelection(type, tox = textarea) {
    if (document.activeElement === tox) {
        if (typeof tox.selectionStart == 'number' && typeof tox.selectionEnd == 'number') {
            var start = tox.selectionStart;
            var end = tox.selectionEnd;
            var selectedText = tox.value.slice(start, end);
            var before = tox.value.slice(0, start);
            var after = tox.value.slice(end);
            var text = before + type + selectedText + type + after;
            if (selectedText != '') {
                tox.value = text;
                tox.setSelectionRange(start + type.length, end + type.length);
                updateCounter();
            }
        }
    }
}

var _paq = window._paq = window._paq || [];
_paq.push(['trackPageView']);
_paq.push(['enableLinkTracking']);
(function () {
    var u = window.location.link + "/matomo/";
    _paq.push(['setTrackerUrl', u + 'matomo.php']);
    _paq.push(['setSiteId', '1']);
    var d = document,
        g = d.createElement('script'),
        s = d.getElementsByTagName('script')[0];
    g.async = true;
    g.src = u + 'matomo.js';
    s.parentNode.insertBefore(g, s);
})();