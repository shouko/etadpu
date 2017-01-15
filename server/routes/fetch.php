<?php
if (!isset($_POST['payload'])) {
  throw new Exception('Missing parameters');
}

$prikey = openssl_pkey_get_private(file_get_contents('server.key'), SERVER_KEY_PASS);
openssl_private_decrypt(base64_decode($_POST['payload']), $payload, $prikey);

$payload = json_decode($payload, 1);

if (!isset($payload['license']) || !checkLicense($payload['license'])) throw new Exception('Invalid licence key');
if (!isset($payload['file'])) throw new Exception('Missing parameters');
$filepath = SERVER_UPDATE_DIR.basename(realpath($payload['file']));
if(!file_exists($filepath)) throw new Exception('Invalid update filename');

$aes_secret = openssl_random_pseudo_bytes(256);
$AES = new AESHelper($aes_secret);
openssl_private_encrypt($aes_secret, $aes_secret_crypted, $prikey);

exit([
  'key' => base64_encode($aes_secret_crypted),
  'content' => base64_encode($AES->encrypt(file_get_contents($filepath))),
  'ts' => time()
]);