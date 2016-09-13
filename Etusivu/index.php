<?php
$y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=jj421960 user=jj421960 password=salasana";

$yhteys = pg_connect($y_tiedot) or die("Tietokantayhteyden luominen epäonnistui.");

if (isset($_POST['laskutus'])) {
   
  $tama_paivamaara = pg_escape_string(date("Y-m-d"));
  
  $muistutettavat = $muistutus_tarkistus = $muistutus_paivitys =
  $karhuttavat = $karhu_tarkistus = $karhu_paivitys = true;
  
  // Muistutuslaskujen muodostaminen.
  
  pg_query('BEGIN');
  
  $muistutettavat_kysely = "SELECT lasku_id, maksettava, maksuaika, tyokohde_id, kotitalousvahennys
                            FROM tiko_ht.lasku
                            WHERE maksupvm IS NULL AND edellinen_id IS NULL
                            AND paivamaara + maksuaika < '$tama_paivamaara'";
  $muistutettavat = pg_query($muistutettavat_kysely);
  
  if ($muistutettavat) {
  
    while ($lasku = pg_fetch_row($muistutettavat)) {
      
      $lasku_id = $lasku[0];
      $maksettava = $lasku[1];
      $maksuaika = $lasku[2];
      $tyokohde_id = $lasku[3];
      $kotitalousvah = $lasku[4];
      
      $muistutus_tarkistus_kysely = "SELECT * FROM tiko_ht.lasku WHERE edellinen_id = $lasku_id";
      $muistutus_tarkistus = pg_query($muistutus_tarkistus_kysely);
      
      if ($muistutus_tarkistus && pg_affected_rows($muistutus_tarkistus) == 0) {
          $muistutus_lisays = "INSERT INTO tiko_ht.lasku (paivamaara, maksettava, kotitalousvahennys, maksuaika, edellinen_id, tyokohde_id)
                               VALUES ('$tama_paivamaara', $maksettava + 5.00, $kotitalousvah, $maksuaika, $lasku_id, $tyokohde_id)";
          $muistutus_paivitys = pg_query($muistutus_lisays);
      }
    }
  }
  
  // Karhulaskujen muodostaminen.
  
  $karhuttavat_kysely = "SELECT lasku_id, maksettava, maksuaika, tyokohde_id, kotitalousvahennys
                         FROM tiko_ht.lasku
                         WHERE maksupvm IS NULL AND edellinen_id IS NOT NULL
                         AND paivamaara + maksuaika < '$tama_paivamaara'";
  $karhuttavat = pg_query($karhuttavat_kysely);
  
  if ($karhuttavat) {
  
    while ($lasku = pg_fetch_row($karhuttavat)) {
      
      $lasku_id = $lasku[0];
      $maksettava = $lasku[1];
      $maksuaika = $lasku[2];
      $tyokohde_id = $lasku[3];
      $kotitalousvah = $lasku[4];
     
      $karhu_tarkistus_kysely = "SELECT * FROM tiko_ht.lasku WHERE edellinen_id = $lasku_id";
      $karhu_tarkistus = pg_query($karhu_tarkistus_kysely);
      
      if ($karhu_tarkistus && pg_affected_rows($karhu_tarkistus) == 0) {
          $karhu_lisays = "INSERT INTO tiko_ht.lasku (paivamaara, maksettava, kotitalousvahennys, maksuaika, edellinen_id, tyokohde_id)
                           VALUES ('$tama_paivamaara', $maksettava + 5.00 + ($maksettava * 0.16), $kotitalousvah, $maksuaika, $lasku_id, $tyokohde_id)";
          $karhu_paivitys = pg_query($karhu_lisays);
      }
    }
  }
  
  if($muistutettavat && $muistutus_tarkistus && $muistutus_paivitys &&
     $karhuttavat && $karhu_tarkistus && $karhu_paivitys)
    $viesti = 'Laskut tarkistettu ja tarvittavat muistutus- ja karhulaskut lisätty.';
  else
    $viesti = 'Laskujen tarkistus ja muistutus- ja karhulaskujen muodostus epäonnistui: ' . pg_last_error();
  
  pg_query('COMMIT');
  
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
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Etusivu/index.php' style="margin-left: 120px;" class="not-active">Etusivu</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Kohteet/kohteet.php' class="active">Kohteet</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Tarvikkeet/tarvikkeet.php' class="active">Tarvikkeet</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Asiakas/Asiakas.php' class="active">Asiakas</a></li>
            <li class="navlist"><label ></label>
        </ul>
        <ol class="sidelist">
            <li class="sideobj"><a class="not-active" href="/~jj421960/TikoHarkka/Etusivu/index.php">Etusivu</a></li>
            <li class="sideobj"><a class="active" href="/~jj421960/TikoHarkka/Etusivu/HintaArvio.php" >Hinta arvio</a></li>
            <li class="sideobj"><a class="active" href="/~jj421960/TikoHarkka/Etusivu/Laskut.php" >Laskut</a></li>
        </ol>
        <h2>Muistutus- ja karhulaskujen muodostaminen</h2>

        <form action="index.php" method="post" class="karhulasku_id">

            <?php if (isset($viesti)) echo '<p style="color:red">'.$viesti.'</p>'; ?>

            <input type="submit" name="laskutus" value="Tarkista ja muodosta laskut" />
        </form>
    </body>
</html>

    