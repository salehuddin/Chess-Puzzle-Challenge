<?php

// Test file upload handling
echo 'PHP Version: '.PHP_VERSION.PHP_EOL;
echo 'upload_tmp_dir: ['.ini_get('upload_tmp_dir').']'.PHP_EOL;
echo 'sys_temp_dir: ['.ini_get('sys_temp_dir').']'.PHP_EOL;
echo 'upload_max_filesize: '.ini_get('upload_max_filesize').PHP_EOL;
echo 'post_max_size: '.ini_get('post_max_size').PHP_EOL;
echo 'file_uploads: '.ini_get('file_uploads').PHP_EOL;

// Check if we can write to temp dir
$tmpDir = sys_get_temp_dir();
echo 'sys_get_temp_dir: '.$tmpDir.PHP_EOL;
echo 'is_writable: '.(is_writable($tmpDir) ? 'yes' : 'no').PHP_EOL;

// Test creating a temp file
$testFile = tempnam($tmpDir, 'test');
echo 'tempnam: '.$testFile.PHP_EOL;
echo 'file_exists: '.(file_exists($testFile) ? 'yes' : 'no').PHP_EOL;

$info = new SplFileInfo($testFile);
echo 'getRealPath: ';
var_dump($info->getRealPath());

unlink($testFile);
