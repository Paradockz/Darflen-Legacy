<?php
require_once(DOCUMENT_ROOT . '\includes\php\library.php');


ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
$image = $_FILES["images"];

if (count($image) > 0) {
$upload = new Upload("T:/darflen-portable/htdocs/experiments/test");
$upload::file($image);
}
// T:/darflen-portable/htdocs/experiments/test
}