<?php
session_start();

if (!isset($_SESSION["login-status"]) || !$_SESSION["login-status"]) {
    header("Location: index.php");
    exit();
}

$curl_h = curl_init('https://idp.e-kreta.hu/connect/token');
$config = parse_ini_file('../config.ini');

curl_setopt($curl_h, CURLOPT_POST, 1);
curl_setopt($curl_h, CURLOPT_HTTPHEADER,
    array(
        'User-Agent: ' . $config["useragent"],
        'Content-Type: application/x-www-form-urlencoded'
    )
);
curl_setopt($curl_h, CURLOPT_ENCODING, 'UTF-8');
curl_setopt($curl_h, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl_h, CURLOPT_POSTFIELDS, "institute_code=". $_GET["inst"] ."&refresh_token=". $_GET["reftok"] ."&grant_type=refresh_token&client_id=kreta-ellenorzo-mobile");

$response = curl_exec($curl_h);
$decoded = json_decode($response, true);
curl_close($curl_h);

$_SESSION["access_token"] = $decoded["access_token"];
$_SESSION["refresh_token"] = $decoded["refresh_token"];

header("Location: ../dashboard.php");

