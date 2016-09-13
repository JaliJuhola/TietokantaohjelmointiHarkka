<?php

$y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=jj421960 user=jj421960 password=salasana";


$yhteys = pg_connect($y_tiedot) or die("Tietokantayhteyden luominen epäonnistui: " . pg_last_error());

if (isset($_POST['tallenna'])) {

  $asiakas_tunnus = intval($_POST['asiakas_tunnus']);
  $tyokohde_nimi = $_POST['tyokohde_nimi'];
  $tyokohde_osoite = $_POST['tyokohde_osoite'];
  $tyosuoritus = $_POST['tyosuoritus'];
  
  if (!empty($asiakas_tunnus) && !empty($tyokohde_nimi) &&
      !empty($tyokohde_osoite) && !empty($tyosuoritus)) {
    
    pg_query('BEGIN');
    
    $tyokohde_lisays = "INSERT INTO tiko_ht.tyokohde (nimi, osoite, asiakas_id)
                        VALUES ($1, $2, $3)";
    $tyokohde_paivitys = pg_query_params($tyokohde_lisays, array($tyokohde_nimi, $tyokohde_osoite, $asiakas_tunnus));
    $paivitys = true;
    
    if ($tyosuoritus == 'tunti') {
      $tuntityot_lisays = "INSERT INTO tiko_ht.tyokohde_tuntityo (tyokohde_id, tyyppi)
                           VALUES (CURRVAL('tiko_ht.tyokohde_id_sekvenssi'), 'tyo');";
      $tuntityot_lisays .= "INSERT INTO tiko_ht.tyokohde_tuntityo (tyokohde_id, tyyppi)
                            VALUES (CURRVAL('tiko_ht.tyokohde_id_sekvenssi'), 'suunnittelu');";
      $tuntityot_lisays .= "INSERT INTO tiko_ht.tyokohde_tuntityo (tyokohde_id, tyyppi)
                            VALUES (CURRVAL('tiko_ht.tyokohde_id_sekvenssi'), 'aputyo');";
      $paivitys = pg_query($tuntityot_lisays);
    }
    else {
      $urakkatyo_lisays = "UPDATE tiko_ht.tyokohde SET urakkahinta = 0
                           WHERE tyokohde_id = CURRVAL('tiko_ht.tyokohde_id_sekvenssi');";
      $paivitys = pg_query($urakkatyo_lisays);
    }
    
    if ($tyokohde_paivitys && $paivitys)
      $viesti = 'Työkohde lisätty.';
    else
      $viesti = 'Työkohdetta ei lisätty: ' . pg_last_error();
    
    pg_query('COMMIT');
    
  }
  else {
    $viesti = 'Pakollinen tieto puuttuu.';
  }
}

pg_close($yhteys);

?>

<html>
    <head>
        <title>Etusivu</title>
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
            <li class="sideobj"><a class="active" href="/~jj421960/TikoHarkka/Asiakas/Asiakas.php">Asiakkaat</a></li>
            <li class="sideobj" style="display:inline-block;"><a  class="not-active" href="/~jj421960/TikoHarkka/Asiakas/LisaaTyokohde.php" >Lisää työkohde</a></li>
            <li class="sideobj"><a class="active" href="/~jj421960/TikoHarkka/Asiakas/LisaaAsiakas.php" >Lisää asiakas</a></li>
        </ol>

        <figure>
            <h2>Työkohteen lisääminen asiakkaalle</h2>
        </figure>
        <form action="LisaaTyokohde.php" method="post" class="lisaaa">
            <?php if (isset($viesti)) echo '<p style="color:red">'.$viesti.'</p>'?>
            <table border="0" cellspacing="0" cellpadding="3">
                <tr>
                    <td>Asiakkaan tunnus</td>
                    <td><input type="text" name="asiakas_tunnus" value="" /></td>
                </tr>
                <tr>
                    <td>Työkohteen nimi</td>
                    <td><input type="text" name="tyokohde_nimi" value="" /></td>
                </tr>
                <tr>
                    <td>Työkohteen osoite</td>
                    <td><input type="text" name="tyokohde_osoite" value="" /></td>
                </tr>
                <tr>
                    <td>Työsuoritustyyppi</td>
                    <td><input type="radio" name="tyosuoritus" value="urakka"> Urakkatyö<td>
                    <td><input type="radio" name="tyosuoritus" value="tunti"> Tuntityö<td>
                </tr>
            </table>

            <br />

            <input type="submit" name="tallenna" value="Lisää työkohde" />
        </form>
    </body>
</html>