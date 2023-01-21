<?php
/* it"s global */ 
if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
    $_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_CF_CONNECTING_IP"];
}
$_SERVER["HTTP_SUBDOMAIN"] = explode('.', $_SERVER["HTTP_HOST"])[0];
if (count(explode('.', $_SERVER["HTTP_HOST"])) < 3) {
    $_SERVER["HTTP_SUBDOMAIN"] = '';
}
define("DOCUMENT_ROOT", rtrim($_SERVER["DOCUMENT_ROOT"], $_SERVER["HTTP_SUBDOMAIN"]));
$https = $_SERVER["SERVER_PORT"] == 443 ? "https://" : "http://";
define("ROOT_LINK", $https . ltrim($_SERVER["HTTP_HOST"], $_SERVER["HTTP_SUBDOMAIN"] . '.'));
define("STATIC_LINK", $https . "static." . ltrim($_SERVER["HTTP_HOST"], $_SERVER["HTTP_SUBDOMAIN"] . '.'));
define("API_LINK", $https . "api." . ltrim($_SERVER["HTTP_HOST"], $_SERVER["HTTP_SUBDOMAIN"] . '.'));

/* If this breaks, everything else breaks and nothing would load! */
if (!str_contains($_SERVER["DOCUMENT_ROOT"], "apps") && isset($_SERVER["HTTP_HOST"]) && !empty($_SERVER["HTTP_HOST"])) {
    /* Prepare everything to make the page works */
    define("CONFIG", parse_ini_file(file_exists(DOCUMENT_ROOT . "\configs2.ini") ? DOCUMENT_ROOT."\configs2.ini" : DOCUMENT_ROOT . "\configs.ini", true));
    require_once DOCUMENT_ROOT . "/includes/php/core.php";
    log_request();
    if (!in_array($_SERVER["HTTP_SUBDOMAIN"], ["api", "experiments", "static"]) && $_SERVER["REQUEST_METHOD"] != "POST") {
        if ($_SERVER["HTTP_SUBDOMAIN"] == "www") {
            header("Location: " . ROOT_LINK);
        }
        /* Maintenance mode */
        if (CONFIG["website"]["maintenance"] == true && !in_array($_SERVER["REQUEST_URI"], ["/maintenance/","/internal/"])) {
            header("Location: " . ROOT_LINK . "/maintenance/");
        }
        $database = prepare_database();
        if (isset($_COOKIE["token"]) && check_token_validity($_COOKIE["token"])) {
            $token = $_COOKIE["token"];
            $user = get_user_info_from_token($token);
            if ($database->preparedQuery("SELECT count(id) AS result FROM bans WHERE user = ?", [$user["identifier"]])->fetch(PDO::FETCH_ASSOC)["result"] > 0 && !in_array($_SERVER["REQUEST_URI"], ["/ban/", "/notifications/", "/terms/", "/privacy/", "/contact/", "/settings/"])) {
                header("Location: ".ROOT_LINK."/ban/");
            }
    
            /* Setup rate limit bombs */
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION["limits"])) {
                $_SESSION["limits"] = [];
                $_SESSION["limits"]["time"] = [];
            }
        }
    }
}