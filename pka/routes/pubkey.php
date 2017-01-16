<?php
exit(json_encode([
  'pubkey' => file_get_contents(PUBKEY_FN)
]));