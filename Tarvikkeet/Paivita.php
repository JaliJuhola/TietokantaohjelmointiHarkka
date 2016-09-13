<?php

$y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=jj421960 user=jj421960 password=salasana";


$yhteys = pg_connect($y_tiedot) or die("Tietokantayhteyden luominen epäonnistui.");

if (isset($_POST['tallenna'])) {
   
  $tarvike_tiedot = $_POST['tarvikkeet'];
  
  if (!empty($tarvike_tiedot)) {
    
    pg_query('BEGIN');

    $tarvikkeet = preg_split('/\r\n|\n|\r/', $tarvike_tiedot, NULL, PREG_SPLIT_NO_EMPTY);
    for ($i = 0; $i < count($tarvikkeet); $i++) {

      $attribuutit = preg_split('/\s/', $tarvikkeet[$i], NULL, PREG_SPLIT_NO_EMPTY);
      
      $tarvike_poisto = "UPDATE tiko_ht.tarvike SET poistettu = true WHERE nimi = $1";
      $tarvike_poisto_paivitys = pg_query_params($tarvike_poisto, array($attribuutit[0]));
      
      $tarvike_lisays = "INSERT INTO tiko_ht.tarvike (nimi, ostohinta, myyntihinta, yksikko, kirjallisuutta)
                         VALUES ($1, $2, $3, $4, $5)";
      $tarvike_paivitys = pg_query_params($tarvike_lisays, $attribuutit);
      
      if (!$tarvike_poisto_paivitys || !$tarvike_paivitys)
        break;
    }
    
    if ($tarvike_poisto_paivitys && $tarvike_paivitys)
      $viesti = 'Tarvikkeet lisätty.';
    else
      $viesti = 'Tarvikkeita ei lisätty: ' . pg_last_error();

    pg_query('COMMIT');
    
  }
  else {
    $viesti = 'Tarvikkeiden tiedot puuttuvat.';
  }
}

pg_close($yhteys);

?>
<html>
    <head>
        <title>Paivita tarvikkeet</title>
        <meta charset="iso-8859-1">
        <link rel="stylesheet" type="text/css" href="Navbar.css">
    </head>
    <body>
        <div id="ylatunniste">

            <h1>Sähkötärsky TMI</h1>
        </div>
        <ul class ="navbar">
            <li class="navlist"><label ></label>
            <li class="navlist"><a href="/~jj421960/TikoHarkka/Etusivu/index.php" style="margin-left: 120px;" class="active">Etusivu</a></li>
            <li class="navlist"><a href="/~jj421960/TikoHarkka/Kohteet/kohteet.php" class="active">Kohteet</a></li>
            <li class="navlist"><a href="/~jj421960/TikoHarkka/Tarvikkeet/tarvikkeet.php" class="not-active">Tarvikkeet</a></li>
            <li class="navlist"><a href="/~jj421960/TikoHarkka/Asiakas/Asiakas.php" class="active">Asiakas</a></li>
            <li class="navlist"><label ></label>
        </ul>
        <ol class="sidelist">
            <li class="sideobj"><a class="active" href="/~jj421960/TikoHarkka/Tarvikkeet/tarvikkeet.php">Tarvikkeet</a></li>
            <li class="sideobj"><a class="not-active" href="/~jj421960/TikoHarkka/Tarvikkeet/Paivita.php" >Lisää tarvikkeita</a></li>
        </ol>
        <div style="margin-left: 30%;">
            <h1>Uuden tarvikehinnaston lisääminen</h1>
            Vihje: Tarvikkeiden tiedot annettava muodossa "nimi ostohinta myyntihinta yksikkö kirjallisuutta"
            ja kunkin tarvikkeen tiedot rivinvaihdolla erotettuina.
            <form action="Paivita.php" method="post">

                <?php if (isset($viesti)) echo '<p style="color:red">'.$viesti.'</p>'; ?>

                <!-- Tarvikkeiden tiedot annettava muodossa "nimi ostohinta myyntihinta yksikkÃ¶"
                     ja kunkin tarvikkeen tiedot rivinvaihdolla erotettuina. -->
                <textarea name="tarvikkeet" rows="10" cols="100"></textarea>
                <br />
                <input type="submit" name="tallenna" value="Lisää tarvikkeet" />
        </div>

    </body>
</html>