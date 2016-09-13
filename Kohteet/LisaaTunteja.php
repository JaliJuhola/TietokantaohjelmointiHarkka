<?php

$y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=jj421960 user=jj421960 password=salasana";

$yhteys = pg_connect($y_tiedot) or die("Tietokantayhteyden luominen epäonnistui.");

if (isset($_POST['tallenna'])) {
   
  $tyokohde_id = intval($_POST['id_lista']);
  
  if (!empty($tyokohde_id)) {
    
    pg_query('BEGIN');
    
    $tunti_paivitys = $tunti_ale_paivitys = $tarvike_paivitys = $tarvike_ale_paivitys = true;

    // Tuntitöiden ja mahdollisten alennusprosenttien lisääminen.
    
    $tuntityypit = array('tyo', 'suunnittelu', 'aputyo');
    $tunnit = array(intval($_POST['tyo']), intval($_POST['suunnittelu']), intval($_POST['aputyo']));
    $alet = array($_POST['tyo_ale'], $_POST['suunnittelu_ale'], $_POST['aputyo_ale']);
    
    for ($i = 0; $i < 3; $i++) {
      if (!empty($tunnit[$i])) {
        $tunti_lisays = "UPDATE tiko_ht.tyokohde_tuntityo SET maara = maara + $1
                         WHERE tyokohde_id = $2 AND tyyppi = $3";
        $tunti_paivitys = pg_query_params($tunti_lisays, array($tunnit[$i], $tyokohde_id, $tuntityypit[$i]));
      }
      if ($alet[$i] != "") {
        $tunti_ale_lisays = "UPDATE tiko_ht.tyokohde_tuntityo SET alennusprosentti = $1
                             WHERE tyokohde_id = $2 AND tyyppi = $3";
        $tunti_ale_paivitys = pg_query_params($tunti_ale_lisays, array($alet[$i], $tyokohde_id, $tuntityypit[$i]));
      }
      if (!$tunti_paivitys || !$tunti_ale_paivitys)
        break;
    }
    
    // Tarvikkeiden ja mahdollisten alennusprosenttien lisääminen.
    
    for ($tarvikeNro = 1; $tarvikeNro <= intval($_POST['tarvikeLkm']); $tarvikeNro++) {
      
      $tarvike_id = intval($_POST['id_lista' . strval($tarvikeNro)]);
      $tarvike_maara = intval($_POST['tarvike_maara' . strval($tarvikeNro)]);
      $tarvike_ale = $_POST['tarvike_ale' . strval($tarvikeNro)];
      
      if (!empty($tarvike_id)) {
        $tarvike_lisays = "UPDATE tiko_ht.tarvike_tyokohde SET maara = maara + $1
                           WHERE tyokohde_id = $2 AND tarvike_id = $3";
        $tarvike_paivitys = pg_query_params($tarvike_lisays, array($tarvike_maara, $tyokohde_id, $tarvike_id));

        // Jos ei vaikuttanut mihinkään riviin niin tarviketta ei vielä ole. Lisätään se.
        if (pg_affected_rows($tarvike_paivitys) == 0) {
          $tarvike_lisays = "INSERT INTO tiko_ht.tarvike_tyokohde (tarvike_id, tyokohde_id, maara)
                             VALUES ($1, $2, $3)";
          $tarvike_paivitys = pg_query_params($tarvike_lisays, array($tarvike_id, $tyokohde_id, $tarvike_maara));
        }
        
        if ($tarvike_ale != "") {
          $tarvike_ale_lisays = "UPDATE tiko_ht.tarvike_tyokohde SET alennusprosentti = $1
                         WHERE tyokohde_id = $2 AND tarvike_id = $3";
          $tarvike_ale_paivitys = pg_query_params($tarvike_ale_lisays, array($tarvike_ale, $tyokohde_id, $tarvike_id));
        }
        
        // Jos ilmeni virheitä niin poistutaan silmukasta.
        if (!$tarvike_paivitys || !$tarvike_ale_paivitys) 
          break;
      }
    }
    
    // Tarkistetaan tapahtuiko virheitä.
    if ($tunti_paivitys && $tunti_ale_paivitys && $tarvike_paivitys && $tarvike_ale_paivitys)
      $viesti = 'Tunnit ja tarvikkeet lisätty.';
    else
      $viesti = 'Tunteja ja tarvikkeita ei lisätty: ' . pg_last_error();
    
    pg_query('COMMIT');
    
  }
  else {
    $viesti = 'Pakollinen tieto puuttuu: Työkohteen tunnus.';
  }
}


?>


<html>
    <head>
        <meta charset="iso-8859-1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="Navbar.css">
        <title>Tuntejen ja tarvikkeiden lisaaminen</title>
        <script type="text/javascript"
                src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js">
        </script>
        <script type="text/javascript">
            $(document).ready(function () {
                var laskuri = 1;
                $('#tarvikeLisays').click(function () {
                    if (laskuri == 1) {
                        $('#tarvikeLista').append('<tr><th>Tarvike</th><th>Tarvikkeen tunnus</th>' +
                                '<th>Tarvikkeen määrä</th><th>Tarvikkeen alennusprosentti</th></tr>');
                    }
                    $('#tarvikeLista').append('<tr><td>' + laskuri + '.</td>' +
                            '<td><input type="text" name="tarvike_id' + laskuri + '" /></td>' +
                            '<td><input type="text" name="tarvike_maara' + laskuri + '" /></td>' +
                            '<td><input type="text" name="tarvike_ale' + laskuri + '" /></td></tr>');
                    document.getElementById("tarvikeLkm").value = laskuri;
                    laskuri++;
                });
            });
        </script>
        <title>Lisaa tunteja</title>
    </head>
    <body>
        <div id="ylatunniste">

            <h1>Sähkötärsky TMI</h1>
        </div>
        <ul class ="navbar">
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Etusivu/index.php' style="margin-left: 120px;" class="active">Etusivu</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Kohteet/kohteet.php' class="not-active">Kohteet</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Tarvikkeet/tarvikkeet.php' class="active">Tarvikkeet</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Asiakas/Asiakas.php' class="active">Asiakas</a></li>
            <li class="navlist"><label ></label>
        </ul>
        <ol class="sidelist">
            <li class="sideobj"><a class="active" href="/~jj421960/TikoHarkka/Kohteet/kohteet.php">Kohteet</a></li>
            <li class="sideobj"><a  class = "active" href="/~jj421960/TikoHarkka/Kohteet/urakkatarjous.php" >Urakkatarjous</a></li>
            <li class="sideobj"><a class="not-active" href="/~jj421960/TikoHarkka/Kohteet/LisaaTunteja.php" >Lisää tunteja</a></li>
        </ol>
        <div style="margin-left: 35%">
            <h1>Tuntitöiden lisääminen työkohteeseen</h1>

            <form action="LisaaTunteja.php" method="post">

                <?php if (isset($viesti)) echo '<p style="color:red">'.$viesti.'</p>'; ?>

                <table>
                    <tr>
                        <td>Valitse tuntityökohde: </td>
                        <td>

                            <?php
                            $tyokohteet = "SELECT nimi, urakkahinta, tyokohde_id FROM tiko_ht.tyokohde ORDER BY tyokohde_id;";
                            $tulos = pg_query($tyokohteet);?>
                            <select name="id_lista">
                                <option>valitse kohde!</option>
                                <?php while($row = pg_fetch_array($tulos)){ ?>
                                 <?php if($row['urakkahinta'] == NULL) { ?>
                                   <option value="<?php echo $row['tyokohde_id']; ?>"><?php echo $row['tyokohde_id'] . ": " . $row['nimi'];?></option> 
                                 
                                <?php }
                                  }?> 
                            </select> 
                        </td>
                    </tr>
                    <tr>
                        <td>Työtuntien määrä</td>
                        <td><input type="text" name="tyo" value="" /></td>
                        <td>Työtuntien alennusprosentti</td>
                        <td><input type="text" name="tyo_ale" value="" /></td>
                    </tr>
                    <tr>
                        <td>Suunnittelutuntien määrä</td>
                        <td><input type="text" name="suunnittelu" value="" /></td>
                        <td>Suunnittelutuntien alennusprosentti</td>
                        <td><input type="text" name="suunnittelu_ale" value="" /></td>
                    </tr>
                    <tr>
                        <td>Aputyötuntien määrä</td>
                        <td><input type="text" name="aputyo" value="" /></td>
                        <td>Aputyötuntien alennusprosentti</td>
                        <td><input type="text" name="aputyo_ale" value="" /></td>
                    </tr>
                </table>

                <table id="tarvikeLista"></table>
                <input type="button" value="Lisää tarvike" id="tarvikeLisays" />

                <br />
                <input type="hidden" name="tarvikeLkm" id="tarvikeLkm" placeholder="0" />
                <br />

                <input type="submit" name="tallenna" value="Lisää tiedot" />
            </form>
        </div>
    </body>
</html>