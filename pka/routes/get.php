<?php
if (!isset($_POST['hash']) || !isset($_POST['type'])) throw new Exception('Missing parameter');
$keystore = [[], []];
if (file_exists(KEYSTORE)) $keystore = json_decode(file_get_contents(KEYSTORE), 1);

$msg = new SecureMessage(PRIKEY_FN, PUBKEY_FN, $keystore[$_POST['type']][$_POST['hash']]);

exit($msg->pack(current($keystore[abs($_POST['type'] - 1)])));