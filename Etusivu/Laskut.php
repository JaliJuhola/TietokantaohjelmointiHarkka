<?php

$y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=jj421960 user=jj421960 password=salasana";

$yhteys = pg_connect($y_tiedot) or die("Tietokantayhteyden luominen epäonnistui.");

if (isset($_POST['tallenna'])) { // Laskun muodostaminen työkohteelle.
   
  $tyokohde_id = $_POST['tyokohde_id'];
  
  if (!empty($tyokohde_id)) {
    
    pg_query('BEGIN');

    // Merkitään työkohde, josta lasku muodostetaan, valmiiksi.
    $tyokohde_valmis = "UPDATE tiko_ht.tyokohde SET valmis = true WHERE tyokohde_id = $1";
    $tyokohde_paivitys = pg_query_params($tyokohde_valmis, array($tyokohde_id));
  
    // Luetaan nykyinen päivämäärä.
    $paivamaara = pg_escape_string(date("Y-m-d"));
    
    // Haetaan arvonlisäveroprosentit kirjallisuudelle ja muille.
    $muu = 0; // Muiden alv
    $kirjallisuus = 0; // Kirjallisuuden alv
    $alvit = pg_query("SELECT ryhma, prosentti FROM tiko_ht.alv");
    while ($alv = pg_fetch_row($alvit)){
      if ($alv[0] == 'muu') {
        $muu = $alv[1];
      } else if ($alv[0] == 'kirjallisuus') {
        $kirjallisuus = $alv[1];
      }
    }
    
    // tuntityö, määrä, alkuperäinen hinta, alvprosentti, alv, alviton hinta,
    // aleprosentti, alennettu hinta, kaikki yhteensä
    $tunti_hintaerittelyt = array();
    
    // Tuntitöiden yhteishinta (eli kotitalousvähennykseen kelpaava osuus)
    $tyon_osuus = 0;
    $tuntityot_kysely = "SELECT maara, alennusprosentti, hinta, tyyppi
                         FROM tiko_ht.tyokohde_tuntityo NATURAL JOIN tiko_ht.tuntityo
                         WHERE tyokohde_id = $1";
    $tuntityot = pg_query_params($tuntityot_kysely, array($tyokohde_id));
    while ($rivi = pg_fetch_row($tuntityot)) {
      if ($rivi[0] > 0) { // Onko tuntityyppiä käytetty ollenkaan?
        $hinta = $rivi[2]; // Verollinen hinta
        $alv_maara = round($hinta * $muu / (100 + $muu), 2); // Arvonlisäveron määrä
        $alviton_hinta = $hinta - $alv_maara; // Veroton hinta
        $ale_prosentti = $rivi[1]; // Alennusprosentti
        $ale_hinta = round($alviton_hinta - $alviton_hinta / $ale_prosentti + $alv_maara, 2); // Alennettu hinta
        $maara = $rivi[0]; // Määrä
        $yhteensa = $ale_hinta * $maara; // Hinta yhteensä
        $tyon_osuus += $yhteensa;
        
        $tunti_hintaerittelyt[] = array($rivi[3], $hinta, $muu, $alv_maara, $alviton_hinta,
                                        $ale_prosentti, $ale_hinta, $maara, $yhteensa);
      }
    }
    
    // tarvike, määrä, alkuperäinen hinta, alvprosentti, alv, alviton hinta,
    // aleprosentti, alennettu hinta, kaikki yhteensä
    $tarvike_hintaerittelyt = array();
    
    // Tarvikkeiden yhteishinta
    $tarvikkeet_maksettava = 0;
    $tarvikkeet_kysely = "SELECT maara, alennusprosentti, myyntihinta, kirjallisuutta, nimi
                          FROM tiko_ht.tarvike_tyokohde NATURAL JOIN tiko_ht.tarvike
                          WHERE tyokohde_id = $1";
    $tarvikkeet = pg_query_params($tarvikkeet_kysely, array($tyokohde_id));
    while ($rivi = pg_fetch_row($tarvikkeet)) {
      $hinta = $rivi[2]; // Verollinen hinta
      $alv_maara; // Arvonlisäveron määrä
      $alv_prosentti; // Arvonlisäveroprosentti
      
      if ($rivi[3] == 't') { // Onko kirjallisuutta?
        $alv_maara = round($hinta * $kirjallisuus / (100 + $kirjallisuus), 2);
        $alv_prosentti = $kirjallisuus;
      } else {
        $alv_maara = round($hinta * $muu / (100 + $muu), 2);
        $alv_prosentti = $muu;
      }
      
      $alviton_hinta = $hinta - $alv_maara; // Veroton hinta
      $ale_prosentti = $rivi[1]; // Alennusprosentti
      $ale_hinta = round($alviton_hinta - $alviton_hinta / $ale_prosentti + $alv_maara, 2); // Alennettu hinta
      $maara = $rivi[0]; // Määrä
      $yhteensa = $ale_hinta * $maara; // Hinta yhteensä
      $tarvikkeet_maksettava += $yhteensa;
      
      $tarvike_hintaerittelyt[] = array($rivi[4], $hinta, $alv_prosentti, $alv_maara, $alviton_hinta,
                                        $ale_prosentti, $ale_hinta, $maara, $yhteensa);
    }
    
    // Tarkistetaan onko työkohteen tyyppi urakkatyö.
    // Jos on, niin työn osuus on urakkahinta - tarvikkeiden osuus
    $urakka_kysely = "SELECT urakkahinta FROM tiko_ht.tyokohde WHERE tyokohde_id = $1";
    $urakkahinta = pg_fetch_row(pg_query_params($urakka_kysely, array($tyokohde_id)))[0];
    if (!is_null($urakkahinta)) {
      $tyon_osuus = $urakkahinta - $tarvikkeet_maksettava;
    }
    
    // Hinta yhteensä
    $maksettava = $tyon_osuus + $tarvikkeet_maksettava;
    
    // Luetaan laskun maksuaika.
    $maksuaika = intval($_POST['maksuaika']);
    
    $lasku_muodostus = "INSERT INTO tiko_ht.lasku (paivamaara, maksettava, kotitalousvahennys,
                        maksuaika, tyokohde_id)
                        VALUES ($1, $2, $3, $4, $5)";
    $lasku_tiedot = array($paivamaara, $maksettava, $tyon_osuus, $maksuaika, $tyokohde_id);
    $lasku_paivitys = pg_query_params($lasku_muodostus, $lasku_tiedot);
    
    // Tarkistetaan työkohdepäivityksen ja laskun muodostamisen onnistuminen.
    if ($tyokohde_paivitys && $lasku_paivitys) {
      $viesti = ' Muodostettiin seuraava lasku: <br />';
      
      // Haetaan ja luetaan muodostetun laskun tunnus.
      $lasku = pg_query("SELECT lasku_id FROM tiko_ht.lasku
                         WHERE lasku_id = CURRVAL('tiko_ht.lasku_id_sekvenssi')");
      $lasku_id = pg_fetch_row($lasku)[0];
      
      // Haetaan ja luetaan sen työkohteen tiedot, josta lasku muodostettiin.
      $tyokohde_kysely = "SELECT nimi, osoite, urakkahinta FROM tiko_ht.tyokohde
                          WHERE tyokohde_id = $1";
      $tyokohde_tiedot = pg_fetch_row(pg_query_params($tyokohde_kysely, array($tyokohde_id)));
      $tyokohde_nimi = $tyokohde_tiedot[0];
      $tyokohde_osoite = $tyokohde_tiedot[1];
      $tyokohde_urakkahinta = $tyokohde_tiedot[2];
      
      // Haetaan ja luetaan sen asiakkaan tiedot, jonka työkohteesta lasku muodostettiin.
      $asiakas_kysely = "SELECT asiakas.asiakas_id, asiakas.osoite, asiakkaan_tyyppi
                         FROM tiko_ht.asiakas INNER JOIN tiko_ht.tyokohde
                         ON tiko_ht.asiakas.asiakas_id = tiko_ht.tyokohde.asiakas_id
                         WHERE tyokohde_id = $1";
      $asiakas_tiedot = pg_fetch_row(pg_query_params($asiakas_kysely, array($tyokohde_id)));
      $asiakas_id = $asiakas_tiedot[0];
      $asiakas_osoite = $asiakas_tiedot[1];
      $asiakas_tyyppi = $asiakas_tiedot[2];
      
      $etunimi = $sukunimi = $y_tunnus = $nimi = '';
      if ($asiakas_tyyppi == 't') {
        $asiakas_tyyppi = 'Henkilö';
        $henkilo_tiedot = pg_fetch_row(pg_query("SELECT etunimi, sukunimi
                                                 FROM tiko_ht.asiakas INNER JOIN tiko_ht.henkilo
                                                 ON asiakas_id = henkilo_id
                                                 WHERE asiakas_id = '$asiakas_id'"));
        $etunimi = $henkilo_tiedot[0];
        $sukunimi = $henkilo_tiedot[1];
      } else {
        $asiakas_tyyppi = 'Yritys';
        $yritys_tiedot = pg_fetch_row(pg_query("SELECT y_tunnus, nimi
                                                FROM tiko_ht.asiakas INNER JOIN tiko_ht.yritys
                                                ON asiakas_id = yritys_id
                                                WHERE asiakas_id = '$asiakas_id'"));
        $y_tunnus = $yritys_tiedot[0];
        $nimi = $yritys_tiedot[1];
      }
      
      // Muodostetun laskun tiedot laskutiedot-merkkijonoon.
      $laskutiedot = '<tr><td>Laskun tunnus: </td><td>' . $lasku_id . '</td></tr>';
      $laskutiedot .= '<tr><td>Päivämäärä: </td><td>' . $lasku_tiedot[0] . '</td></tr>';
      $laskutiedot .= '<tr><td>Maksettava: </td><td>' . $lasku_tiedot[1] . '</td></tr>';
      $laskutiedot .= '<tr><td>Työn osuus (kotitalousvähennykseen kelpaava): </td><td>' . $lasku_tiedot[2] . '</td></tr>';
      $laskutiedot .= '<tr><td>Tarvikkeiden osuus: </td><td>' . $tarvikkeet_maksettava . '</td></tr>';
      $laskutiedot .= '<tr><td>Maksuaika: </td><td>' . $lasku_tiedot[3] . '</td></tr>';
      
      $laskutiedot .= '<tr><td>Työkohteen tunnus: </td><td>' . $tyokohde_id . '</td></tr>';
      $laskutiedot .= '<tr><td>Työkohteen nimi: </td><td>' . $tyokohde_nimi . '</td></tr>';
      $laskutiedot .= '<tr><td>Työkohteen osoite: </td><td>' . $tyokohde_osoite . '</td></tr>';
      if (is_null($tyokohde_urakkahinta))
        $laskutiedot .= '<tr><td>Työsuoritteen tyyppi: </td><td>Tuntityö</td></tr>';
      else
        $laskutiedot .= '<tr><td>Työsuoritteen tyyppi: </td><td>Urakka</td></tr>';
      
      $laskutiedot .= '<tr><td>Asiakkaan tunnus: </td><td>' . $asiakas_id . '</td></tr>';
      $laskutiedot .= '<tr><td>Asiakkaan osoite: </td><td>' . $asiakas_osoite . '</td></tr>';
      $laskutiedot .= '<tr><td>Asiakkaan tyyppi: </td><td>' . $asiakas_tyyppi . '</td></tr>';
      if ($asiakas_tyyppi == 'Henkilö') {
        $laskutiedot .= '<tr><td>Asiakkaan etunimi: </td><td>' . $etunimi . '</td></tr>';
        $laskutiedot .= '<tr><td>Asiakkaan sukunimi: </td><td>' . $sukunimi . '</td></tr>';
      } else {
        $laskutiedot .= '<tr><td>Yrityksen y-tunnus: </td><td>' . $y_tunnus . '</td></tr>';
        $laskutiedot .= '<tr><td>Yrityksen nimi: </td><td>' . $nimi . '</td></tr>';
      }
      
      if (is_null($tyokohde_urakkahinta)) { // Työtunteja ei eritellä urakkatöissä.
        $laskutiedot .= '<tr><td>Työtunnit: </td></tr>';
        $laskutiedot .= '<tr><th>Tuntityö</th><th>Alkuperäinen hinta</th><th>Arvonlisäveroprosentti</th>
                         <th>Arvonlisävero</th><th>Veroton hinta</th><th>Alennusprosentti</th>
                         <th>Alennettu hinta</th><th>Määrä</th><th>Yhteensä</th></tr>';
        foreach ($tunti_hintaerittelyt as $tunti_erittely) {
          if (is_array($tunti_erittely)) {
            $laskutiedot .= '<tr>';
            foreach ($tunti_erittely as $tunti_tieto) {
              if ($tunti_tieto == 'tyo')
                $laskutiedot .= '<td>Työtunti</td>';
              else if ($tunti_tieto == 'suunnittelu')
                $laskutiedot .= '<td>Suunnittelutunti</td>';
              else if ($tunti_tieto == 'aputyo')
                $laskutiedot .= '<td>Aputyötunti</td>';
              else
                $laskutiedot .= '<td>' . $tunti_tieto . '</td>';
            }
            $laskutiedot .= '</tr>';
          }
        }
      }
      
      $laskutiedot .= '<tr><td>Käytetyt tarvikkeet: </td></tr>';
      $laskutiedot .= '<tr><th>Tarvike</th><th>Alkuperäinen hinta</th><th>Arvonlisäveroprosentti</th>
                       <th>Arvonlisävero</th><th>Veroton hinta</th><th>Alennusprosentti</th>
                       <th>Alennettu hinta</th><th>Määrä</th><th>Yhteensä</th></tr>';
      foreach ($tarvike_hintaerittelyt as $tarvike_erittely) {
        if (is_array($tarvike_erittely)) {
          $laskutiedot .= '<tr>';
          foreach ($tarvike_erittely as $tarvike_tieto) {
            $laskutiedot .= '<td>' . $tarvike_tieto . '</td>';
          }
          $laskutiedot .= '</tr>';
        }
      }
      
    }
    else {
      $viesti = 'Laskua ei muodostettu: ' . pg_last_error();
    }
    
    pg_query('COMMIT');
    
  }
  else {
    $viesti = 'Anna työkohteen tunnnus.';
  }
}

else if (isset($_POST['urakkatarjouslasku'])) { // Laskun/laskujen muodostaminen urakkatarjouksesta.
  
  $urakkatarjous_id = $_POST['urakkatarjous_id'];
  
  if (!empty($urakkatarjous_id)) {
    
    pg_query('BEGIN');
    
    $urakkatarjous_kysely = "SELECT kokonaishinta, alennusprosentti, tyokohde_id
                             FROM tiko_ht.urakkatarjous WHERE urakkatarjous_id = $1";
    $urakkatarjous_tiedot = pg_fetch_row(pg_query_params($urakkatarjous_kysely, array($urakkatarjous_id)));
    $kokonaishinta = $urakkatarjous_tiedot[0];
    $tarjous_ale_prosentti = $urakkatarjous_tiedot[1];
    $tyokohde_id = $urakkatarjous_tiedot[2];
    
    // Merkitään työkohde, johon urakkatarjous liittyy, valmiiksi.
    $tyokohde_valmis = "UPDATE tiko_ht.tyokohde SET valmis = true WHERE tyokohde_id = $1";
    $tyokohde_paivitys = pg_query_params($tyokohde_valmis, array($tyokohde_id));
    
    // Luetaan nykyinen päivämäärä.
    $paivamaara = pg_escape_string(date("Y-m-d"));
    
    // Haetaan arvonlisäveroprosentit kirjallisuudelle ja muille.
    $muu = 0; // Muiden alv
    $kirjallisuus = 0; // Kirjallisuuden alv
    $alvit = pg_query("SELECT ryhma, prosentti FROM tiko_ht.alv");
    while ($alv = pg_fetch_row($alvit)){
      if ($alv[0] == 'muu') {
        $muu = $alv[1];
      } else if ($alv[0] == 'kirjallisuus') {
        $kirjallisuus = $alv[1];
      }
    }
    
    $tarvike_hintaerittelyt = array();
    
    // Tarvikkeiden yhteishinta
    $tarvikkeet_maksettava = 0;
    $tarvikkeet_kysely = "SELECT maara, alennusprosentti, myyntihinta, kirjallisuutta, nimi
                          FROM tiko_ht.tarvike_urakkatarjous NATURAL JOIN tiko_ht.tarvike
                          WHERE urakkatarjous_id = $1";
    $tarvikkeet = pg_query_params($tarvikkeet_kysely, array($urakkatarjous_id));
    while ($rivi = pg_fetch_row($tarvikkeet)) {
      $hinta = $rivi[2]; // Verollinen hinta
      $alv_maara; // Arvonlisäveron määrä
      $alv_prosentti; // Arvonlisäveroprosentti
      
      if ($rivi[3] == 't') { // Onko kirjallisuutta?
        $alv_maara = round($hinta * $kirjallisuus / (100 + $kirjallisuus), 2);
        $alv_prosentti = $kirjallisuus;
      } else {
        $alv_maara = round($hinta * $muu / (100 + $muu), 2);
        $alv_prosentti = $muu;
      }
      
      $alviton_hinta = $hinta - $alv_maara; // Veroton hinta
      $ale_prosentti = $rivi[1]; // Alennusprosentti
      $ale_hinta = round($alviton_hinta - $alviton_hinta / $ale_prosentti + $alv_maara, 2); // Alennettu hinta
      $maara = $rivi[0]; // Määrä
      $yhteensa = $ale_hinta * $maara; // Hinta yhteensä
      $tarvikkeet_maksettava += $yhteensa;
      
      $tarvike_hintaerittelyt[] = array($rivi[4], $hinta, $alv_prosentti, $alv_maara, $alviton_hinta,
                                        $ale_prosentti, $ale_hinta, $maara, $yhteensa);
    }
    
    $tyon_osuus = $kokonaishinta - $tarvikkeet_maksettava; // Työn osuus kokonaishinnasta
    $tyon_osuus_alv = round($tyon_osuus * $muu / (100 + $muu), 2); // Työn arvonlisäveron määrä
    $alviton_tyon_osuus = $tyon_osuus - $tyon_osuus_alv; // Työn veroton hinta
    $ale_tyon_osuus = round($alviton_tyon_osuus - $alviton_tyon_osuus / $tarjous_ale_prosentti + $tyon_osuus_alv, 2); // Työn alennettu hinta
    $kokonaishinta -= ($tyon_osuus - $ale_tyon_osuus); // Kokonaishinta vastaamaan työn alennettua hintaa.
    
    // Luetaan laskun/laskujen maksuaika.
    $maksuaika = intval($_POST['urakkatarjous_maksuaika']);
    
    $lasku_tiedot_1 = $lasku_tiedot_2 = $lasku_id_1 = $lasku_id_2 = '';
    $lasku_muodostus = "INSERT INTO tiko_ht.lasku (paivamaara, maksettava, kotitalousvahennys,
                        maksuaika, tyokohde_id) VALUES ($1, $2, $3, $4, $5)";
    
    // Tarkistetaan onko urakka laskutettava kahdessa erässä.
    $laskutus = $_POST['laskutus'];
    if ($laskutus == 'kylla') {
      
      $toinen_pvm = $_POST['toinen_laskutuspvm']; // Luetaan toisen laskun päivämäärä.
      
      $ekamaksettava = $tokamaksettava = $kokonaishinta / 2; // Jaetaan laskujen maksettava tasan.
      // Jos jaossa ei tule kahden desimaalin tarkkuudella olevia maksettavia on
      // toinen pyöristettävä ylöspäin ja toinen alaspäin.
      $ekamaksettava = round($ekamaksettava, 2, PHP_ROUND_HALF_UP);
      $tokamaksettava = round($tokamaksettava, 2, PHP_ROUND_HALF_DOWN);
      
      // Muodostetaan ensimmäinen lasku.
      $lasku_tiedot_1 = array($paivamaara, $ekamaksettava, $ale_tyon_osuus, $maksuaika, $tyokohde_id);
      $lasku_paivitys = pg_query_params($lasku_muodostus, $lasku_tiedot_1);
      
      // Haetaan ja luetaan muodostetun ensimmäisen laskun tunnus.
      $lasku = pg_query("SELECT lasku_id FROM tiko_ht.lasku
                         WHERE lasku_id = CURRVAL('tiko_ht.lasku_id_sekvenssi')");
      $lasku_id_1 = pg_fetch_row($lasku)[0];
      
      // Muodostetaan toinen lasku.
      if ($lasku_paivitys) {
        $lasku_tiedot_2 = array($toinen_pvm, $tokamaksettava, $ale_tyon_osuus, $maksuaika, $tyokohde_id);
        $lasku_paivitys = pg_query_params($lasku_muodostus, $lasku_tiedot_2);
        
        // Haetaan ja luetaan muodostetun toisen laskun tunnus.
        $lasku = pg_query("SELECT lasku_id FROM tiko_ht.lasku
                          WHERE lasku_id = CURRVAL('tiko_ht.lasku_id_sekvenssi')");
        $lasku_id_2 = pg_fetch_row($lasku)[0];
      }
    }
    else {
      $lasku_tiedot_1 = array($paivamaara, $kokonaishinta, $ale_tyon_osuus, $maksuaika, $tyokohde_id);
      $lasku_paivitys = pg_query_params($lasku_muodostus, $lasku_tiedot_1);
      
      // Haetaan ja luetaan muodostetun laskun tunnus.
      $lasku = pg_query("SELECT lasku_id FROM tiko_ht.lasku
                         WHERE lasku_id = CURRVAL('tiko_ht.lasku_id_sekvenssi')");
      $lasku_id_1 = pg_fetch_row($lasku)[0];
    }
    
    if ($tyokohde_paivitys && $lasku_paivitys) {
      
      // Haetaan ja luetaan sen työkohteen tiedot, josta lasku muodostettiin.
      $tyokohde_kysely = "SELECT nimi, osoite FROM tiko_ht.tyokohde
                          WHERE tyokohde_id = $1";
      $tyokohde_tiedot = pg_fetch_row(pg_query_params($tyokohde_kysely, array($tyokohde_id)));
      $tyokohde_nimi = $tyokohde_tiedot[0];
      $tyokohde_osoite = $tyokohde_tiedot[1];
      
      // Haetaan ja luetaan sen asiakkaan tiedot, jonka työkohteesta lasku muodostettiin.
      $asiakas_kysely = "SELECT asiakas.asiakas_id, asiakas.osoite, asiakkaan_tyyppi
                         FROM tiko_ht.asiakas INNER JOIN tiko_ht.tyokohde
                         ON tiko_ht.asiakas.asiakas_id = tiko_ht.tyokohde.asiakas_id
                         WHERE tyokohde_id = $1";
      $asiakas_tiedot = pg_fetch_row(pg_query_params($asiakas_kysely, array($tyokohde_id)));
      $asiakas_id = $asiakas_tiedot[0];
      $asiakas_osoite = $asiakas_tiedot[1];
      $asiakas_tyyppi = $asiakas_tiedot[2];
      
      $etunimi = $sukunimi = $y_tunnus = $nimi = '';
      if ($asiakas_tyyppi == 't') {
        $asiakas_tyyppi = 'Henkilö';
        $henkilo_tiedot = pg_fetch_row(pg_query("SELECT etunimi, sukunimi
                                                 FROM tiko_ht.asiakas INNER JOIN tiko_ht.henkilo
                                                 ON asiakas_id = henkilo_id
                                                 WHERE asiakas_id = '$asiakas_id'"));
        $etunimi = $henkilo_tiedot[0];
        $sukunimi = $henkilo_tiedot[1];
      } else {
        $asiakas_tyyppi = 'Yritys';
        $yritys_tiedot = pg_fetch_row(pg_query("SELECT y_tunnus, nimi
                                                FROM tiko_ht.asiakas INNER JOIN tiko_ht.yritys
                                                ON asiakas_id = yritys_id
                                                WHERE asiakas_id = '$asiakas_id'"));
        $y_tunnus = $yritys_tiedot[0];
        $nimi = $yritys_tiedot[1];
      }
      
      if ($laskutus == 'kylla')
        $ut_viesti = 'Laskutus kahdessa erässä. Yhteiset tiedot: <br />';
      else
        $ut_viesti = 'Muodostettiin seuraava lasku: <br />';
      
      // Muodostetun laskun tiedot laskutiedot-merkkijonoon.
      $ut_laskutiedot;
      if($laskutus == 'ei') {
        $ut_laskutiedot .= '<tr><td>Laskun tunnus: </td><td>' . $lasku_id_1 . '</td></tr>';
        $ut_laskutiedot .= '<tr><td>Päivämäärä: </td><td>' . $lasku_tiedot_1[0] . '</td></tr>';
        $ut_laskutiedot .= '<tr><td>Maksettava: </td><td>' . $lasku_tiedot_1[1] . '</td></tr>';
      } else {
        $ut_laskutiedot .= '<tr><td>Maksettava yhteensä: </td><td>' . $kokonaishinta . '</td></tr>';
      }
      
      $ut_laskutiedot .= '<tr><td>Työn osuus: </td><td>' . $tyon_osuus . '</td></tr>';
      $ut_laskutiedot .= '<tr><td>Työn arvonlisäveroprosentti: </td><td>' . $muu . '</td></tr>';
      $ut_laskutiedot .= '<tr><td>Työn arvonlisävero: </td><td>' . $tyon_osuus_alv . '</td></tr>';
      $ut_laskutiedot .= '<tr><td>Työn veroton hinta: </td><td>' . $alviton_tyon_osuus . '</td></tr>';
      $ut_laskutiedot .= '<tr><td>Työn alennusprosentti: </td><td>' . $tarjous_ale_prosentti . '</td></tr>';
      $ut_laskutiedot .= '<tr><td>Työn lopullinen hinta (kotitalousvähennykseen kelpaava): </td><td>' . $ale_tyon_osuus . '</td></tr>';
      
      $ut_laskutiedot .= '<tr><td>Tarvikkeiden osuus: </td><td>' . $tarvikkeet_maksettava . '</td></tr>';
      $ut_laskutiedot .= '<tr><td>Maksuaika: </td><td>' . $lasku_tiedot_1[3] . '</td></tr>';
      
      $ut_laskutiedot .= '<tr><td>Työkohteen tunnus: </td><td>' . $tyokohde_id . '</td></tr>';
      $ut_laskutiedot .= '<tr><td>Työkohteen nimi: </td><td>' . $tyokohde_nimi . '</td></tr>';
      $ut_laskutiedot .= '<tr><td>Työkohteen osoite: </td><td>' . $tyokohde_osoite . '</td></tr>';
      $ut_laskutiedot .= '<tr><td>Työsuoritteen tyyppi: </td><td>Urakka</td></tr>';
      
      $ut_laskutiedot .= '<tr><td>Asiakkaan tunnus: </td><td>' . $asiakas_id . '</td></tr>';
      $ut_laskutiedot .= '<tr><td>Asiakkaan osoite: </td><td>' . $asiakas_osoite . '</td></tr>';
      $ut_laskutiedot .= '<tr><td>Asiakkaan tyyppi: </td><td>' . $asiakas_tyyppi . '</td></tr>';
      if ($asiakas_tyyppi == 'Henkilö') {
        $ut_laskutiedot .= '<tr><td>Asiakkaan etunimi: </td><td>' . $etunimi . '</td></tr>';
        $ut_laskutiedot .= '<tr><td>Asiakkaan sukunimi: </td><td>' . $sukunimi . '</td></tr>';
      } else {
        $ut_laskutiedot .= '<tr><td>Yrityksen y-tunnus: </td><td>' . $y_tunnus . '</td></tr>';
        $ut_laskutiedot .= '<tr><td>Yrityksen nimi: </td><td>' . $nimi . '</td></tr>';
      }
      
      $ut_laskutiedot .= '<tr><td>Käytetyt tarvikkeet: </td></tr>';
      $ut_laskutiedot .= '<tr><th>Tarvike</th><th>Alkuperäinen hinta</th><th>Arvonlisäveroprosentti</th>
                       <th>Arvonlisävero</th><th>Veroton hinta</th><th>Alennusprosentti</th>
                       <th>Alennettu hinta</th><th>Määrä</th><th>Yhteensä</th></tr>';
      foreach ($tarvike_hintaerittelyt as $tarvike_erittely) {
        if (is_array($tarvike_erittely)) {
          $ut_laskutiedot .= '<tr>';
          foreach ($tarvike_erittely as $tarvike_tieto) {
            $ut_laskutiedot .= '<td>' . $tarvike_tieto . '</td>';
          }
          $ut_laskutiedot .= '</tr>';
        }
      }
      
      if ($laskutus == 'kylla') {
        $ut_laskutiedot .= '<tr><th>Laskujen erilliset tiedot:</th></tr>';
        $ut_laskutiedot .= '<tr><th>Lasku</th><th>Laskun tunnus</th><th>Laskun päivämäärä</th><th>Maksettava</th></tr>';
        $ut_laskutiedot .= '<tr><td>Ensimmäinen lasku</td><td>' . $lasku_id_1 . '</td>';
        $ut_laskutiedot .= '<td>' . $lasku_tiedot_1[0] . '</td><td>' . $lasku_tiedot_1[1] . '</td></tr>';
        $ut_laskutiedot .= '<tr><td>Toinen lasku</td><td>' . $lasku_id_2 . '</td>';
        $ut_laskutiedot .= '<td>' . $lasku_tiedot_2[0] . '</td><td>' . $lasku_tiedot_2[1] . '</td></tr>';
      }
     
    }
    else {
      $ut_viesti = 'Laskua ei muodostettu: ' . pg_last_error();
    }
    
    pg_query('COMMIT');
  
  }
  else {
    $ut_viesti = 'Anna urakkatarjouksen tunnus.';
  }
}

pg_close($yhteys);

?>

<html>
    <head>
        <meta charset="iso-8859-1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="Navbar.css">
        <title>Raportit 2, 3 ja 5: Laskun muodostaminen</title>
        <script type="text/javascript"
                src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js">
        </script>
        <script type="text/javascript">
            $(document).ready(function () {

                $('#toinen_pvm').hide();
                $('#urakkatarjouslasku').hide();

                $('#ei').click(function () {
                    $('#toinen_pvm').hide();
                    $('#urakkatarjouslasku').show();
                });

                $('#kylla').click(function () {
                    $('#toinen_pvm').show();
                    $('#urakkatarjouslasku').show();
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
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Etusivu/index.php' style="margin-left: 120px;" class="not-active">Etusivu</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Kohteet/kohteet.php' class="active">Kohteet</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Tarvikkeet/tarvikkeet.php' class="active">Tarvikkeet</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Asiakas/Asiakas.php' class="active">Asiakas</a></li>
            <li class="navlist"><label ></label>
        </ul>
        <ol class="sidelist">
            <li class="sideobj"><a class="active" href="/~jj421960/TikoHarkka/Etusivu/index.php">Etusivu</a></li>
            <li class="sideobj"><a class="active" href="/~jj421960/TikoHarkka/Etusivu/HintaArvio.php" >Hinta arvio</a></li>
            <li class="sideobj"><a class="not-active" href="/~jj421960/TikoHarkka/Etusivu/Laskut.php" >Laskut</a></li>
        </ol>
        <div style="margin-left: 400px;">
            <h3>Laskun muodostaminen työkohteesta</h3>
            <form action="Laskut.php" method="post">

                <?php if (isset($viesti)) echo '<p style="color:red">'.$viesti.'</p>'; ?>

                <table><?php if (isset($laskutiedot)) echo $laskutiedot; ?></table>

                <table>
                    <tr>
                        <td>Työkohteen tunnus</td>
                        <td><input type="text" name="tyokohde_id" value="" /></td>
                    </tr>
                    <tr>
                        <td>Laskun maksuaika</td>
                        <td><input type="text" name="maksuaika" value="30" /></td>
                    </tr>
                </table>

                <br />
                <input type="submit" name="tallenna" value="Muodosta työkohteesta lasku" />

                <h3>Laskun muodostaminen urakkatarjouksesta</h3>

                <?php if (isset($ut_viesti)) echo '<p style="color:red">'.$ut_viesti.'</p>'; ?>

                <table><?php if (isset($ut_laskutiedot)) echo $ut_laskutiedot; ?></table>

                <table>
                    <tr>
                        <td>Urakkatarjouksen tunnus</td>
                        <td><input type="text" name="urakkatarjous_id" value="" /></td>
                    </tr>
                    <tr>
                        <td>Laskun/laskujen maksuaika</td>
                        <td><input type="text" name="urakkatarjous_maksuaika" value="30" /></td>
                    </tr>
                    <tr>
                        <td>Laskutus kahdessa erässä?</td>
                        <td><input type="radio" name="laskutus" id="ei" value="ei"> Ei<td>
                        <td><input type="radio" name="laskutus" id="kylla" value="kylla"> Kyllä<td>
                    </tr>
                </table>

                <table id="toinen_pvm">
                    <tr>
                        <td>Anna toisen laskun päivämäärä muodossa: vuosi-kuukausi-päivä</td>
                        <td><input type="text" name="toinen_laskutuspvm" value=""></td>
                    </tr>
                </table>

                <br />
                <input type="submit" id="urakkatarjouslasku" name="urakkatarjouslasku" value="Muodosta urakkatarjouksesta lasku(t)" />

                </body>
        </div>
</html>