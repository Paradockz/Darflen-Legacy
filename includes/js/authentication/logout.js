function logout(form) {
    createModal('Log Out', 'Are you sure you want to log out?', 'Log Out', true, () => {
        let url = window.location.link + "/includes/php/authentication/logout.php";
        let request = new XMLHttpRequest();
        request.open('POST', url, true);
        request.send();
        request.onload = function () {
            window.location.replace(window.location.link + '/');
        }
    }, 'content');
}
