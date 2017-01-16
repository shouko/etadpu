<?php
require_once 'config.php';

function init() {
  if (!file_exists('config.php')) return 'Config file not found';
  if (file_exists(PRIKEY_FN) && file_exists(PUBKEY_FN)) return 0;

  echo 'Generating key pair';
  $res = openssl_pkey_new([
    'digest_alg' => DIGEST_ALG,
    'private_key_bits' => RSA_KEY_SIZE,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
  ]);

  // get private key
  openssl_pkey_export($res, $prikey);

  // get public key
  $csr = openssl_csr_new([], $res);
  $cert = openssl_csr_sign($csr, null, $res, 3650);
  openssl_x509_export($cert, $cert);

  echo 'Exporting key pair';
  file_put_contents(PRIKEY_FN, $prikey);
  file_put_contents(PUBKEY_FN, $cert);

}

function pka_reg() {
  $opts = [
    'http' => [
      'method' => 'POST',
      'header' => 'Content-type: application/x-www-form-urlencoded',
      'content' => http_build_query([
        'pubkey' => file_get_contents(PUBKEY_FN),
        'type' => DEVICE_TYPE
      ])
    ]
  ];

  echo "Registering to PKA\n";
  $response = file_get_contents(PKA_ENDPOINT.'reg', false, stream_context_create($opts));
}