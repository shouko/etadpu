<?php
require_once 'config.php';
$res = openssl_pkey_new([
  'digest_alg' => SERVER_DIGEST_ALG,
  'private_key_bits' => SERVER_RSA_KEY_SIZE,
  'private_key_type' => OPENSSL_KEYTYPE_RSA,
]);

// Get private key
openssl_pkey_export($res, $privkey, SERVER_KEY_PASS);

// Get public key
$pubkey=openssl_pkey_get_details($res);
$pubkey=$pubkey["key"];

var_dump($privkey);

var_dump($pubkey);