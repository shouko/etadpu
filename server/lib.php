<?php
function checkLicense($license) {
  return (strlen($license) == 25);
}

class AESHelper {
 private $key;
 private $iv;

 function __construct($secret) {
   if (is_null($secert)) return false;
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