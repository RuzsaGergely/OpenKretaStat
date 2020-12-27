<?php
$curl_h = curl_init('https://kretaglobalmobileapi2.ekreta.hu:443/api/v2/Institute');
$config = parse_ini_file('config.ini');

curl_setopt($curl_h, CURLOPT_HTTPHEADER,
    array(
        'apiKey: ' . $config["apikey"],
    )
);
curl_setopt($curl_h, CURLOPT_ENCODING, 'UTF-8');
curl_setopt($curl_h, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($curl_h);

$decoded = json_decode($response, true);

?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>OpenKretaStat - Iskolák</title>
</head>
<body>
    <table>
        <tr>
            <th>ID</th>
            <th>Név</th>
            <th>Kód</th>
            <th>Link</th>
        </tr>
        <?php
        foreach ($decoded as $item){
            echo "<tr><td>".$item["instituteId"]."</td><td>".$item["name"]."</td><td>".$item["instituteCode"]."</td><td><a href='".$item["url"]."'>".$item["url"]."</a></td></tr>";
        }
        ?>
    </table>
</body>
</html>
