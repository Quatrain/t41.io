#!/usr/bin/php
<?php
$start = microtime(true);

require 'config.inc.php';

define('FOLDER', '20140721');
define('FANTOIR', DATADIR . 'fantoir/' . FOLDER . '/');
define('EXT', '*.txt');

$fh = fopen(DATADIR  . 'communes-plus-20140630.csv','r');
$geo = [];
while (($data = fgetcsv($fh, 1000, ";")) !== FALSE) {
	/**
		[0] => insee
		[1] => nom
		[2] => wikipedia
		[3] => surf_m2
		[4] => lat_centro
		[5] => lon_centro
		[6] => statut
		[7] => x_chf_lieu
		[8] => y_chf_lieu
		[9] => z_moyen
		[10] => population
		+ code_cant;code_arr;code_dept;nom_dept;code_reg;nom_region
	 */
	$geo[(string) str_pad($data[0], 5, "0")] = $data;
}

$zones = array(
        'cdep' => array("Code Département", 0, 2, '', FALSE), //
        'cdir' => array("Code Direction", 2 , 1, 'int', FALSE), // INT
        'ccom' => array("Code Commune", 3, 3, '', FALSE), //
        'idvoie' => array("Identifiant Voie", 6, 4, '', TRUE), // CHAR 4
        'cleriv' => array("Clé RIVOLI", 10, 1, '', TRUE), // CHAR 1
        'type' => array("Code Nature Voie", 11, 4, '', TRUE),
        'label' => array("Libellé Voie", 15, 26, '', TRUE), // VARCHAR 26
//        'typcom' => array("Type Commune", 42, 1, '', FALSE), // CHAR 1
//        'caracrur' => array("Caractère RUR", 45, 1, 'int', FALSE), // INT
//        'caracvoie' => array("Caractère Voie", 48, 1, 'bool', FALSE), // BOOL
//        'caracpop' => array("Caractère Population", 49, 1, 'bool', FALSE), // BOOL
//        'poppart' => array("Population à part", 59, 7, '', FALSE), // ??? 0000000
//        'popfic' => array("Population Fictive", 66, 7, '', FALSE), // ??? 0000000
//        'caracann' => array("Caractère d'Annulation", 73, 1, '', FALSE), // CHAR 1
//        'dateann' => array("Date d'Annulation", 74, 7, '', FALSE), // ??? 0000000
//        'datecrea' => array("Date Création", 81, 7, 'int', FALSE), // date sur 7 caractères YYYYzzz
//        'cidmajic' => array("Code Identifiant MAJIC", 103, 5, '', FALSE), // CHAR
//        'type' => array("Type Voie", 108, 1, 'int', TRUE), // INT
//        'caracld' => array("Caractère Lieu-dit", 109, 1, 'bool', FALSE), // BOOL ?
       'dermalph' => array("Dernier Mot du Libellé de la Voie", 112, 8, '', FALSE) // CHAR 8
);


$filtre = array();

foreach ($zones as $k => $v) {
    if ($v[4] === FALSE) $filtre[$k] = 0;
}

$communes = getCities();

$y = 0;

// Lecture de tous les fichiers respectant le pattern
foreach (glob(FANTOIR . EXT) as $filename) {
    $villes = $villes2 = array();
    $substart = microtime(true);

    // ouverture du fichier fantoir en lecture seule
    $handle = fopen($filename,'r');
    if ($handle === FALSE) {
		exit(RN . 'erreur de lecture du fichier' . RN);
    }

    echo RN.'Reading file '.basename($filename);

    $i = $t = 0;

    while (($line = fgets($handle)) != FALSE) {

        // on ignore les lignes vides
        if (strlen(trim($line)) == 0) continue;

        // on ignore les lignes qui ne représentent pas une voie
        if ((strlen(trim(substr($line, $zones['dermalph'][1], $zones['dermalph'][2])))) == 0) continue;

        $voie = array();
        
        // lecture des colonnes
        foreach ($zones as $key => $val) {

            // sélection et nettoyage de la colonne
            $data = trim(substr($line, $val[1], $val[2]));

            // transformation de la valeur si nécessaire
            switch ($val[3]) {
                case 'int':
                    $data = intval($data);
                    break;
                case 'bool':
                    $data = (!! $data);
                    break;
            }

            $voie[$key] = $data;
        }

        // on prépare une clé composite pour la voie
        $partialcode = $voie['cdep'] . $voie['ccom'] . $voie['idvoie'] . $voie['cleriv'];
        $voie['code'] = ['FR' . $partialcode];
        $ville_id = checkVille($voie);

        // suppression des colonnes qui se rapportent à la ville (inutiles puisqu'on s'insère dans un objet ville qui les porte déjà)
        $voie = array_diff_key($voie, $filtre);

        
        $villes2[$ville_id][] = $voie;

        // donner une idée de l'avancement pendant la lecture
        if ($i >= 1000) {
            echo '.';
            $i=0;
        }

        $i++; $t++; $y++;
    }

    fclose($handle);
    
    // insertion dans la BDD seulement après la lecture complète du fichier
    insertion($villes);

    echo RN.$t . ' lines in '. timing($substart). 's for file '.basename($filename). ' with '.count($villes). ' cities'.RN;
   
    // on tente de libérer la mémoire pour le fichier suivant
    unset($villes);
}

// dernière ligne du script
echo RN.'TOTAL: '.$y . ' lines in '.count($communes).' cities, took '. timing($start). 's'.RN;


///////////////////////////////////////////////////////////////////////////////

function timing($start) {
    return round(microtime(true) - $start, 2);
}

// prépare le tableau pour la ville et retourne son id
function checkVille($voie) {
    global $villes;
    global $collection;
    global $communes;

    $insee = $voie['cdep'] . $voie['ccom'];

    if (!array_key_exists($insee, $villes)) {
        $villes[$insee] = array(
            'code' => ['FR' . $insee],
        );

        if (isset($communes[$insee])) {
            $villes[$insee]['label'] = [['k' => '__', 'v' => utf8_encode($communes[$insee]['label'])]];
            $villes[$insee]['fromdate'] = new \MongoDate(strtotime($communes[$insee]['creation']));
            $villes[$insee]['todate'] = new \MongoDate(strtotime(str_replace('9999','2199', $communes[$insee]['suppression'])));
            $villes[$insee]['postcode'] = [$communes[$insee]['codepostal']];
            $villes[$insee]['county'] = $communes[$insee]['departement'];
            $villes[$insee]['country'] = ['code' => ['@fr','FR'], '$ref' => 'country', '$id' => mkMongoId('FR',true)];
        }
    }
    return $insee;
}

// insérer un document ville contenant un tableau voies
function insertion() {
    global $villes, $ville_id, $villes2, $geo;
    

    $start = microtime(true);
    echo RN.'Pushing data to Mongo... ';

    // connexion à MongoDB et sélection de la base/collection
    // connexion persistante par défaut, pas besoin de vérifier si elle existe déjà
    $m = new \MongoClient(MONGOHOST);
    $db = $m->{MONGODB};
    $collection = $db->city;
    $collection2 = $db->road;

    foreach ($villes as $ville) {
    	$insee = substr($ville['code'][0],2);
        // on génère un MongoId manuellement pour éviter tout risque de doublon
        $ville['_id'] = mkMongoId($ville['code'][0]);
        $ville['source'] = ['quatrain','fantoir','osm'];
        $ville['creation'] = new \MongoDate();
        
        if (isset($geo[$insee])) {
        	$geodata = $geo[$insee];
        	$ville['location'] = ['type' => 'Point', 'coordinates' => [(float) $geodata[5], (float) $geodata[4]]];
        	$ville['area'] = (int) ($geodata[3] / 1000000);
        	$ville['population'] = (int) ($geodata[10] * 1000);
        	//$data['bbox'] = [(float) $geodata[3], (float) $geodata[4], (float) $geodata[5], (float) $geodata[6]];
        }
        
        printf("Insertion de la ville %s\n", $ville['label'][0]['v']);
        $collection->update(['_id' => $ville['_id']], $ville, ['upsert' => true]);
        
        $voies = $villes2[$insee];

        printf("Insertion de %s voies de la ville %s\n", count($voies), $ville['label'][0]['v']);
        foreach ($voies as $voie) {
        	unset($voie['idvoie']);
        	unset($voie['cleriv']);
        	$voie['label'] = [['k' => '__', 'v' => $voie['label']]];
        	$voie['source'] = ['fantoir'];
        	$voie['creation'] = new \MongoDate();
        	$voie['city'] = array('code' => $ville['code'], '$ref' => 'city', '$id' => mkMongoId($ville['code'][0],true));
        	$voie['country'] = ['code' => ['@fr','FR'], '$ref' => 'country', '$id' => mkMongoId('FR',true)];
        	$voie['_id'] = mkMongoId($voie['code'][0]);
        	 
        	$collection2->update(['_id' => $voie['_id']], $voie, ['upsert' => true]);
        }
    }
    echo 'took '.timing($start).'s';
}


function getCities() {
    $start = microtime(true);
    $villes = array();
    $mysqli = new mysqli(MYSQLHOST, MYSQLLOGIN, MYSQLPASSWD, MYSQLDATABASE);

    if (mysqli_connect_errno()) {
        printf("Échec de la connexion : %s\n", $mysqli->connect_error);
        exit();
    }

    if ($result = $mysqli->query("SELECT * FROM `italic_core_commune`;")) {
        while ($row = $result->fetch_assoc()) {
            $villes[$row['id']] = $row;
        }
        $result->free();
    }
    $mysqli->close();

    echo 'Fetching the cities database took '.timing($start).'s'.RN;
    return $villes;
}
