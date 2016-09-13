
<html>
    <head>
        <title>Tietokannassa olevat kohteet</title>
        <meta charset="iso-8859-1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="Navbar.css">
    </head>
    <body>
        <div id="ylatunniste">

            <h1>Sähkötärsky TMI</h1>
        </div>
        <ul class ="navbar">
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Etusivu/index.php' style="margin-left: 120px;" class="active">Etusivu</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Kohteet/Kohteet.php' class="not-active">Kohteet</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Tarvikkeet/tarvikkeet.php' class="active">Tarvikkeet</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Asiakas/Asiakas.php' class="active">Asiakas</a></li>
            <li class="navlist"><label ></label>
        </ul>
        <ol class="sidelist">
            <li class="sideobj"><a class="not-active" href="/~jj421960/TikoHarkka/Kohteet/kohteet.php">Kohteet</a></li>
            <li class="sideobj"><a  class = "active" href="/~jj421960/TikoHarkka/Kohteet/urakkatarjous.php" >Urakkatarjous</a></li>
            <li class="sideobj"><a class="active" href="/~jj421960/TikoHarkka/Kohteet/LisaaTunteja.php" >Lisää tunteja</a></li>
        </ol>
        <div style="margin-left: 40%;">
            <h2>Kohteet</h2>
            <?php 
            $y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=jj421960 user=jj421960 password=salasana";
            $yhteys = pg_connect($y_tiedot) or die("Tietokantayhteyden luominen epÃ¤onnistui.");
            $kkohteet = "SELECT valmis, tyokohde_id, nimi, osoite FROM tiko_ht.tyokohde ORDER BY tyokohde_id ASC;";
            $tulos = pg_query($kkohteet);
            echo "<table class='Tarvike-taulu'>
            <tr>
            <th>Id</th>
            <th>Nimi</th>	    
            <th>Osoite</th>
            <th>Tilanne</th>";

            echo "</tr>"; 
            if (!$tulos) {
               echo "Virhe kyselyssÃƒÂ¤.\n";
               exit;
            }
            while($row = pg_fetch_array($tulos)){   
               if($row['valmis'] == 'f') {
                  echo "<tr><td>" . $row['tyokohde_id'] . "</td><td>" . $row['nimi'] . "</td><td>" . $row['osoite'] . "</td><td>" . "Kesken" . "</td></tr>"; 
                } else {
                   echo "<tr><td>" . $row['tyokohde_id'] . "</td><td>" . $row['nimi'] . "</td><td>" . $row['osoite'] . "</td><td>" . "Valmis" . "</td></tr>"; 
                } 
            }
            echo "</table>";
            pg_close($yhteys);
            ?>
        </ol>
    </div>
</body>
</html>