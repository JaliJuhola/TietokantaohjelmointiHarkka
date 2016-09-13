<?php
$y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=jj421960 user=jj421960 password=salasana";
$yhteys = pg_connect($y_tiedot) or die("Tietokantayhteyden luominen epÃ¤onnistui.");


?>

<html>
    <head>
        <title>Urakkatarjous</title> 
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
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Kohteet/kohteet.php' class="not-active">Kohteet</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Tarvikkeet/tarvikkeet.php' class="active">Tarvikkeet</a></li>
            <li class="navlist"><a href='/~jj421960/TikoHarkka/Asiakas/Asiakas.php' class="active">Asiakas</a></li>
            <li class="navlist"><label ></label>
        </ul>
 <ol class="sidelist">
            <li class="sideobj"><a class="active" href="/~jj421960/TikoHarkka/Kohteet/kohteet.php">Kohteet</a></li>
            <li class="sideobj"><a  class = "not-active" href="/~jj421960/TikoHarkka/Kohteet/urakkatarjous.php" >Urakkatarjous</a></li>
            <li class="sideobj"><a class="active" href="/~jj421960/TikoHarkka/Kohteet/LisaaTunteja.php" >Lisää tunteja</a></li>
        </ol>

       <div style="margin-left: 30%;">
            <h2>Urakkatarjoukset</h2>
            <form action="urakkatarjous.php" method="post">
            <b>Valitse urakkatarjous:</b>
            <?php
            $tyokohteet = "SELECT ut.urakkatarjous_id, tk.nimi
			               FROM tiko_ht.urakkatarjous AS ut, tiko_ht.tyokohde AS tk
						   WHERE ut.tyokohde_id = tk.tyokohde_id";
            $tulos = pg_query($tyokohteet);?>
            <select name="id_lista">
                <option value="">valitse kohde!</option>
                <?php while($row = pg_fetch_array($tulos)){ ?>
				
                        <option value="<?php echo $row['urakkatarjous_id']; ?>"><?php echo $row['urakkatarjous_id'] . ". " . $row['nimi'];?></option>
				
			<?php } ?> 
            </select> 
            <br/> <br/><br/>
            <input type="submit" value="Muodosta hinta-arvio" name="hinta_arvio">
        </form>
        <h3>Kohteen tiedot: </h3> <br/>
		
         <?php 
        if (isset($_POST['hinta_arvio'])) {
			$alennusprosentti = 0;

            pg_query("BEGIN");
            $ut_id= intval($_POST['id_lista']);
            $kohteen_tiedot = "SELECT tk.nimi, tk.osoite, tk.valmis, tk.tyokohde_id, ut.alennusprosentti
                               FROM tiko_ht.urakkatarjous AS ut, tiko_ht.tyokohde AS tk
                               WHERE ut.tyokohde_id = tk.tyokohde_id AND ut.urakkatarjous_id = $ut_id";			 
            $tuloss = pg_query($kohteen_tiedot);
            while($row = pg_fetch_array($tuloss)) { 
			     $alennusprosentti = intval($row['alennusprosentti']);
                 if($row['valmis'] == 'f') { 
                  echo "<b>Kohde on kesken.</b><br/>";
                }else {
                   echo "<b> Kohde on valmis </b> <br/>";
                 } ?>
                <b>Kohteen id:</b> <?php echo $row['tyokohde_id']; ?>
               <br/> <b>Kohteen nimi:</b> <?php echo $row['nimi']; ?>
               <br/> <b>Kohteen osoite:</b> <?php echo $row['osoite']; ?> <br/>
                <?php } ?>
                <h3>Asiakkaan tiedot: </h3><br/>
                <?php 
                $kohteen_tiedot = "SELECT asi.asiakas_id, asi.asiakkaan_tyyppi, asi.osoite, hk.etunimi, hk.sukunimi, yt.y_tunnus, yt.nimi
                                   FROM tiko_ht.asiakas AS asi, tiko_ht.henkilo AS hk, tiko_ht.yritys AS yt, tiko_ht.tyokohde AS tk, tiko_ht.urakkatarjous AS ut
                                   WHERE (asi.asiakas_id = hk.henkilo_id OR asi.asiakas_id = yt.yritys_id)
                                   AND asi.asiakas_id = tk.asiakas_id
                                   AND ut.tyokohde_id = tk.tyokohde_id
                                   AND ut.urakkatarjous_id = $ut_id;";
								   
                $tuloss = pg_query($kohteen_tiedot);		
                    if(empty($tuloss))	{
                        die("kohdetta ei loytynyt");
                        pg_query("ROLLBACK");	
                    }						
                while($row = pg_fetch_array($tuloss)) {?>
                    <?php if($row['asiakkaan_tyyppi'] == 'f')   { ?>
					
                        <b> Yrityksen id: </b>;
                        <?php echo " " . $row['asiakas_id']; ?>
                        <br/>
						
                        <b> Yrityksen osoite: </b>
                        <?php echo " " . $row['osoite']; ?>
                        <br/>
						
                        <b>Y-tunnus:</b 
                        <?php echo " " . $row['y_tunnus'] . "<br/>"; ?>
                        <b>Yrityksen nimi:</b>
                        <?php echo " " . $row['nimi']; ?>
                        <br/>

                    <?php } else { ?>
                        <b> Henkilon id: </b>
                        <?php echo " " . $row['asiakas_id']; ?>
                        <br/>
                        <b> Osoite: </b>
                        <?php echo " " . $row['osoite']; ?>
                       <br/>
                        <b> Nimi: </b>
                        <?php echo " " . $row['etunimi'] ." ". $row['sukunimi']; ?>
                        <br/>
				<?php }
				     break;
				} ?>
                    <h3> Tarvikkeiden osuus: </h3></br>
						
					
                 <?php 
						
                $tarvikkeidenOsuus = 0;
                $tarvikeq = "SELECT trk.myyntihinta, trk.nimi, trk.tarvike_id, trk.yksikko, tu.maara, trk.kirjallisuutta
				             FROM tiko_ht.tarvike AS trk, tiko_ht.urakkatarjous AS ut, tiko_ht.tarvike_urakkatarjous AS tu
                             WHERE tu.tarvike_id = trk.tarvike_id 
                             AND ut.urakkatarjous_id = tu.urakkatarjous_id AND ut.urakkatarjous_id = $ut_id";  
                $tulos = pg_query($tarvikeq);
				if(empty($tulos)) {
					echo "Ei tarvikkeita";
				}
                /* Tahan tulisi lisata jokin taulu tai ainakin muuttaa tarvikkeiden tulostusta rankasti */
                while($row = pg_fetch_array($tulos)) { ?> 
               <br/> <b> TarvikeId: <b/> <?php echo " " . $row['tarvike_id']; ?> <br/>
                <b> Nimi: </b><?php echo " " . $row['nimi']; ?> <br/>
                <b> Hinta(Yksikko): </b> <?php echo " " . $row['myyntihinta']; ?> <br/>
                <b> Maara: </b><?php echo " " . $row['maara'] . " " . $row['yksikko']; ?> <br/>
                <b> Alv: </b> <?php if($row['kirjallisuutta'] == 'f') {
					echo "24% <br/>";
					$alvitonOsuus = (intval($row['myyntihinta']) / 1.24) * intval($row['maara']);					
					$tarvikkeidenOsuus = $tarvikkeidenOsuus + intval($row['myyntihinta']) * intval($row['maara']);
				    $alvillinen = (intval($row['myyntihinta'])) * intval($row['maara']);
					echo "Alviton osuus kokonaishinta: " . $alvitonOsuus . " Euroa <br/>";
					echo "Alvillinen kokonaishinta: " . $alvillinen . " Euroa <br/>";

                } else {
					echo "10% <br/>";
                    $tarvikkeidenOsuus = $tarvikkeidenOsuus + (intval($row['myyntihinta'])) * intval($row['maara']);
					$alvillinen = (intval($row['myyntihinta'])) * intval($row['maara']);
					$alvitonOsuus = (intval($row['myyntihinta']) / 1.24) * intval($row['maara']);
					echo "Alviton kokonaishinta: " . $alvitonOsuus . " Euroa <br/>";
					echo "Alvillinen kokonaishinta: " . $alvillinen . " Euroa <br/>";
                } 
				} ?>

            <br/>
            <?php 

            $tyonOsuusQ = "SELECT ut.kokonaishinta 
                          FROM tiko_ht.urakkatarjous AS ut
                          WHERE ut.urakkatarjous_id = $ut_id"; /* Palautus yhden rivin mittainen pitaisi lisata viela alvit etc*/ 
            $kokonaisHintaA = pg_query($tyonOsuusQ);
            $tyonOsuus = 0.0;
			$kokonaisHinta = 0.0;
			$alennettuHinta = 0.0;
			if(empty($kokonaisHintaA)) {
				pg_query("ROLLBACK");
				die("Kokonaishintaa ei saatu haettua");
			}
            while($row = pg_fetch_array($kokonaisHintaA)) {
                $tyonOsuus = intval($row['kokonaishinta']) - $tarvikkeidenOsuus;
				$kokonaisHinta = intval($row['kokonaishinta']);
				$alennettuHinta  = $kokonaisHinta - ($kokonaisHinta * ($alennusprosentti / 100));
				break;
            }
			echo "<h3>Hinta </h3><br/>";
			echo "<b> alentamaton kokonaishinta: </b>" . " " . $kokonaisHinta . "<br/>";
			echo "<b> Kotitalousvähennyskelponen: </b>";
            echo " " . $tyonOsuus . "<br/> Tarvikkeiden osuus: " . $tarvikkeidenOsuus . "<br/>"; /* Lopullisen hinnan tilanteessa, Selvitettava miten alvit liittyvat tahan */
			echo "Alennusprosentti: " . $alennusprosentti . "<br/>";
			echo "alennettu hinta: " . $alennettuHinta;
            pg_query("COMMIT;");
			pg_connect($yhteys);
            } ?> 
        </div>
    </body>
</html>


