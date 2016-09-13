-- Dropataan, luodaan ja asetetaan search_path tiko_ht

DROP SCHEMA IF EXISTS tiko_ht CASCADE;
CREATE SCHEMA tiko_ht;
SET SEARCH_PATH TO tiko_ht;

-- TIETOKANNAN LUONTILAUSEET

CREATE SEQUENCE asiakas_id_sekvenssi;

CREATE TABLE asiakas (
asiakas_id INT DEFAULT NEXTVAL('asiakas_id_sekvenssi'),
asiakkaan_tyyppi BOOLEAN NOT NULL, -- TRUE = henkilo, FALSE = yritys
osoite VARCHAR(99) NOT NULL,
PRIMARY KEY (asiakas_id) );

ALTER SEQUENCE asiakas_id_sekvenssi OWNED BY asiakas.asiakas_id;

CREATE TABLE henkilo (
henkilo_id INT DEFAULT CURRVAL('asiakas_id_sekvenssi'),
etunimi VARCHAR(99) NOT NULL,
sukunimi VARCHAR(99) NOT NULL,
FOREIGN KEY(henkilo_id) REFERENCES asiakas(asiakas_id),
PRIMARY KEY (henkilo_id) );

CREATE TABLE yritys (
yritys_id INT DEFAULT CURRVAL('asiakas_id_sekvenssi'),
y_tunnus INT NOT NULL,
nimi VARCHAR(99) NOT NULL,
FOREIGN KEY(yritys_id) REFERENCES asiakas(asiakas_id),
PRIMARY KEY (yritys_id) );

CREATE SEQUENCE tyokohde_id_sekvenssi;

CREATE TABLE tyokohde (
tyokohde_id INT DEFAULT NEXTVAL('tyokohde_id_sekvenssi'),
nimi VARCHAR(99) NOT NULL,
osoite VARCHAR(99) NOT NULL,
urakkahinta NUMERIC(9, 2), -- Jos NULL niin tuntityö, jos ei niin urakkatyö
valmis BOOLEAN NOT NULL DEFAULT false,
asiakas_id INT NOT NULL,
FOREIGN KEY (asiakas_id) REFERENCES asiakas(asiakas_id),
PRIMARY KEY (tyokohde_id) );

ALTER SEQUENCE tyokohde_id_sekvenssi OWNED BY tyokohde.tyokohde_id;

CREATE TABLE tuntityo (
tyyppi VARCHAR(99) NOT NULL, -- urakkatarjous_tuntityo-taulu?
hinta NUMERIC(9, 2) NOT NULL,
PRIMARY KEY(tyyppi) );

CREATE TABLE tyokohde_tuntityo (
tyokohde_id INT, -- Tähän voi laittaa CURRVAL('tyokohde_id_sekvenssi')?
tyyppi VARCHAR(99),
maara INT NOT NULL DEFAULT 0,
alennusprosentti INT DEFAULT 0 CHECK (alennusprosentti >= 0 AND alennusprosentti <= 100),
FOREIGN KEY (tyokohde_id) REFERENCES tyokohde(tyokohde_id),
FOREIGN KEY (tyyppi) REFERENCES tuntityo(tyyppi),
PRIMARY KEY(tyokohde_id, tyyppi) );

CREATE SEQUENCE urakkatarjous_id_sekvenssi;

CREATE TABLE urakkatarjous (
urakkatarjous_id INT DEFAULT NEXTVAL('urakkatarjous_id_sekvenssi'),
tyotunnit INT NOT NULL,
suunnittelu INT NOT NULL,
aputyo INT NOT NULL,
kokonaishinta NUMERIC(9, 2),
alennusprosentti INT CHECK (alennusprosentti >= 0 AND alennusprosentti <= 100),
tyokohde_id INT NOT NULL,
FOREIGN KEY (tyokohde_id) REFERENCES tyokohde(tyokohde_id),
PRIMARY KEY (urakkatarjous_id),
UNIQUE(tyokohde_id) );

ALTER SEQUENCE urakkatarjous_id_sekvenssi OWNED BY urakkatarjous.urakkatarjous_id;

CREATE SEQUENCE tarvike_id_sekvenssi;

CREATE TABLE tarvike (
tarvike_id INT DEFAULT NEXTVAL('tarvike_id_sekvenssi'),
nimi VARCHAR(99) NOT NULL,
ostohinta NUMERIC(9, 2) NOT NULL,
myyntihinta NUMERIC(9, 2) NOT NULL,
yksikko VARCHAR(99) NOT NULL, -- CHECK ('metri' || 'kpl')
kirjallisuutta BOOLEAN NOT NULL,
varastotilanne INT NOT NULL DEFAULT 0,
poistettu BOOLEAN NOT NULL DEFAULT false,
PRIMARY KEY (tarvike_id) );

ALTER SEQUENCE tarvike_id_sekvenssi OWNED BY tarvike.tarvike_id;

CREATE TABLE tarvike_tyokohde (
tarvike_id INT,
tyokohde_id INT,
maara INT NOT NULL DEFAULT 0,
alennusprosentti INT DEFAULT 0 CHECK (alennusprosentti >= 0 AND alennusprosentti <= 100),
FOREIGN KEY (tarvike_id) REFERENCES tarvike(tarvike_id),
FOREIGN KEY (tyokohde_id) REFERENCES tyokohde(tyokohde_id),
PRIMARY KEY (tarvike_id, tyokohde_id) );

CREATE TABLE tarvike_urakkatarjous (
tarvike_id INT,
urakkatarjous_id INT,
maara INT NOT NULL DEFAULT 0,
alennusprosentti INT DEFAULT 0 CHECK (alennusprosentti >= 0 AND alennusprosentti <= 100),
FOREIGN KEY (tarvike_id) REFERENCES tarvike(tarvike_id),
FOREIGN KEY (urakkatarjous_id) REFERENCES urakkatarjous(urakkatarjous_id),
PRIMARY KEY (tarvike_id, urakkatarjous_id) );

CREATE SEQUENCE lasku_id_sekvenssi;

CREATE TABLE lasku ( -- Onko laskulla oltava tyyppi (normaali, muistutus, karhu)?
lasku_id INT DEFAULT NEXTVAL('lasku_id_sekvenssi'),
paivamaara DATE NOT NULL,
maksettava NUMERIC(9, 2) NOT NULL,
kotitalousvahennys NUMERIC(9, 2) NOT NULL,
maksuaika INT NOT NULL,
maksupvm DATE,
edellinen_id INT,
tyokohde_id INT NOT NULL,
FOREIGN KEY (edellinen_id) REFERENCES lasku(lasku_id),
FOREIGN KEY (tyokohde_id) REFERENCES tyokohde(tyokohde_id),
PRIMARY KEY (lasku_id) );

ALTER SEQUENCE lasku_id_sekvenssi OWNED BY lasku.lasku_id;

CREATE TABLE alv (
ryhma VARCHAR(99),
prosentti INT NOT NULL CHECK (prosentti >= 0 AND prosentti <= 100) );


-- ESIMERKKISISÄLTÖ TIETOKANNALLE

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
