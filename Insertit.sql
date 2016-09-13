-- Tuntityötyypit tuntityötauluun

INSERT INTO tuntityo (tyyppi, hinta)
VALUES ('tyo', 45.00);

INSERT INTO tuntityo (tyyppi, hinta)
VALUES ('suunnittelu', 55.00);

INSERT INTO tuntityo (tyyppi, hinta)
VALUES ('aputyo', 35.00);

-- Asiakkaita

-- Henkilöasiakkaita

INSERT INTO asiakas (asiakkaan_tyyppi, osoite)
VALUES (true, 'Rantakatu 1');

INSERT INTO henkilo (etunimi, sukunimi)
VALUES ('Tero', 'Taistola');

INSERT INTO asiakas (asiakkaan_tyyppi, osoite)
VALUES (true, 'Lähteentie 2');

INSERT INTO henkilo (etunimi, sukunimi)
VALUES ('Erkki', 'Mäenpää');

INSERT INTO asiakas (asiakkaan_tyyppi, osoite)
VALUES (true, 'Raunionkatu 10');

INSERT INTO henkilo (etunimi, sukunimi)
VALUES ('Hanna', 'Hennala');

-- Yritysasiakkaita

INSERT INTO asiakas (asiakkaan_tyyppi, osoite)
VALUES (false, 'Yrittäjänkatu 1');

INSERT INTO yritys (y_tunnus, nimi)
VALUES (123456, 'Konsultti Oy');

INSERT INTO asiakas (asiakkaan_tyyppi, osoite)
VALUES (false, 'Yritystie 5');

INSERT INTO yritys (y_tunnus, nimi)
VALUES (654321, 'Kauppa Ay');

INSERT INTO asiakas (asiakkaan_tyyppi, osoite)
VALUES (false, 'Keskuskatu 12');

INSERT INTO yritys (y_tunnus, nimi)
VALUES (010101, 'Jokakodin kone');

-- Tuntityösuoritteisia työkohteita ja kullekin työkohteelle suhteet tuntityötyyppeihin

INSERT INTO tyokohde (nimi, osoite, asiakas_id)
VALUES ('Loma-asunto', 'Lomakatu 10', 1);

INSERT INTO tyokohde_tuntityo (tyokohde_id, tyyppi, maara)
VALUES (1, 'tyo', 12);
INSERT INTO tyokohde_tuntityo (tyokohde_id, tyyppi, maara, alennusprosentti)
VALUES (1, 'suunnittelu', 3, 10);
INSERT INTO tyokohde_tuntityo (tyokohde_id, tyyppi)
VALUES (1, 'aputyo');

INSERT INTO tyokohde (nimi, osoite, asiakas_id)
VALUES ('Ranta-asunto', 'Rantatie 5', 1);

INSERT INTO tyokohde_tuntityo (tyokohde_id, tyyppi)
VALUES (2, 'tyo');
INSERT INTO tyokohde_tuntityo (tyokohde_id, tyyppi)
VALUES (2, 'suunnittelu');
INSERT INTO tyokohde_tuntityo (tyokohde_id, tyyppi)
VALUES (2, 'aputyo');

INSERT INTO tyokohde (nimi, osoite, valmis, asiakas_id)
VALUES ('Toimitila', 'Yrittäjänkatu 1', true, 4);

INSERT INTO tyokohde_tuntityo (tyokohde_id, tyyppi, maara)
VALUES (3, 'tyo', 2);
INSERT INTO tyokohde_tuntityo (tyokohde_id, tyyppi, maara)
VALUES (3, 'suunnittelu', 2);
INSERT INTO tyokohde_tuntityo (tyokohde_id, tyyppi, maara)
VALUES (3, 'aputyo', 2);

-- Urakkatyösuoritteisia työkohteita

INSERT INTO tyokohde (nimi, osoite, urakkahinta, asiakas_id)
VALUES ('Oma asunto', 'Lähteentie 2', 500.00, 2);

INSERT INTO tyokohde (nimi, osoite, urakkahinta, asiakas_id)
VALUES ('Isovanhempien asunto', 'Maalaistie 1120', 600.00 , 3);

INSERT INTO tyokohde (nimi, osoite, urakkahinta, valmis, asiakas_id)
VALUES ('Tehdas', 'Tehdastie 20', 400.00, true, 5);

-- Tarvikkeita

INSERT INTO tarvike (nimi, ostohinta, myyntihinta, yksikko, kirjallisuutta, varastotilanne)
VALUES ('Sähköjohto', 2.00, 3.00, 'metri', false, 50);

INSERT INTO tarvike (nimi, ostohinta, myyntihinta, yksikko, kirjallisuutta, varastotilanne)
VALUES ('Jatkojohto', 1.00, 1.99, 'metri', false, 100);

INSERT INTO tarvike (nimi, ostohinta, myyntihinta, yksikko, kirjallisuutta, varastotilanne)
VALUES ('Pistorasia', 15.95, 20.00, 'kpl', false, 25);

INSERT INTO tarvike (nimi, ostohinta, myyntihinta, yksikko, kirjallisuutta, varastotilanne)
VALUES ('Kytkin', 10.00, 15.00, 'kpl', false, 25);

INSERT INTO tarvike (nimi, ostohinta, myyntihinta, yksikko, kirjallisuutta, varastotilanne)
VALUES ('Rasialiitin', 5.00, 10.00, 'kpl', false, 25);

INSERT INTO tarvike (nimi, ostohinta, myyntihinta, yksikko, kirjallisuutta, varastotilanne)
VALUES ('Opaskirja', 8.00, 10.00, 'kpl', true, 20);

-- Työkohteen tarvikkeita työkohteelle 1: Loma-asunto

INSERT INTO tarvike_tyokohde (tarvike_id, tyokohde_id, maara, alennusprosentti)
VALUES (1, 1, 3, 10);

INSERT INTO tarvike_tyokohde (tarvike_id, tyokohde_id, maara, alennusprosentti)
VALUES (3, 1, 1, 20);

INSERT INTO tarvike_tyokohde (tarvike_id, tyokohde_id, maara, alennusprosentti)
VALUES (6, 1, 1, 20);

-- Työkohteen tarvikkeita työkohteelle 3: Toimitila

INSERT INTO tarvike_tyokohde (tarvike_id, tyokohde_id, maara, alennusprosentti)
VALUES (3, 3, 10, 10);

INSERT INTO tarvike_tyokohde (tarvike_id, tyokohde_id, maara, alennusprosentti)
VALUES (4, 3, 10, 50);

-- Työkohteen tarvikkeita työkohteelle 6: Tehdas

INSERT INTO tarvike_tyokohde (tarvike_id, tyokohde_id, maara)
VALUES (1, 6, 10);

INSERT INTO tarvike_tyokohde (tarvike_id, tyokohde_id, maara)
VALUES (2, 6, 10);

-- Urakkatarjouksia

INSERT INTO urakkatarjous (tyotunnit, suunnittelu, aputyo, kokonaishinta, alennusprosentti, tyokohde_id)
VALUES (20, 5, 0, 200.00, 10, 3);

-- Urakkatarjouksen tarvikkeita

INSERT INTO tarvike_urakkatarjous (tarvike_id, urakkatarjous_id, maara, alennusprosentti)
VALUES (1, 1, 20, 10);

INSERT INTO tarvike_urakkatarjous (tarvike_id, urakkatarjous_id, maara, alennusprosentti)
VALUES (3, 1, 3, 20);

INSERT INTO tarvike_urakkatarjous (tarvike_id, urakkatarjous_id, maara, alennusprosentti)
VALUES (4, 1, 1, 20);

-- Laskuja

INSERT INTO lasku (paivamaara, maksettava, kotitalousvahennys, maksuaika, tyokohde_id)
VALUES ('2016-01-01', 601.50, 270.00, 30, 3);

INSERT INTO lasku (paivamaara, maksettava, kotitalousvahennys, maksuaika, tyokohde_id)
VALUES ('2016-02-10', 400.00, 350.10, 20, 6);

-- Muistutuslasku laskusta 1

INSERT INTO lasku (paivamaara, maksettava, kotitalousvahennys, maksuaika,
                   edellinen_id, tyokohde_id)
VALUES ('2016-02-01', 601.55, 270.00, 30, 1, 3);

-- Alv

INSERT INTO alv (ryhma, prosentti)
VALUES ('muu', 24);

INSERT INTO alv (ryhma, prosentti)
VALUES ('kirjallisuus', 10);
