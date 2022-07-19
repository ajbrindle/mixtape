<?php
require_once(__DIR__ . '/config.php');

function authoriseOther($state, $redirect, $scope = '') {
    $authURL = 'https://accounts.spotify.com/authorize/';
    $args = '?client_id=' . CLIENT_ID .
            '&response_type=code' .
            '&redirect_uri=' . urlencode($redirect) .
            '&state=' . $state .
            '&show_dialog=false' .
            (strlen($scope) > 0 ? '&scope=' . $scope : '');
    echo '<script type="text/javascript">' .
         'window.location = "' . $authURL . $args . '"' .
         '</script>';
}

function getToken($code, $id, $redirect) {
    $authURL = 'https://accounts.spotify.com/api/token';
    
    $data = array('grant_type' => 'authorization_code',
                  'code' => $code,
                  'state' => $id,
                  'redirect_uri' => $redirect);
    $headers = array('Authorization: Basic ' . base64_encode(CLIENT_ID . ":" . CLIENT_SECRET),
    	 	     'Content-Type: application/x-www-form-urlencoded');

    $cURLConnection = curl_init($authURL);
    curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($cURLConnection);
    $status = curl_getinfo($cURLConnection, CURLINFO_HTTP_CODE);
    curl_close($cURLConnection);
    
    if ($result === FALSE || $status != 200) {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        $error = array('error' => $result);
        error_log('CURL ERROR: ' . $error);
        echo json_encode($error);
        http_response_code(401);
        die();
    }
    
    return json_decode($result, TRUE);
}
