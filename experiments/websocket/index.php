<?php
require_once DOCUMENT_ROOT . '\vendor\autoload.php';

$server = new WebSocket\Server();
while ($server->accept()) {
    try {
        $message = $server->receive();
        $server->text("HELLO WORLD IT WORKS!!!");
        if ($message->hasContent() && $message->getContent() == "STOP") {
            break;
        }
    } catch (\WebSocket\ConnectionException $e) {
        //error_log("AHHHH: ".$e);
        break;
    }
    sleep(1);
}
$server->close();