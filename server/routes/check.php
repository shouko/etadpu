<?php
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

exit(json_encode([
  'files' => $files,
  'ts' => time()
]));