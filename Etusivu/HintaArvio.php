<?php
$y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=jj421960 user=jj421960 password=salasana";
$yhteys = pg_connect($y_tiedot) or die("Tietokantayhteyden luominen epÃ¤onnistui.");


?>

<html>
    <head>
        <title>Hinta arvio</title>
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
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Etusivu/index.php' style="margin-left: 120px;" class="not-active">Etusivu</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Kohteet/kohteet.php' class="active">Kohteet</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Tarvikkeet/tarvikkeet.php' class="active">Tarvikkeet</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Asiakas/Asiakas.php' class="active">Asiakas</a></li>
            <li class="navlist"><label ></label>
        </ul>
        <ol class="sidelist">
            <li class="sideobj"><a class="active" href="/~jj421960/TikoHarkka/Etusivu/index.php">Etusivu</a></li>
            <li class="sideobj"><a class="not-active" href="/~jj421960/TikoHarkka/Etusivu/HintaArvio.php" >Hinta arvio</a></li>
            <li class="sideobj"><a class="active" href="/~jj421960/TikoHarkka/Etusivu/Laskut.php" >Laskut</a></li>
        </ol>
        <h2>Muodosta hinta-arvio</h2>
        <div style="margin-left: 35%;">
            <form action="HintaArvio.php" method="post">
                <b>Valitse kohde:</b>
                <?php
                $tyokohteet = "SELECT nimi, tyokohde_id, urakkahinta FROM tiko_ht.tyokohde;";
                $tulos = pg_query($tyokohteet);?>
                <select name="id_lista">
                    <option>valitse kohde!</option>
                    <?php while($row = pg_fetch_array($tulos)){ ?>
                    <?php if($row['urakkahinta'] == NULL){?>
                    <option value="<?php echo $row['tyokohde_id']; ?>"><?php echo $row['tyokohde_id'] . ": " . $row['nimi'];?></option>
                    <?php } ?>
                    <?php }?> 
                </select> 
                <br/>
                <br/>
                <br/>
                <input type="submit" value="Muodosta hinta-arvio" name="hinta_arvio">

            </form>
            <?php 
            $kokonaishinta = 0;

            if (isset($_POST['hinta_arvio'])) {
               pg_query("BEGIN;");
			   echo"<br/><b>Kohteen tarvikkeet:</b><br/>";
               $kohteen_id = intval($_POST['id_lista']);
               $tarvike_hinta = "SELECT tarvi.myyntihinta, tarvi.tarvike_id, tarvi.nimi, tt.maara 
                                 FROM tiko_ht.tyokohde AS kohde, tiko_ht.tarvike AS tarvi, tiko_ht.tarvike_tyokohde AS TT
                                 WHERE tt.tarvike_id = tarvi.tarvike_id AND tt.tyokohde_id = $kohteen_id AND tt.tyokohde_id = kohde.tyokohde_id;";
               $kohteen_tarvikkeet = pg_query($tarvike_hinta);
               if(empty($kohteen_tarvikkeet)) {
                  die("Virheellinen kysely!");
               }

               echo "<table class='taulu'>";
               echo " <tr>
               <th>Tarvikkeen id</th>
               <th>Tarvikkeen nimi</th>
               <th>Tarvikkeen hinta</th>
               <th>Tarvikkeiden maara</th>
               </tr>";
               while($row = pg_fetch_row($kohteen_tarvikkeet)) {
                  $tarvike_nimi = $row[2];
                  $tarvike_id = $row[1];
                  $tarvike_hinta = $row[0];
                  $tarvike_maara = $row[3];
                  $kokonaishinta = doubleval($kokonaishinta) + doubleval($tarvike_hinta) * doubleval($tarvike_maara);

                  echo "<tr> <td>" . $tarvike_id . "</td><td>" . $tarvike_nimi . "</td><td>" . $tarvike_hinta .  "</td><td>" . $tarvike_maara . "</td></tr>";
               }
               echo "</table><br/>";
  
               $tyo_tunnit = "SELECT tunt.tyyppi, tunt.hinta, tt.maara 
                           FROM tiko_ht.tyokohde AS kohde, tiko_ht.tuntityo AS tunt, tiko_ht.tyokohde_tuntityo AS tt  
                           WHERE tt.tyokohde_id = kohde.tyokohde_id AND kohde.tyokohde_id = $kohteen_id AND tt.tyyppi = tunt.tyyppi;";
               $tehdyt_tunnit = pg_query($tyo_tunnit);
               if(empty($tehdyt_tunnit)) {
                 die("Virheellinen kysely!");
               }
               echo "<b> Tehdyt tyot: </b><br/><table class='taulu'>";
               echo " <tr>
                   <th>Tyon tyyppi</th>
                   <th> Tunnin hinta</th>
                   <th> Tuntien maara</th>
                   </tr>";
                   while($rivi = pg_fetch_row($tehdyt_tunnit)) {
                      $tunti_tyyppi = $rivi[0];
                      $tunti_hinta = $rivi[1];
                      $tunti_maara = $rivi[2];
                      $kokonaishinta = $kokonaishinta + intval($tunti_hinta) * intval($tunti_maara);
                      echo "<tr> <td>" .  $tunti_tyyppi . "</td><td>" . $tunti_hinta . "</td><td>" . $tunti_maara .  "</td></tr>";
                  }
                  echo "</table>";
                  ?>
            <b>Kohteen kokonaishinta:</b>
            <input type="text" style="display: inline; height: 30px; width: 120px;" value="<?php if (isset($_POST['hinta_arvio'])) {echo "   $kokonaishinta"; }?>" id="kohteen_hinta" disabled>
            <?php pg_query("COMMIT"); } ?>
        </div>

    </body>
</html>