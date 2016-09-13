<?php

$y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=jj421960 user=jj421960 password=salasana";

$yhteys = pg_connect($y_tiedot) or die("Tietokantayhteyden luominen epäonnistui: " . pg_last_error());

if (isset($_POST['tallenna'])) {
  
  $asiakas_tyyppi = $_POST['asiakas_tyyppi'];
  $asiakas_osoite = $_POST['asiakas_osoite'];
  
  if (!empty($asiakas_tyyppi) && !empty($asiakas_osoite)) {
    
    $etunimi = $sukunimi = $y_tunnus = $nimi = true;
    
    if ($asiakas_tyyppi == 'henkilo') {
      $asiakas_tyyppi = 'true'; // true -> henkilöasiakas
      $etunimi = $_POST['etunimi'];
      $sukunimi = $_POST['sukunimi'];
    }
    else {
      $asiakas_tyyppi = 'false'; // false -> yritysasiakas
      $y_tunnus = $_POST['y_tunnus'];
      $nimi = $_POST['nimi'];
    }
    
    if (!empty($etunimi) && !empty($sukunimi) && !empty($y_tunnus) && !empty($nimi)) {
    
      pg_query('BEGIN');
      
      $asiakas_lisays = "INSERT INTO tiko_ht.asiakas (asiakkaan_tyyppi, osoite)
                         VALUES ($1, $2)";
      $paivitys = pg_query_params($asiakas_lisays, array($asiakas_tyyppi, $asiakas_osoite));
      
      if ($asiakas_tyyppi == 'true' && $paivitys) {
        $henkilo_lisays = "INSERT INTO tiko_ht.henkilo (henkilo_id, etunimi, sukunimi)
                           VALUES (CURRVAL('tiko_ht.asiakas_id_sekvenssi'), $1, $2)";
        $paivitys = pg_query_params($henkilo_lisays, array($etunimi, $sukunimi));
      }
      else if ($asiakas_tyyppi == 'false' && $paivitys) {
        $yritys_lisays = "INSERT INTO tiko_ht.yritys (yritys_id, y_tunnus, nimi)
                          VALUES (CURRVAL('tiko_ht.asiakas_id_sekvenssi'), $1, $2)";
        $paivitys = pg_query_params($yritys_lisays, array($y_tunnus, $nimi));
      }
      
      if ($paivitys)
        $viesti = 'Asiakas lisätty.';
      else
        $viesti = 'Asiakasta ei lisätty: ' . pg_last_error();
      
      pg_query('COMMIT');
    
    }
    else {
      $viesti = 'Pakollinen tieto puuttuu.';
    }
  }
  else {
    $viesti = 'Pakollinen tieto puuttuu.';
  }
  
}

pg_close($yhteys);

?>

<html>
  <head>
     <meta charset="iso-8859-1">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" type="text/css" href="Navbar.css">
    <title>Uuden asiakkaan lisääminen</title>
    <script type="text/javascript"
      src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js">
    </script>
    <script type="text/javascript">
    $(document).ready(function(){
      
      $('#yritys_tiedot').hide();
      $('#henkilo_tiedot').hide();
      $('#tallenna').hide();
      
      $('#henkilo').click(function() {
        $('#yritys_tiedot').hide();
        $('#henkilo_tiedot').show();
        $('#tallenna').show();
      });
      
      $('#yritys').click(function() {
        $('#henkilo_tiedot').hide();
        $('#yritys_tiedot').show();
        $('#tallenna').show();
      });
      
    });
    </script>
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
            <li class="sideobj" style="display:inline-block;"><a  class="active" href="/~jj421960/TikoHarkka/Asiakas/LisaaTyokohde.php" >Lisää työkohde</a></li>
            <li class="sideobj"><a class="not-active" href="/~jj421960/TikoHarkka/Asiakas/LisaaAsiakas.php" >Lisää asiakas</a></li>
        </ol>
        <div style="margin-left: 300px">
    <h1>Uuden asiakkaan lisääminen</h1>
    <form action="LisaaAsiakas.php" method="post">
    <?php if (isset($viesti)) echo '<p style="color:red">'.$viesti.'</p>'; ?>
    <table>
	    <tr>
        <td>Asiakkaan tyyppi</td>
        <td><input type="radio" name="asiakas_tyyppi" id="henkilo" value="henkilo"> Henkilö<td>
        <td><input type="radio" name="asiakas_tyyppi" id="yritys" value="yritys"> Yritys<td>
	    </tr>
	    <tr>
        <td>Asiakkaan osoite</td>
        <td colspan = '2'><input type="text" name="asiakas_osoite" value="" /></td>
	    </tr>
    </table>
    
    <table id="henkilo_tiedot">
      <tr>
        <td>Asiakkaan etunimi</td>
        <td><input type="text" name="etunimi" value="" /></td>
      </tr>
      <tr>
        <td>Asiakkaan sukunimi</td>
        <td><input type="text" name="sukunimi" value="" /></td>
      </tr>
    </table>
    
    <table id="yritys_tiedot">
      <tr>
        <td>Asiakasyrityksen y-tunnus</td>
        <td><input type="text" name="y_tunnus" value="" /></td>
      </tr>
      <tr>
        <td>Asiakasyrityksen nimi</td>
        <td><input type="text" name="nimi" value="" /></td>
      </tr>
    </table>

    <br />
    
    <input type="submit" id="tallenna" name="tallenna" value="Lisää asiakas" />
	</div>
  
  </body>
</html>