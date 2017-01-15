<?php
if (!isset($_POST['payload'])) {
  throw new Exception('Missing parameters');
}

$msg = new SecureMessage(PRIKEY_FN, PUBKEY_FN);
$package = json_decode($msg->read($_POST['payload']), 1);

if (!isset($package['license']) || !checkLicense($package['license'])) throw new Exception('Invalid licence key');
if (!isset($package['file'])) throw new Exception('Missing parameters');
$filepath = UPDATE_DIR.'/'.str_replace('/', '', $package['file']);
if(!file_exists($filepath)) throw new Exception('Invalid update filename');

error_log("Delivering file: ".$filepath);

echo $msg->pack(file_get_contents($filepath));
$msg->free();
exit();