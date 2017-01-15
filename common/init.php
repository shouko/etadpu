<?php
require_once 'config.php';

function init() {
  if (!file_exists('config.php')) return 'Config file not found';
  if (file_exists(PRIKEY_FN) && file_exists(PUBKEY_FN)) return 0;

  $res = openssl_pkey_new([
    'digest_alg' => DIGEST_ALG,
    'private_key_bits' => RSA_KEY_SIZE,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
  ]);

  // get private key
  openssl_pkey_export($res, $prikey);

  // get public key
  $pubkey = openssl_pkey_get_details($res);
  $pubkey = $pubkey['key'];

  file_put_contents(PRIKEY_FN, $prikey);
  file_put_contents(PUBKEY_FN, $pubkey);
}