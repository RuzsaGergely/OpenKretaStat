<?php
session_start();
if (isset($_SESSION["login-status"]) && $_SESSION["login-status"]) {
    header("Location: faliujsag.php");
    exit();
}
$config = parse_ini_file('config.ini');
$curl_h = curl_init('https://kretaglobalmobileapi2.ekreta.hu:443/api/v2/Institute');

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
    <title>OpenKretaStat - Bejelentkezés</title>
</head>
<body>
<form action="actions/login.php" method="post">
    <input type="text" name="username" placeholder="Felhasználónév">
    <br>
    <br>
    <input type="password" name="password" placeholder="Jelszó">
    <br>
    <br>
    <select name="school" id="school">
        <?php
        foreach ($decoded as $item){
            echo "<option value=\"" . $item["instituteCode"] . "\">" . $item["instituteCode"] . "</option>";
        }
        ?>
    </select>
    <br>
    <br>
    <input type="submit" value="Bejelentkezés">
</form>
</body>
</html>
