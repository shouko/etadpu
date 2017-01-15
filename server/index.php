<?php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');
if (!isset($_GET['func'])) {
    exit(json_encode([
        'result' => 'failed'
    ]));
}

switch ($_GET['func']) {
    case 'check':
        require 'routes/check.php';
    case 'fetch':
        require 'routes/fetch.php';
}