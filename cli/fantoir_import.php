#!/usr/bin/php
<?php
$start = microtime(true);
define('RN', "\r\n");
define('MONGO', 'mongodb://127.0.0.1:27017');
define('FANTOIR', __DIR__ . '/840.txt');


$zones = array(
	'cdep' => array("Code Département", 0, 2, ''), //
	'cdir' => array("Code Direction",2 , 1, 'int'), // INT
	'ccom' => array("Code Commune", 3, 3, ''), //
	'idvoie' => array("Identifiant Voie", 6, 4, ''), // CHAR 4
	'cleriv' => array("Clé RIVOLI", 10, 1, ''), // CHAR 1
	'cnatvoie' => array("Code Nature Voie", 11, 4, ''),
	'libvoie' => array("Libellé Voie", 15, 26, ''), // VARCHAR 26
	'typcom' => array("Type Commune", 42, 1, ''), // CHAR 1
	'caracrur' => array("Caractère RUR", 45, 1, 'int'), // INT
	'caracvoie' => array("Caractère Voie", 48, 1, 'bool'), // BOOL
	'caracpop' => array("Caractère Population", 49, 1, 'bool'), // BOOL
	'poppart' => array("Population à part", 59, 7, ''), // ??? 0000000
	'popfic' => array("Population Fictive", 66, 7, ''), // ??? 0000000
	'caracann' => array("Caractère d'Annulation", 73, 1, ''), // CHAR 1
	'dateann' => array("Date d'Annulation", 74, 7, ''), // ??? 0000000
	'datecrea' => array("Date Création", 81, 7, 'int'), // date sur 7 caractères YYYYzzz
	'cidmajic' => array("Code Identifiant MAJIC", 103, 5, ''), // CHAR
	'typvoie' => array("Type Voie", 108, 1, 'int'), // INT
	'caracld' => array("Caractère Lieu-dit", 109, 1, 'bool'), // BOOL ?
	'dermalph' => array("Dernier Mot du Libellé de la Voie", 112, 8, '') // CHAR 8
);

// connexion à MongoDB et sélection de la base/collection
$m = new \MongoClient(MONGO);
$db = $m->fantoir;
$collection = $db->fantoir;


// ouverture du fichier fantoir en lecture seule
$handle = fopen(FANTOIR,'r');
if ($handle===FALSE) {
	echo 'erreur de lecture du fichier';
	exit();
}

$i=0;
$t=0;

while (($line = fgets($handle)) != FALSE) {

	// on ignore les lignes vides
	if (strlen(trim($line)) == 0) {
		continue;
	}

	// on ignore les lignes qui ne représentent pas une voie
	if ((strlen(trim(substr($line, $zones['dermalph'][1], $zones['dermalph'][2])))) == 0) {
		//echo 'X';
		continue;
	}


	$array = array();
	foreach ($zones as $key => $val) {

		$data = trim(substr($line, $val[1], $val[2]));

		// récupération des valeurs
		switch ($val[3]) {
			case 'int':
				$data = intval($data);
				break;
			case 'bool':
				$data = (bool) $data;
				break;
			case 'cp':
				switch($data) {
					case '2A':
						$data = 98;
						break;
					case '2B':
						$data = 99;
						break;
					default:
						$data = intval($data);
						break;
				}
				break;
			default:
				break;
		}
		$array[$key] = $data;
	}

	$collection->insert($array);

	if ($i>=1000) {
		echo '.';
		$i=0;
	}

	$i++;
	$t++;
}

fclose($handle);


$stop = microtime(true);
$total = $stop-$start;
echo RN.$t . ' lines in '. $total. 's total'.RN;
