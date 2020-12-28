<?php
session_start();
if (isset($_SESSION["login-status"]) && $_SESSION["login-status"]) {
    header("Location: dashboard.php");
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="holderdiv">
    <form action="actions/login.php" method="post">
        <div class="form-group">
            <label for="username">Felhasználónév</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="pl.: 71064799651">
        </div>
        <div class="form-group">
            <label for="password">Jelszó</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="pl.: 1990-12-31">
        </div>
        <div class="form-group">
            <label for="school">Iskola</label>
            <select class="form-control" id="school" name="school">
                <?php
                foreach ($decoded as $item){

                    // Bocsi, fanboy vagyok. :P
                    if($item["instituteCode"] == "bmszc-neumann"){
                        echo "<option value=\"" . $item["instituteCode"] . "\" selected>" . $item["instituteCode"] . "</option>";
                    } else {
                        echo "<option value=\"" . $item["instituteCode"] . "\">" . $item["instituteCode"] . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <input type="submit" class="form-control" value="Bejelentkezés">
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
</body>
</html>
