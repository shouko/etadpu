<?php
function checkLicense($license) {
  return (strlen($license) == 25);
}

class AESHelper {
 private $key;
 private $iv;

 function __construct($secret) {
   if (is_null($secret)) return false;
   $hash = hash('SHA384', $secret, true);
   $this->key = substr($hash, 0, 32);
   $this->iv = substr($hash, 32, 16);
 }

 public function encrypt($data) {
   $padding = 16 - (strlen($data) % 16);
   $data .= str_repeat(chr($padding), $padding);
   return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $data, MCRYPT_MODE_CBC, $this->iv);
 }

 public function decrypt($data) {
   $data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $data, MCRYPT_MODE_CBC, $this->iv);
   $padding = ord($data[strlen($data) - 1]);
   return substr($data, 0, -$padding);
 }
}

class SecureMessage {
  private $prikey;
  private $pubkey;
  private $pubkey_str;
  private $pubkey_remote;
  private $sign_algo;
  private $verify_algo;

  function __construct($prikey_fn, $pubkey_fn, $pubkey_remote = NULL, $verify_algo = 'sha256WithRSAEncryption', $sign_algo = 'sha256') {
    $this->prikey = openssl_pkey_get_private(file_get_contents($prikey_fn));
    $this->pubkey_str = file_get_contents($pubkey_fn);
    $this->pubkey = openssl_pkey_get_public($this->pubkey_str);
    $this->pubkey_remote = $pubkey_remote;
    if (!is_null($pubkey_remote)) $this->set_pubkey_remote($pubkey_remote);
    $this->sign_algo = $sign_algo;
    $this->verify_algo = $verify_algo;
    while ($msg = openssl_error_string()) error_log($msg);
  }

  function read($payload) {
    $payload = json_decode($payload, 1);
    if (isset($payload['result']) && $payload['result'] == 'failed') throw new Exception('Request failed. '.$payload['message']);
    if (!isset($payload['aeskey'])) throw new Exception('Missing AES secret');
    if (!isset($payload['signature'])) throw new Exception('Missing signature');
    if (is_null($this->pubkey_remote)) {
      if (!isset($payload['pubkey'])) throw new Exception('Missing remote public key');
      $this->set_pubkey_remote($payload['pubkey']);
    }

    $verify = openssl_verify(base64_decode($payload['package']), base64_decode($payload['signature']), $this->pubkey_remote, $this->verify_algo);
    if ($verify !== 1) throw new Exception('Signature mismatch');

    openssl_private_decrypt(base64_decode($payload['aeskey']), $aes_secret, $this->prikey);
    $AES = new AESHelper($aes_secret);
    while ($msg = openssl_error_string()) error_log($msg);

    return $AES->decrypt(base64_decode($payload['package']));
  }

  function pack($package, $include_local_pubkey = false) {
    $aes_secret = openssl_random_pseudo_bytes(256);
    $AES = new AESHelper($aes_secret);
    openssl_public_encrypt($aes_secret, $aes_secret_crypted, $this->pubkey_remote);

    $package_encrypted = $AES->encrypt($package);
    openssl_sign($package_encrypted, $signature, $this->prikey, $this->sign_algo);

    $payload = [
      'aeskey' => base64_encode($aes_secret_crypted),
      'signature' => base64_encode($signature),
      'package' => base64_encode($package_encrypted)
    ];

    if ($include_local_pubkey) $payload['pubkey'] = $this->pubkey_str;

    error_log(json_encode($payload));
    while ($msg = openssl_error_string()) error_log($msg);
    return json_encode($payload);
  }

  function free() {
    openssl_pkey_free($this->prikey);
    openssl_pkey_free($this->pubkey);
    openssl_pkey_free($this->pubkey_remote);
  }

  private function set_pubkey_remote($pubkey_remote) {
    $this->pubkey_remote = openssl_pkey_get_public($pubkey_remote);
  }
}
