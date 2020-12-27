<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == "POST") {
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
    curl_setopt($curl_h, CURLOPT_POSTFIELDS, "userName=".$_POST["username"]."&password=".$_POST["password"]."&institute_code=".$_POST["school"]."&grant_type=password&client_id=kreta-ellenorzo-mobile");

    $response = curl_exec($curl_h);

    $decoded = json_decode($response, true);

    if(!empty($decoded["access_token"])){
        $_SESSION["login-status"] = true;
        $_SESSION["access_token"] = $decoded["access_token"];
        $_SESSION["refresh_token"] = $decoded["refresh_token"];
        header('Location: ../dashboard.php');
    } else {
        session_destroy();
        header('Location: ../index.php');
    }
}