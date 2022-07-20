<?php
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/authUtil.php');

$code = $_GET['code'];
$filePrefix = $_GET['state'];
session_id($filePrefix);
session_save_path('/tmp');
session_start();
//error_log("Session (m) " . $filePrefix);
//error_log('Stored data: ' . $_SESSION[$filePrefix]);
//exit(0);

$redirect = 'https://timstwitterlisteningparty.com/pages/mixtape.html';
if (strpos($_SERVER['DOCUMENT_ROOT'], 'lbavyjjs') > 0) {
    $redirect = 'http://sk7software.co.uk/listeningparty/main/pages/mixtape.html';
}
$tokens = getToken($code, $filePrefix, $redirect);
$spotifyAccessToken = $tokens['access_token'];

$spotifyURL = 'https://api.spotify.com/v1/me';

$headers = array('Authorization: Bearer ' . $spotifyAccessToken);

$cURLConnection = curl_init();
curl_setopt($cURLConnection, CURLOPT_URL, $spotifyURL);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($cURLConnection);
checkStatus($cURLConnection, $result);
curl_close($cURLConnection);

$userInfo = json_decode($result, TRUE);
$userId = $userInfo['id'];

// Get track ids
//$fileName = '/mix_files/' . $filePrefix . '.txt';
//$contents = file_get_contents($fileName);
//
//if ($contents === FALSE) {
//    http_response_code(500);
//    echo '{"error": "Could not find track details"}';
//    die();
//}
$lines = explode(';', $contents);
$ids = $lines[0];
$name = $lines[1];
$imageNum = $lines[2];
$imageFile = '';
//unlink($fileName);

if ($imageNum == 1) {
    $imageFile = 'ttlp-tape.jpg';
} else if ($imageNum == 2) {
    $imageFile = 'timoji.jpg';
}

if (strlen($name) == 0) {
    $name = '#TimsTwitterListeningParty Mixtape';
}

$trackUris = explode(',', $ids);
shuffle($trackUris);
for($i=0; $i<sizeof($trackUris); $i++) {
    $trackUris[$i] = 'spotify:track:' . $trackUris[$i];
}

$playlistId = '';
$createURI = 'https://api.spotify.com/v1/users/' . $userId . '/playlists';
$playlistName = $name;
$playlist = array(
    'name' => $playlistName,
    'public' => false
);

$headers = array('Authorization: Bearer ' . $spotifyAccessToken,
    	 	 'Content-Type: application/json');

$cURLConnection = curl_init($createURI);
curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, json_encode($playlist));
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($cURLConnection);
checkStatus($cURLConnection, $result);
curl_close($cURLConnection);

$json_a = json_decode($result, TRUE);
$playlistId = $json_a['id'];
$return = array('playlistId' => $playlistId);

if ($imageNum < 3) {
    // Set image
	$setImageURI = 'https://api.spotify.com/v1/playlists/' . $playlistId . '/images';
	$headers = array('Authorization: Bearer ' . $spotifyAccessToken,
			 'Content-Type: image/jpeg');

	$cURLConnection = curl_init($setImageURI);
	curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, base64_encode(file_get_contents('./img/' . $imageFile)));
	curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($cURLConnection, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($cURLConnection);
	curl_close($cURLConnection);
}

// Add Tracks
$addTracksURI = 'https://api.spotify.com/v1/users/'. $userId . '/playlists/' . $playlistId . '/tracks';

$data = array(
    'uris' => $trackUris
);

$headers = array('Authorization: Bearer ' . $spotifyAccessToken,
    	 	 'Content-Type: application/json');

$cURLConnection = curl_init($addTracksURI);
curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($cURLConnection);
checkStatus($cURLConnection, $result);
curl_close($cURLConnection);

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
}
    
// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

header('Content-Type: application/json');
echo json_encode($return);

function checkStatus($c, $result) {
    $status = curl_getinfo($c, CURLINFO_HTTP_CODE);
    
    if ($result === FALSE || $status > 299) {
        header('Content-Type: application/json');
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
	$error = array('error' => $result);
        error_log('CURL ERROR: ' . $error);
        echo json_encode($error);
        http_response_code($status);
        die();
    }
}
