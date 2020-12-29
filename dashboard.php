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
    <link rel="stylesheet" href="style.css">
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
            <a class="nav-link" href="javascript:hideshow('profile')">Adatlap</a>
            <a class="nav-link" href="javascript:hideshow('debug')">Debug</a>
        </div>
        <ul class="navbar-nav ml-auto">
            <div class="nav-item">
                <a class="nav-link" href="actions/logout.php">Kijelentkezés</a>
            </div>
        </ul>

    </div>
</nav>
<div class="contentdiv">
    <div class="card module" id="grades">
        <div class="card-body">
            <?php
            $response = makerequest(Array(
                "Authorization: Bearer " . $_SESSION["access_token"],
                "User-Agent: " . $config["useragent"]
            ), "https://".$_SESSION["institute_code"].".e-kreta.hu/ellenorzo/V3/Sajat/Ertekelesek");

            // Igor... ha nem találsz vénát... csináljá'!
            function arrayCheck($array, $search){
                $van = false;
                foreach ($array as $item){
                    if($item["tantargy"] == $search){
                        $van = true;
                        break;
                    }
                }
                return $van;
            }

            function getIndex($array, $search){
                for ($i = 0; $i < sizeof($array); $i++) {
                    if($array[$i]["tantargy"] == $search){
                        return $i;
                    }
                }
            }

            $tantargyak = Array();

            foreach ($response as $item){
                if(!arrayCheck($tantargyak, $item["Tantargy"]["Nev"])){
                    array_push($tantargyak, Array(
                            "tantargy" => $item["Tantargy"]["Nev"]
                    ));
                }
            }

            foreach ($response as $item){
                array_push($tantargyak[getIndex($tantargyak, $item["Tantargy"]["Nev"])], Array(
                    "jegy" => $item["SzamErtek"],
                    "suly" => $item["SulySzazalekErteke"],
                    "tema" => $item["Tema"],
                    "beirva" => $item["KeszitesDatuma"],
                    "targynap" => $item["RogzitesDatuma"]
                ));
            }
            ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">Tantárgy</th>
                        <th scope="col">Jegy</th>
                        <th scope="col">Súly</th>
                        <th scope="col">Téma</th>
                        <th scope="col">Beírva</th>
                        <th scope="col">Tárgynap</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($tantargyak as $item){
                        echo "<tr>";
                        echo "<td scope=\"row\" colspan='4'>".$item["tantargy"]."</td>";
                        echo "</tr>";
                        foreach (array_reverse($item) as $subitem){
                            if(is_numeric($subitem["jegy"]) || $subitem["jegy"] == "-"){
                                echo "<tr><td>&nbsp;</td>";
                                echo "<td>" . $subitem["jegy"] . "</td>";
                                echo "<td>" . $subitem["suly"] . "%</td>";
                                echo "<td>" . $subitem["tema"] . "</td>";
                                echo "<td>" . $subitem["beirva"] . "</td>";
                                echo "<td>" . $subitem["targynap"] . "</td>";
                                echo "</tr>";
                            }

                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>

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
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Dátum</th>
                        <th scope="col">Tipus</th>
                        <th scope="col">Tanár</th>
                        <th scope="col">Cim</th>
                        <th scope="col">Szöveg</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $response = makerequest(Array(
                        "Authorization: Bearer " . $_SESSION["access_token"],
                        "User-Agent: " . $config["useragent"]
                    ), "https://".$_SESSION["institute_code"].".e-kreta.hu/ellenorzo/V3/Sajat/Feljegyzesek");
                    foreach ($response as $item){
                        echo "<tr>";
                        echo "<td scope=\"row\">".$item["Datum"]."</td>";
                        echo "<td>".$item["Tipus"]["Leiras"]."</td>";
                        echo "<td>".$item["KeszitoTanarNeve"]."</td>";
                        echo "<td>".$item["Cim"]."</td>";
                        echo "<td>".$item["Tartalom"]."</td>";
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card module" id="exams" style="display: none">
        <div class="card-body">
            <div class="table-responsive">
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
    </div>
    <div class="card module" id="absences" style="display: none">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Mulasztás dátuma</th>
                        <th scope="col">Tantárgy</th>
                        <th scope="col">Tanár</th>
                        <th scope="col">Mulasztás tipusa</th>
                        <th scope="col">Igazolás tipusa</th>
                        <th scope="col">Állapot</th>
                        <th scope="col">Késés (perc)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $response = makerequest(Array(
                        "Authorization: Bearer " . $_SESSION["access_token"],
                        "User-Agent: " . $config["useragent"]
                    ), "https://".$_SESSION["institute_code"].".e-kreta.hu/ellenorzo/V3/Sajat/Mulasztasok?datumTol=null");
                    foreach ($response as $item){
                        echo "<tr>";
                        echo "<td scope=\"row\">".$item["Datum"]."</td>";
                        echo "<td>".$item["Tantargy"]["Nev"]."</td>";
                        echo "<td>".$item["RogzitoTanarNeve"]."</td>";
                        echo "<td>".$item["Mod"]["Leiras"]."</td>";
                        echo "<td>".$item["IgazolasTipusa"]["Leiras"]."</td>";
                        echo "<td>".$item["IgazolasAllapota"]."</td>";
                        echo "<td>".$item["KesesPercben"]."</td>";
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card module" id="profile" style="display: none">
        <div class="card-body">
            <?php
            $response = makerequest(Array(
                "Authorization: Bearer " . $_SESSION["access_token"],
                "User-Agent: " . $config["useragent"]
            ), "https://".$_SESSION["institute_code"].".e-kreta.hu/ellenorzo/V3/Sajat/Tanulo");
            print_r($response);
            ?>
        </div>
    </div>
    <div class="card module" id="debug" style="display: none">
        <div class="card-body">
            <p><b>Login stat.: </b> <?php echo $_SESSION["login-status"];?></p>
            <p><b>Intézmény: </b> <?php echo $_SESSION["institute_code"];?></p>
            <p><b>User: </b> <?php echo $_SESSION["user"];?></p>
            <p><b>Access Token: </b> <?php echo $_SESSION["access_token"];?></p>
            <p><b>Refresh Token: </b> <?php echo $_SESSION["refresh_token"];?></p>
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
    hideshow(localStorage.getItem("last"));
    window.setTimeout(function () {
        window.location.replace("actions/refresh.php?inst=<?php echo $_SESSION["institute_code"];?>&reftok=<?php echo $_SESSION["refresh_token"];?>");
    }, 1700000);
</script>
</body>
</html>
