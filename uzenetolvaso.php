<?php
session_start();
header('Content-type: text/html; charset=utf-8');

if (!isset($_SESSION["login-status"]) || !$_SESSION["login-status"]) {
    header("Location: index.php");
    exit();
}

$config = parse_ini_file('config.ini');

function makerequest($headers, $url){
    $curl_h = curl_init($url);

    curl_setopt($curl_h, CURLOPT_HTTPHEADER,
        $headers
    );
    curl_setopt($curl_h, CURLOPT_ENCODING, 'UTF-8');
    curl_setopt($curl_h, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl_h);
    $decoded = json_decode($response, true);
    curl_close($curl_h);
    return $decoded;
}

// Forrás: https://stackoverflow.com/a/30008824
function table_cell($data) {
    $return = "<table border='1'>";
    foreach ($data as $key => $value) {
        $return .= "<tr><td>$key</td><td>";
        if (is_array($value)) {
            $return .= table_cell($value);
        } else {
            $return .= $value;
        }
        $return .= "</td><tr>";
    }
    $return .= "</tr></table>";
    return($return);
}

$response = makerequest(Array(
    "Authorization: Bearer " . $_SESSION["access_token"],
    "User-Agent: " . $config["useragent"]
), "https://eugyintezes.e-kreta.hu/api/v1/kommunikacio/postaladaelemek/" . $_GET["azon"]);

$table = table_cell($response);
echo $table;