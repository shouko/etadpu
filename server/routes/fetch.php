<?php
if (!isset($_POST['payload'])) {
  throw new Exception('Missing parameters');
}

$msg = new SecureMessage(PRIKEY_FN, PUBKEY_FN, PRIKEY_PASS);
$package = $msg->read($_POST['payload']);

if (!isset($package['license']) || !checkLicense($package['license'])) throw new Exception('Invalid licence key');
if (!isset($package['file'])) throw new Exception('Missing parameters');
$filepath = UPDATE_DIR.basename(realpath($package['file']));
if(!file_exists($filepath)) throw new Exception('Invalid update filename');
if (!isset($package['pubkey'])) throw new Exception('Missing client public key');

echo $msg->pack(file_get_contents($filepath));
$msg->free();
exit();