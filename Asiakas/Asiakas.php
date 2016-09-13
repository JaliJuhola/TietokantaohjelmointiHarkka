<html>
    <head>
        <title>Asiakkaat</title>
        <meta charset="iso-8859-1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="Navbar.css">
    </head>
    <body>
        <div id="ylatunniste">

            <h1>Sähkötärsky TMI</h1>
        </div>
        <ul class ="navbar">
            <li class="navlist"><label ></label>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Etusivu/index.php' style="margin-left: 120px;" class="active">Etusivu</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Kohteet/kohteet.php' class="active">Kohteet</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Tarvikkeet/tarvikkeet.php' class="active">Tarvikkeet</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Asiakas/Asiakas.php' class="not-active">Asiakas</a></li>
            <li class="navlist"><label ></label>
        </ul>
        <ol class="sidelist">
            <li class="sideobj"></li>
            <li class="sideobj"><a class="not-active" href="Asiakas.php">Asiakkaat</a></li>
            <li class="sideobj" style="display:inline-block;"><a  class="active" href="/~jj421960/TikoHarkka/Asiakas/LisaaTyokohde.php" >Lisää työkohde</a></li>
            <li class="sideobj"><a class="active" href="/~jj421960/TikoHarkka/Asiakas/LisaaAsiakas.php" >Lisää asiakas</a></li>
        </ol>
        <figure>
            <h2>Yritysasiakkaat</h2>
            <?php
            $y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=jj421960 user=jj421960 password=salasana";

            if (!$yhteys = pg_connect($y_tiedot))
               die("Tietokantayhteyden luominen epÃ¤onnistui.");
               
            header("Content-Type: text/html; charset=iso-8859-1");

            echo "<table class='Tarvike-taulu'>
            <tr>
            <th>Asiakas Id</th>
            <th>Osoite</th>
            <th>Nimi</th>	    
            <th>Ytunnus</th>

            </tr>"; 
            $tulos = pg_query("SELECT osoite, asiakas_id, nimi, y_tunnus FROM tiko_ht.asiakas, tiko_ht.yritys WHERE yritys_id = asiakas_id");
            if (!$tulos) {
               echo "Virhe kyselyssÃ¤.\n";
               exit;
            }
            while($row = pg_fetch_array($tulos)){   
               echo "<tr> <td>" . $row['asiakas_id'] . "</td><td>" . $row['osoite'] . "</td><td>" . $row['nimi'] . "</td><td>" . $row['y_tunnus'] . "</td></tr>"; 
            }

            echo "</table>"; 

            pg_close($yhteys);
            ?>
        </figure>
        <figure>
            <h2>Henkilöasiakkaat</h2>
            <?php
            $y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=jj421960 user=jj421960 password=salasana";

            if (!$yhteys = pg_connect($y_tiedot))
            die("Tietokantayhteyden luominen epÃ¤onnistui.");

            pg_query('BEGIN');
            header("Content-Type: text/html; charset=iso-8859-1");
            echo "<table class='Tarvike-taulu' 'style='margin-left: 10%;'>
            <tr>
            <th>Asiakas Id</th>
            <th>Osoite</th>
            <th>Etunimi</th>	    
            <th>Sukunimi</th>

            </tr>"; 
            $tulos = pg_query("SELECT osoite, etunimi, sukunimi, asiakas_id FROM tiko_ht.asiakas, tiko_ht.henkilo WHERE henkilo_id = asiakas_id");
            if (!$tulos) {
            echo "Virhe kyselyssÃ¤.\n";
            pg_query('ROLLBACK');
            exit;
            }
            while($row = pg_fetch_array($tulos)){   
            echo "<tr><td>" . $row['asiakas_id'] . "</td><td>" . $row['osoite'] . "</td><td>" . $row['etunimi'] . "</td><td>" . $row['sukunimi'] . "</td></tr>"; 
            }

            echo "</table>"; 
            pg_query('COMMIT');
            pg_close($yhteys);
            ?>
        </figure>

    </body>
</html>