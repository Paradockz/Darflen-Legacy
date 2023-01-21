<?php
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
error_reporting(E_ERROR);
try {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $database = prepare_database();
        $identifier = $_GET['u'] ?? 1;
        $_GET['p'] = $_GET['p'] == null ? 0 : $_GET['p'];
        if ($author = get_user_info_from_identifier($identifier)) {
            $total = $database->preparedQuery('SELECT count(id) as result FROM badges WHERE account = ?', [$author['id']])->fetch(PDO::FETCH_ASSOC)['result'];
            $paginator = 15;
            $paginator_count = floor($total / $paginator);
            if (isset($_GET['p'])) {
                $paginator_page = $_GET['p'];
            } else {
                $paginator_page = 0;
            }
            $follows = $database->preparedQuery('SELECT badge, JSON_VALUE(data,"$.miscellaneous.creation_time") AS time FROM badges WHERE account = ? LIMIT ?,?', [$author['id'], $paginator * $paginator_page, $paginator])->fetchAll(PDO::FETCH_ASSOC);
            $errors['code'] = 'success';
            foreach ($follows as $index => $follow) {
                $errors[$index] = [
                    "badge" => $follow['badge'],
                    "miscellaneous" => [
                        "creation_time" => $follow['time']
                    ]
                ];
            }
        } else {
            $errors = ['code' => 'fail', 'error' => '404', 'cause' => 'User does not exist'];
        }
    } else {
        // There's an error.
        $errors = ['code' => 'fail', 'error' => '400', 'cause' => 'Invalid request method'];
    }
} catch (Exception $error) {
    // There's an error.
    $errors = ['code' => 'fail', 'error' => '400', 'cause' => $error->getMessage()];
}
ob_clean();
echo json_encode($errors);