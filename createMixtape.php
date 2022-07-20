<?php
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/authUtil.php');

$name = $_POST['name'];
$ids = $_POST['tracks'];
$imageNum = $_POST['image'];

$bytes = openssl_random_pseudo_bytes(16);
$code = bin2hex($bytes);
session_id($code);
session_save_path(SESSION_DIR);
//error_log("Session (c) " . $code);

$sessionStr = '';
$sessionStr .= $ids . ';';
$sessionStr .= $name . ';';
$sessionStr .= $imageNum;

//$file = fopen('/mix_files/' . $fileName, "w");
//fwrite($file, $ids . PHP_EOL);
//fwrite($file, $name . PHP_EOL);
//fwrite($file, $imageNum);
//fclose($file);
session_start();
$_SESSION[$code] = $sessionStr;

$redirect = 'https://timstwitterlisteningparty.com/pages/mixtape.html';

if (strpos($_SERVER['DOCUMENT_ROOT'], 'lbavyjjs') > 0) {
    $redirect = 'http://sk7software.co.uk/listeningparty/main/pages/mixtape.html';
}
authoriseOther($code, $redirect, 'playlist-modify-private ugc-image-upload');
