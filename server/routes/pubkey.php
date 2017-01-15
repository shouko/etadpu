<?php
exit(json_encode([
  'pubkey' => file_get_contents(PRIKEY_FN)
]));