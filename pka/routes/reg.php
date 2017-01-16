<?php
if (!isset($_POST['pubkey']) || !isset($_POST['type'])) throw new Exception('Missing parameter');
$keystore = [[], []];
if (file_exists(KEYSTORE)) $keystore = json_decode(file_get_contents(KEYSTORE), 1);
$hash = hash('sha256', $_POST['pubkey']);
$keystore[$_POST['type']][$hash] = $_POST['pubkey'];

$msg = new SecureMessage(PRIKEY_FN, PUBKEY_FN, $_POST['pubkey']);

file_put_contents(KEYSTORE, json_encode($keystore));

exit(json_encode([
  'result' => 'success'
]));