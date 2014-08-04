#!/usr/bin/php
<?php
$start = microtime(true);
define('RN', "\r\n");
define('MONGO', 'mongodb://127.0.0.1');
//$MONGOPT = array('replicaSet'=>'qt0');
define('FANTOIR', '/mnt/hgfs/Téléchargements/fantoir/');
define('EXT', '*.txt');

define('MYSQLHOST', '127.0.0.1');
define('MYSQLLOGIN', 'root');
define('MYSQLPASSWD', 'aek188bm');
define('MYSQLDATABASE', 'cime_italic2');

//MongoLog::setModule( MongoLog::ALL );
//MongoLog::setLevel( MongoLog::ALL );
MongoCursor::$timeout = -1;

$zones = array(
        'cdep' => array("Code Département", 0, 2, '', FALSE), //
        'cdir' => array("Code Direction", 2 , 1, 'int', FALSE), // INT
        'ccom' => array("Code Commune", 3, 3, '', FALSE), //
        'idvoie' => array("Identifiant Voie", 6, 4, '', TRUE), // CHAR 4
        'cleriv' => array("Clé RIVOLI", 10, 1, '', TRUE), // CHAR 1
        'cnatvoie' => array("Code Nature Voie", 11, 4, '', TRUE),
        'libvoie' => array("Libellé Voie", 15, 26, '', TRUE), // VARCHAR 26
        'typcom' => array("Type Commune", 42, 1, '', FALSE), // CHAR 1
//        'caracrur' => array("Caractère RUR", 45, 1, 'int', FALSE), // INT
//        'caracvoie' => array("Caractère Voie", 48, 1, 'bool', FALSE), // BOOL
//        'caracpop' => array("Caractère Population", 49, 1, 'bool', FALSE), // BOOL
//        'poppart' => array("Population à part", 59, 7, '', FALSE), // ??? 0000000
//        'popfic' => array("Population Fictive", 66, 7, '', FALSE), // ??? 0000000
//        'caracann' => array("Caractère d'Annulation", 73, 1, '', FALSE), // CHAR 1
//        'dateann' => array("Date d'Annulation", 74, 7, '', FALSE), // ??? 0000000
//        'datecrea' => array("Date Création", 81, 7, 'int', FALSE), // date sur 7 caractères YYYYzzz
//        'cidmajic' => array("Code Identifiant MAJIC", 103, 5, '', FALSE), // CHAR
        'typvoie' => array("Type Voie", 108, 1, 'int', TRUE), // INT
//        'caracld' => array("Caractère Lieu-dit", 109, 1, 'bool', FALSE), // BOOL ?
        'dermalph' => array("Dernier Mot du Libellé de la Voie", 112, 8, '', FALSE) // CHAR 8
);


$filtre = array();

foreach ($zones as $k => $v) {
    if ($v[4]===FALSE) $filtre[$k] = 0;
}

$communes = getCities();

$y=0;
foreach (glob(FANTOIR.EXT) as $filename) {
    $villes = $villes2 = array();
    $substart = microtime(true);

    // ouverture du fichier fantoir en lecture seule
    $handle = fopen($filename,'r');
    if ($handle===FALSE) {
            echo RN.'erreur de lecture du fichier'.RN;
            exit();
    }

    echo RN.'Reading file '.basename($filename);

    $i=0;
    $t=0;

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
     //   $voie['_id'] = new MongoId();
        $voie['id'] = $voie['cdep'] . $voie['ccom'] . $voie['idvoie'] . $voie['cleriv'];
        $ville_id = checkVille($voie);
        $voie['commune'] = $ville_id;

        // suppression des colonnes qui se rapportent à la ville (inutiles puisqu'on s'insère dans un objet ville qui les porte déjà)
        $voie = array_diff_key($voie, $filtre);

        
        $villes2[$ville_id][] = $voie;

        // donner une idée de l'avancement pendant la lecture
        if ($i>=1000) {
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
            'departement' => $voie['cdep'],
            'direction' => $voie['cdir'],
            'code' => $voie['ccom'],
            'insee' => $insee,
           // 'voies' => array()
        );

        if (isset($communes[$insee])) {
            $villes[$insee]['label'] = utf8_encode($communes[$insee]['label']);
            $villes[$insee]['creation'] = new MongoDate(strtotime($communes[$insee]['creation']));
            $villes[$insee]['suppression'] = new MongoDate(strtotime(str_replace('9999','2199', $communes[$insee]['suppression'])));
            $villes[$insee]['codepostal'] = $communes[$insee]['codepostal'];
            $villes[$insee]['departement'] = $communes[$insee]['departement'];
        }
    }
    return $insee;
}

// insérer un document ville contenant un tableau voies
function insertion() {
    global $villes;
    global $ville_id;
    global $villes2;
    global $MONGOPT;

    $start = microtime(true);
    echo RN.'Pushing data to Mongo... ';

    // connexion à MongoDB et sélection de la base/collection
    // connexion persistante par défaut, pas besoin de vérifier si elle existe déjà
    $m = new MongoClient(MONGO/*, $MONGOPT*/);
    $db = $m->fantoir;
    $collection = $db->communes;
    $collection2 = $db->voies;

    foreach ($villes as $ville) {
        // on génère un MongoId manuellement pour éviter tout risque de doublon
        $ville['_id'] = new MongoId();
        printf("Insertion de la ville %s\n", $ville['label']);
        $collection->insert($ville);
        
        $voies = $villes2[$ville['insee']];

        printf("Insertion de %s voies de la ville %s\n", count($voies), $ville['label']);
        foreach ($voies as $voie) {
        	$voie['_commune'] = $ville['_id'];
        	$collection2->insert($voie);
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