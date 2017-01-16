<?php
require_once 'config.php';
require_once '../common/lib.php';
header('Content-Type: application/json; charset=utf-8');

try {
  if (!isset($_GET['func'])) {
    throw new Exception('Missing parameter func');
  }
  switch ($_GET['func']) {
    case 'reg':
      require 'routes/reg.php';
    case 'get':
      require 'routes/get.php';
    case 'pubkey':
      require 'routes/pubkey.php';
  }
} catch (Exception $e) {
    exit(json_encode([
        'result' => 'failed',
        'message' => $e->getMessage()
    ]));
}
