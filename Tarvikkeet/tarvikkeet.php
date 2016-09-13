
<html>
    <head>
        <title>Tarvikkeet</title>
        <meta charset="iso-8859-1">
        <meta http-equiv="Content-Type" content="text/html; iso-8859-1">
        <link rel="stylesheet" type="text/css" href="Navbar.css">
    </head>
    <body>
        <div id="ylatunniste">

            <h1>Sähkötärsky TMI</h1>
        </div>
        <ul class ="navbar">
            <li class="navlist"><a href="/~jj421960/TikoHarkka/Etusivu/index.php" style="margin-left: 120px;" class="active">Etusivu</a></li>
            <li class="navlist"><a href="/~jj421960/TikoHarkka/Kohteet/kohteet.php" class="active">Kohteet</a></li>
            <li class="navlist"><a href="/~jj421960/TikoHarkka/Tarvikkeet/tarvikkeet.php" class="not-active">Tarvikkeet</a></li>
            <li class="navlist"><a href="/~jj421960/TikoHarkka/Asiakas/Asiakas.php" class="active">Asiakas</a></li>
            <li class="navlist"><label ></label>
        </ul>
        <ol class="sidelist">
            <li class="sideobj"><a class="not-active" href="default.asp">Tarvikkeet</a></li>
            <li class="sideobj"><a  class="active"href="/~jj421960/TikoHarkka/Tarvikkeet/Paivita.php" >Lisää tarvikkeita</a></li>
        </ol>
        <h2>Tietokannassa olevat tarvikkeet</h2>
        <br>
        <br>
        <figure>
            <?php
            header("Content-Type: text/html; charset=iso-8859-1");
            $y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=jj421960 user=jj421960 password=salasana";

            if (!$yhteys = pg_connect($y_tiedot))
               die("Tietokantayhteyden luominen epäonnistui.");

            echo "<table class='Tarvike-taulu'>
            <tr>
            <th>Id</th>
            <th>Nimi</th>	    
            <th>Määrä</th>
            <th>Ostohinta</th>
            <th>Myyntihinta</th>
            </tr>"; 
            $tulos = pg_query("SELECT tarvike_id, nimi, varastotilanne, ostohinta, myyntihinta FROM tiko_ht.tarvike WHERE poistettu = 'f';");
            if (!$tulos) {
               echo "Virhe kyselyssä.\n";
               exit;
            }
            while($row = pg_fetch_array($tulos)){   
               echo "<tr><td>" . $row['tarvike_id'] . "</td><td>" . $row['nimi'] . "</td><td>" . $row['varastotilanne'] . "</td><td>" . $row['ostohinta'] . "</td><td>" . $row['myyntihinta'] . "</td></tr>"; 
            }

            echo "</table>"; 

            pg_close($yhteys);
            ?>
        </figure>
    </body>
</html>