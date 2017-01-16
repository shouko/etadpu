<?php
require_once '../common/init.php';
require_once '../common/lib.php';
init();
pka_reg();

function finish($msg) {
  echo $msg."\n";
  exit();
}

try {
  echo "Fetching PKA public key\n";
  $pka_pubkey = json_decode(file_get_contents(PKA_ENDPOINT.'pubkey'), 1)['pubkey'];
  $msg = new SecureMessage(PRIKEY_FN, PUBKEY_FN, $pka_pubkey);

  echo "Fetching update server public key from PKA\n";
  $opts = [
    'http' => [
      'method' => 'POST',
      'header' => 'Content-type: application/x-www-form-urlencoded',
      'content' => http_build_query([
        'hash' => hash('sha256', file_get_contents(PUBKEY_FN)),
        'type' => DEVICE_TYPE
      ])
    ]
  ];
  $response = file_get_contents(PKA_ENDPOINT.'get', false, stream_context_create($opts));
  $server_pubkey_pka = $msg->read($response);

  echo "Verifying update server\n";
  $n = openssl_random_pseudo_bytes(50);
  openssl_public_encrypt($n, $challenge_string, openssl_pkey_get_public($server_pubkey_pka));

  $opts = [
    'http' => [
      'method' => 'POST',
      'header' => 'Content-type: application/x-www-form-urlencoded',
      'content' => http_build_query([
        'payload' => base64_encode($challenge_string)
      ])
    ]
  ];
  $response = file_get_contents(SERVER_ENDPOINT.'challenge', false, stream_context_create($opts));
  echo "Original: ".base64_encode($n)."\n";
  echo "Response: ".json_decode($response, 1)['result']."\n";
  if (base64_decode(json_decode($response, 1)['result']) !== $n) throw new Exception('Server challenge failed');

  echo "Server challenge success\n";

  if (!file_exists(UPDATE_DIR)) mkdir(UPDATE_DIR);

  $files = [];
  if (is_dir(UPDATE_DIR)){
    if ($dh = opendir(UPDATE_DIR)){
        while (($file = readdir($dh)) !== false){
            if ($file[0] != '.') $files[] = $file;
        }
        closedir($dh);
    }
  }
  rsort($files);

  echo "Checking for updates\n";
  $check = json_decode(file_get_contents(SERVER_ENDPOINT.'check'), 1);

  if (!isset($check['files']) || count($check['files']) == 0) finish('No updates available');
  if (count($files) != 0 && strcmp($files[0], $check['files'][0]) >= 0) finish('Already updated to latest');

  echo "Fetching server public key\n";
  $server_pubkey = json_decode(file_get_contents(SERVER_ENDPOINT.'pubkey'), 1)['pubkey'];

  $msg = new SecureMessage(PRIKEY_FN, PUBKEY_FN, $server_pubkey);

  $opts = [
    'http' => [
      'method' => 'POST',
      'header' => 'Content-type: application/x-www-form-urlencoded',
      'content' => http_build_query([
        'payload' => $msg->pack(json_encode([
          'file' => $check['files'][0],
          'license' => LICENSE
        ]), 1)
      ])
    ]
  ];

  echo "Requesting update file ".$check['files'][0]."\n";
  $response = file_get_contents(SERVER_ENDPOINT.'fetch', false, stream_context_create($opts));
  $package = $msg->read($response);
  file_put_contents(UPDATE_DIR.'/'.$check['files'][0], $package);
} catch (Exception $e) {
  echo $e->getMessage()."\n";
}