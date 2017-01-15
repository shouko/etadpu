<?php
if (!isset($_POST['payload'])) {
  throw new Exception('Missing parameters');
}

$payload = json_decode($_POST['payload']);

$prikey = openssl_pkey_get_private(file_get_contents('server.key'), SERVER_KEY_PASS);
if (!isset($payload['key'])) throw new Exception('Missing AES secret');
openssl_private_decrypt(base64_decode($payload['key']), $aes_secret, $prikey);
$AES = new AESHelper($aes_secret);

$content = json_decode($AES->decrypt($payload['content']), 1);

if (!isset($content['license']) || !checkLicense($content['license'])) throw new Exception('Invalid licence key');
if (!isset($content['file'])) throw new Exception('Missing parameters');
$filepath = SERVER_UPDATE_DIR.basename(realpath($content['file']));
if(!file_exists($filepath)) throw new Exception('Invalid update filename');
if (!isset($content['pubkey'])) throw new Exception('Missing client public key');

$aes_secret = openssl_random_pseudo_bytes(256);
$AES = new AESHelper($aes_secret);
$pubkey = openssl_pkey_get_public($content['pubkey']);
openssl_public_encrypt($aes_secret, $aes_secret_crypted, $pubkey);

exit(json_encode([
  'key' => base64_encode($aes_secret_crypted),
  'content' => base64_encode($AES->encrypt(file_get_contents($filepath))),
  'ts' => base64_encode($AES->encrypt(time()))
]));