<?php
if (!file_exists('config.php')) exit('Config file not found');
if (file_exists('server.key') && file_exists('server.pub')) exit(0);

require_once 'config.php';

$res = openssl_pkey_new([
  'digest_alg' => SERVER_DIGEST_ALG,
  'private_key_bits' => SERVER_RSA_KEY_SIZE,
  'private_key_type' => OPENSSL_KEYTYPE_RSA,
]);

// get private key
openssl_pkey_export($res, $privkey, SERVER_KEY_PASS);

// get public key
$pubkey = openssl_pkey_get_details($res);
$pubkey = $pubkey['key'];

file_put_contents('server.key', $privkey);
file_put_contents('server.pub', $pubkey);