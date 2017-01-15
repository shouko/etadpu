<?php
require_once '../common/init.php';
require_once '../common/lib.php';
init();

function finish($msg) {
  echo $msg."\n";
  exit();
}

try {
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