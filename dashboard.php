<?php
session_start();

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
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>OpenKretaStat - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">OpenKretaStat</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav">
            <a class="nav-link" href="javascript:hideshow('grades')">Jegyek</a>
            <a class="nav-link" href="javascript:hideshow('stats')">Statisztika</a>
            <a class="nav-link" href="javascript:hideshow('messages')">Üzenetek</a>
            <a class="nav-link" href="javascript:hideshow('notes')">Feljegyzések</a>
            <a class="nav-link" href="javascript:hideshow('exams')">Számonkérések</a>
            <a class="nav-link" href="javascript:hideshow('absences')">Hiányzások</a>
            <a class="nav-link" href="javascript:hideshow('debug')">Debug</a>
        </div>
        <ul class="navbar-nav ml-auto">
            <div class="nav-item">
                <a class="nav-link" href="actions/logout.php">Kijelentkezés</a>
            </div>
        </ul>

    </div>
</nav>
<div style="padding-left: 10%; padding-right: 10%; padding-top: 1%;">
    <div class="card module" id="grades">
        <div class="card-body">
            This is some text within a card body. 1
        </div>
    </div>
    <div class="card module" id="stats" style="display: none">
        <div class="card-body">
            This is some text within a card body. 2
        </div>
    </div>
    <div class="card module" id="messages" style="display: none">
        <div class="card-body">
            This is some text within a card body. 3
        </div>
    </div>
    <div class="card module" id="notes" style="display: none">
        <div class="card-body">
            This is some text within a card body. 4
        </div>
    </div>
    <div class="card module" id="exams" style="display: none">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th scope="col">Bejelentés dátuma</th>
                    <th scope="col">Dolgozat dátuma</th>
                    <th scope="col">Tantárgy</th>
                    <th scope="col">Tanár</th>
                    <th scope="col">Téma</th>
                    <th scope="col">Beszámoló módja</th>
                </tr>
                </thead>
                <tbody>
            <?php
            $response = makerequest(Array(
                    "Authorization: Bearer " . $_SESSION["access_token"],
                    "User-Agent: " . $config["useragent"]
            ), "https://".$_SESSION["institute_code"].".e-kreta.hu/ellenorzo/V3/Sajat/BejelentettSzamonkeresek?datumTol=null");
            foreach ($response as $item){
                echo "<tr>";
                echo "<td scope=\"row\">".$item["BejelentesDatuma"]."</td>";
                echo "<td>".$item["Datum"]."</td>";
                echo "<td>".$item["TantargyNeve"]."</td>";
                echo "<td>".$item["RogzitoTanarNeve"]."</td>";
                echo "<td>".$item["Temaja"]."</td>";
                echo "<td>".$item["Modja"]["Leiras"]."</td>";
                echo "</tr>";
            }
            ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card module" id="absences" style="display: none">
        <div class="card-body">
            This is some text within a card body. 5
        </div>
    </div>
    <div class="card module" id="debug" style="display: none">
        <div class="card-body">
            <p><b>Login stat.: </b> <?php echo $_SESSION["login-status"];?></p>
            <p><b>Intézmény: </b> <?php echo $_SESSION["institute_code"];?></p>
            <p><b>User: </b> <?php echo $_SESSION["user"];?></p>
            <p><b>Token: </b> <?php echo $_SESSION["access_token"];?></p>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
<script>
    function hideshow(element) {
        var x = document.getElementById(element);
        if (x.style.display === "none") {
            var divsToHide = document.getElementsByClassName("module"); //divsToHide is an array
            for (var i = 0; i < divsToHide.length; i++) {
                divsToHide[i].style.display = "none";
            }
            x.style.display = "block";
            localStorage.setItem("last", element)
        } else {
            x.style.display = "none";
            localStorage.setItem("last", "")
        }
    }
</script>
</body>
</html>
