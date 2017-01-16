<?php
if (!isset($_POST['payload'])) throw new Exception('Missing parameter');
$prikey = openssl_pkey_get_private(file_get_contents(PRIKEY_FN));
openssl_private_decrypt(base64_decode($_POST['payload']), $result, $prikey);
exit(json_encode([
  'result' => base64_encode($result)
]));