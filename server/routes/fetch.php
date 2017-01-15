<?php
if (!isset($_POST['payload'])) {
  throw new Exception('Missing parameters');
}

$msg = new SecureMessage('server.key', 'server.pub', SERVER_KEY_PASS);
$package = $msg->read($_POST['payload']);

if (!isset($package['license']) || !checkLicense($package['license'])) throw new Exception('Invalid licence key');
if (!isset($package['file'])) throw new Exception('Missing parameters');
$filepath = SERVER_UPDATE_DIR.basename(realpath($package['file']));
if(!file_exists($filepath)) throw new Exception('Invalid update filename');
if (!isset($package['pubkey'])) throw new Exception('Missing client public key');

echo $msg->pack(file_get_contents($filepath));
$msg->free();
exit();