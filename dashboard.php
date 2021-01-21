<?php
// Források
// https://github.com/bczsalba/ekreta-docs-v3
// https://github.com/filc/naplo/blob/3023c78a6785c9b2e000e2aaed6f450fddd33f34/lib/kreta/client.dart
// https://github.com/filc/naplo/blob/3023c78a6785c9b2e000e2aaed6f450fddd33f34/lib/kreta/api.dart

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
    //file_put_contents("reqs/file_".rand(2003,65535).".json", $response);
    $decoded = json_decode($response, true);
    curl_close($curl_h);
    return $decoded;
}

// Forrás: https://stackoverflow.com/a/30008824
function table_cell($data) {
    $return = "<table class='table'>";
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

function average($response, $search){
    $all = 0;
    $counter = 0;
    foreach ($response as $item){
        if(!empty($item[$search])) {
            $all = $all + $item[$search];
            $counter += 1;
        }
    }
    return ($all/$counter);
}

function gradeAverage($input){
    $dividend = 0;
    $divisor = 0;

    foreach($input as $item){
        $dividend += ($item[0] * $item[1]);
        $divisor += $item[1];
    }

    return $dividend / $divisor;
}

$curl_h = curl_init('https://idp.e-kreta.hu/connect/token');

curl_setopt($curl_h, CURLOPT_POST, 1);
curl_setopt($curl_h, CURLOPT_HTTPHEADER,
    array(
        'User-Agent: ' . $config["useragent"],
        'Content-Type: application/x-www-form-urlencoded'
    )
);
curl_setopt($curl_h, CURLOPT_ENCODING, 'UTF-8');
curl_setopt($curl_h, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl_h, CURLOPT_POSTFIELDS, "institute_code=". $_SESSION["institute_code"] ."&refresh_token=". $_SESSION["refresh_token"] ."&grant_type=refresh_token&client_id=kreta-ellenorzo-mobile");

$response = curl_exec($curl_h);
$decoded = json_decode($response, true);
curl_close($curl_h);

$_SESSION["access_token"] = $decoded["access_token"];
$_SESSION["refresh_token"] = $decoded["refresh_token"];

$color_codes = Array(
    50=>"gray",
    100=>"blue",
    150=>"darkgreen",
    200=>"red"
);

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
<nav class="navbar sticky-top navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">OpenKretaStat</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav">
            <a class="nav-link" href="javascript:hideshow('grades')">Jegyek</a>
            <a class="nav-link" href="javascript:hideshow('stats')">Statisztika</a>
            <a class="nav-link" href="javascript:hideshow('groups')">Csoportok</a>
            <a class="nav-link" href="javascript:hideshow('messages')">Üzenetek</a>
            <a class="nav-link" href="javascript:hideshow('notes')">Feljegyzések</a>
            <a class="nav-link" href="javascript:hideshow('events')">Faliújság</a>
            <a class="nav-link" href="javascript:hideshow('exams')">Számonkérések</a>
            <a class="nav-link" href="javascript:hideshow('absences')">Hiányzások</a>
            <a class="nav-link" href="javascript:hideshow('profile')">Adatlap</a>
            <a class="nav-link" href="javascript:hideshow('debug')">Debug</a>
            <a class="nav-link" href="javascript:hideshow('api')">API</a>
        </div>
        <ul class="navbar-nav ml-auto">
            <div class="nav-item">
                <a class="nav-link" href="actions/logout.php">Kijelentkezés</a>
            </div>
        </ul>

    </div>
</nav>
<div class="contentdiv">
    <div class="card module" id="grades" style="display: none">
        <div class="card-body">
            <?php
            $response = makerequest(Array(
                "Authorization: Bearer " . $_SESSION["access_token"],
                "User-Agent: " . $config["useragent"]
            ), "https://".$_SESSION["institute_code"].".e-kreta.hu/ellenorzo/V3/Sajat/Ertekelesek");

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
            $osszes_jegy = Array();

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
                    "beirva" => date("Y. F. d. H:i:s", strtotime($item["KeszitesDatuma"])),
                    "targynap" => date("Y. F. d. H:i:s", strtotime($item["RogzitesDatuma"])),
                    "tipus" => Array(
                        $item["Tipus"]["Uid"],
                        $item["Tipus"]["Leiras"]
                    ),
                    "tanar" => $item["ErtekeloTanarNeve"]
                ));
                array_push($osszes_jegy, Array(
                    $item["SzamErtek"],
                    $item["SulySzazalekErteke"]
                ));
            }
            ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Tantárgy</th>
                        <th scope="col">Jegy</th>
                        <th scope="col">Súly</th>
                        <th scope="col">Téma</th>
                        <th scope="col">Tanár</th>
                        <th scope="col">Beírva</th>
                        <th scope="col">Tárgynap</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($tantargyak as $item){
                        echo "<tr>";
                        echo "<td scope=\"row\" colspan='8'><b>".$item["tantargy"]."</b></td>";
                        echo "</tr>";
                        foreach (array_reverse($item) as $subitem){
                            if(is_numeric($subitem["jegy"]) || $subitem["jegy"] == "-"){
                                echo "<tr data-toggle=\"tooltip\" data-placement=\"top\" title='".$subitem["tipus"][1]."'><td>&nbsp;</td>";
                                echo "<td style='color:".$color_codes[$subitem["suly"]]."'>" . $subitem["jegy"] . "</td>";
                                echo "<td>" . $subitem["suly"] . "%</td>";
                                echo "<td>" . $subitem["tema"] . "</td>";
                                echo "<td>" . $subitem["tanar"] . "</td>";
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
    <div class="card module" id="events" style="display: none">
        <div class="card-body">
            <?php
            $response = makerequest(Array(
                "Authorization: Bearer " . $_SESSION["access_token"],
                "User-Agent: " . $config["useragent"]
            ), "https://".$_SESSION["institute_code"].".e-kreta.hu/ellenorzo/V3/Sajat/FaliujsagElemek");

            if(empty($response)){
                echo "Üres a faliújság";
            } else {
                $table = table_cell($response);
                echo $table;
            }
            ?>
        </div>
    </div>
    <div class="card module" id="groups" style="display: none">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Uid</th>
                        <th scope="col">Név</th>
                        <th scope="col">Oktatás-Nevelési Feladat</th>
                        <th scope="col">Tipus</th>
                        <th scope="col">Aktív?</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $csoportok_request = makerequest(Array(
                        "Authorization: Bearer " . $_SESSION["access_token"],
                        "User-Agent: " . $config["useragent"]
                    ), "https://".$_SESSION["institute_code"].".e-kreta.hu/ellenorzo/V3/Sajat/OsztalyCsoportok");

                    foreach ($csoportok_request as $item){
                        echo "<tr>";
                        echo "<td>".$item["Uid"]."</td>";
                        echo "<td>".$item["Nev"]."</td>";
                        echo "<td>".$item["OktatasNevelesiFeladat"]["Uid"]."</td>";
                        echo "<td>".$item["Tipus"]."</td>";
                        echo "<td>".$item["IsAktiv"]."</td>";
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card module" id="stats" style="display: none">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Tantárgy</th>
                        <th scope="col">Tanulói átlag</th>
                        <th scope="col">Csoportátlag</th>
                        <th scope="col">Átlagtól való eltérés</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $response = makerequest(Array(
                        "Authorization: Bearer " . $_SESSION["access_token"],
                        "User-Agent: " . $config["useragent"]
                    ), "https://".$_SESSION["institute_code"].".e-kreta.hu/ellenorzo/V3/Sajat/Ertekelesek/Atlagok/OsztalyAtlagok?oktatasiNevelesiFeladatUid=" . $csoportok_request[0]["OktatasNevelesiFeladat"]["Uid"]);

                    $id = 0;
                    foreach ($response as $item){
                        echo "<tr>";
                        echo "<td>" . $id . "</td>";
                        echo "<td>" . $item["Tantargy"]["Nev"] . "</td>";
                        echo "<td>" . $item["TanuloAtlag"] . "</td>";
                        echo "<td>" . $item["OsztalyCsoportAtlag"] . "</td>";
                        if($item["OsztalyCsoportAtlagtolValoElteres"]>0) {
                            echo "<td>+" . $item["OsztalyCsoportAtlagtolValoElteres"] . "</td>";
                        } else {
                            echo "<td>" . $item["OsztalyCsoportAtlagtolValoElteres"] . "</td>";
                        }
                        echo "</tr>";
                        $id += 1;
                    }
                    echo "<tr><td colspan='2'>Átlagok átlaga</td><td>".round(average($response, "TanuloAtlag"),2)."</td><td>".round(average($response, "OsztalyCsoportAtlag"),2)."</td><td>&nbsp</td></tr>";
                    echo "<tr><td colspan='2'>Jegyek összátlaga</td><td>".round(gradeAverage($osszes_jegy),2)."</td><td></td><td>&nbsp</td></tr>";
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card module" id="messages" style="display: none">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Üzenet azon.</th>
                        <th scope="col">Azonosító</th>
                        <th scope="col">Tárgy</th>
                        <th scope="col">Feladó</th>
                        <th scope="col">Elolvasva?</th>
                        <th scope="col">Dátum</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $response = makerequest(Array(
                        "Authorization: Bearer " . $_SESSION["access_token"],
                        "User-Agent: " . $config["useragent"]
                    ), "https://eugyintezes.e-kreta.hu/api/v1/kommunikacio/postaladaelemek/beerkezett");

                    foreach ($response as $item){
                        echo "<tr>";
                        echo "<td>" . $item["uzenetAzonosito"] . "</td>";
                        echo "<td><a href='uzenetolvaso.php?azon=" . $item["azonosito"] . "' target='_blank'>".$item["azonosito"]."</a></td>";
                        echo "<td>" . $item["uzenetTargy"] . "</td>";
                        echo "<td>" . $item["uzenetFeladoNev"] . "</td>";
                        if($item["isElolvasva"] == 1) {
                            echo "<td>Igen</td>";
                        } else {
                            echo "<td>Nem</td>";
                        }
                        echo "<td>" . date("Y. F. d. H:i:s", strtotime($item["uzenetKuldesDatum"])) . "</td>";

                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
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
                        echo "<td scope=\"row\">".date("Y. F. d. H:i:s", strtotime($item["Datum"]))."</td>";
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
                        echo "<td scope=\"row\">".date("Y. F. d. H:i:s", strtotime($item["BejelentesDatuma"]))."</td>";
                        echo "<td>".date("Y. M. d. H:i:s", strtotime($item["Datum"]))."</td>";
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
                        echo "<td scope=\"row\">".date("Y. F. d. H:i:s", strtotime($item["Datum"]))."</td>";
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
            <div class="table-responsive">
                    <?php
                    $response = makerequest(Array(
                        "Authorization: Bearer " . $_SESSION["access_token"],
                        "User-Agent: " . $config["useragent"]
                    ), "https://".$_SESSION["institute_code"].".e-kreta.hu/ellenorzo/V3/Sajat/TanuloAdatlap");

                    $table = table_cell($response);
                    echo $table;
                    ?>
            </div>
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
    <div class="card module" id="api" style="display: none">
        <div class="card-body">
            <b>Ide kerül majd az API dokumentáció</b>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
<script>
    function hideshow(element) {
        var x = document.getElementById(element);
        if(x.style.display != "block"){
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

        if ($(window).width() <= 800 && $('.navbar-collapse').is('.collapse:not(.show)') == false) {
            $('.navbar-collapse').collapse('toggle');
        }
    }
    if(localStorage.hasOwnProperty("last") === false) {
        hideshow('grades');
    } else {
        if(localStorage.getItem("last") !== ""){
            hideshow(localStorage.getItem("last"));
        }
    }
    window.setTimeout(function () {
        window.location.replace("actions/refresh.php?inst=<?php echo $_SESSION["institute_code"];?>&reftok=<?php echo $_SESSION["refresh_token"];?>");
    }, 1700000);

</script>
</body>
</html>
