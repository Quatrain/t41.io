#!/usr/bin/php
<?php
require 'config.inc.php';

$start = microtime(true);
define('MONGO', 'mongodb://localhost');
define('MONGO2', 'mongodb://beta.t41.io');
//$MONGOPT = array('replicaSet'=>'qt0');
define('BANO', DATADIR . 'bano/');
define('EXT', 'bano-*.csv');


$banoH = array(
    array('code', "ID", "string"),
    array('number', "Numero de l'adresse", "string"),
    array('label', "Nom de la voie", "string"),
    array('codepostal',"Code Postal", "string"),
    array('cityName',"Commune", "string"),
    array('source',"Source", "string"),
    array('lat',"Latitude", "loclat"),
    array('lng',"Longitude", "loclng")
);


$cnx = new MongoConnection();
$roads = [];
$y = 0;

foreach (glob(BANO . EXT) as $filename) {
    $batiments = $roads = [];
    $substart = microtime(true);

    // ouverture du fichier fantoir en lecture seule
    $handle = fopen($filename,'r');
    if ($handle === FALSE) {
        echo RN.'erreur de lecture du fichier'.RN;
        exit();
    }
    
    $dept = substr($filename, strrpos($filename, '-')+1, 2);
    $communes = getCities($dept);
    

    echo RN.'Reading file ' . basename($filename);

    $collection = $cnx->getCollection('plot');
    
    $i = $t = 0;

    while (($line = fgetcsv($handle)) !== FALSE) {

        // on ignore les lignes vides
        if (count($line) == 1 || $line[0] == NULL) continue;

        $point = array('location' => array('type' => 'Point', 'coordinates' => array())); // WGS84
        
        // lecture des colonnes
        foreach ($line as $key => $value) {

            // sélection et nettoyage de la colonne
            $data = trim($value);

            // transformation de la valeur si nécessaire
            switch ($banoH[$key][2]) {
                case 'string':
                    $point[$banoH[$key][0]] = $data;
                break;
                case 'int':
                    $point[$banoH[$key][0]] = intval($data);
                break;
                case 'bool':
                    $point[$banoH[$key][0]] = (!! $data);
                break;

                case 'loclng':
                    $point['location']['coordinates'][0] = floatval($data);
                	break;
                
                case 'loclat':
                	$point['location']['coordinates'][1] = floatval($data);
                	break;
            }

        }

        $point['source'] = ['bano', $point['source']];        
        //$point['label'] = sprintf('%s %s', $point['number'], $point['label']);
        $point['insee'] = substr($point['code'], 0, 5);
        $point['code'] = ['FR' . $point['code']];
        $point['road'] = checkRoad($point['code']);
        $point['city'] = checkVille($point);
        $point['country'] = ['code' => ['@fr','FR'], '$ref' => 'country', '$id' => "406672ffffffffffffffffff"];

        // Séparation du numéro
        preg_match("/([0-9]+)([A-Z]*)/", $point['number'], $parts);
        $point['numberNumPart'] = (int) $parts[1];
        if (strlen($parts[2]) > 0) {
        	$point['numberAlphaPart'] = $parts[2];
        }
        
        // @todo enrichir codes postaux à partir des données
        
        unset($point['codepostal']);
        unset($point['insee']);
        unset($point['label']);
        unset($point['cityName']);
        
        $point['_id'] = mkMongoId($point['code'][0]);
        
        // sort location data
        ksort($point['location']['coordinates']);
        $batiments[] = $point;
        
        // donner une idée de l'avancement pendant la lecture
        if ($i >= 1000) {
            //$collection->batchInsert($batiments, ['continueOnError' => true]);
            
             foreach ($batiments as $batiment) {
                $collection->update(['_id' => $batiment['_id']], $batiment, ['upsert' => true]);
            }
            echo '.';
            $batiments = [];
            $i = 0;
            continue;
        }

        $i++; $t++; $y++;
    }

    fclose($handle);


    echo RN . $t . ' lines in ' . timing($substart) . 's for file '.basename($filename) . RN;
   
    // on tente de libérer la mémoire pour le fichier suivant
    unset($batiments);
}

// dernière ligne du script
echo RN.'TOTAL: '.$y . ' lines in '.count($communes).' cities, took '. timing($start). 's'.RN;


///////////////////////////////////////////////////////////////////////////////

function timing($start) {
    return round(microtime(true) - $start, 2);
}

// prépare le tableau pour la ville et retourne son id
function checkVille($p) {
    global $communes;
	$code = 'FR' . $p['insee'];
    if (isset($communes[$code]) && is_array($communes[$code])) {
        return ['code' => [$code], '$ref' => 'city', '$id' => mkMongoId($code,true)];
    }

    echo RN.'No match for '.$p['insee']. ' ('.$p['cityName'].')'.RN;
    return false;
}


// prépare le tableau pour la voie et retourne son id
function checkRoad($p) {
	global $roads, $cnx;
	
	$parts = explode('-', $p[0]);
	$code = $parts[0];

	if (isset($roads[$code])) {
		return $roads[$code];
	}
	
	$c = $cnx->getCollection('road');
	$cursor = $c->find(array('code' => $code))->limit(1);
	$cursor->timeout(-1);

	$p = $cursor->getNext();
	if ($p) {
		$roads[$code] = array('code' => [$code], '$ref' => 'road', '$id' => $p['_id']->__toString());
		return $roads[$code];
	} else {
		echo RN.'No match for ' . $code . RN;
		return "MISSING";
	}
}


// insérer un document ville contenant un tableau voies
function insertion() { 
    global $batiments, $cnx;

    $start = microtime(true);
    echo RN.'Pushing data to Mongo... ';

    $collection = $cnx->getCollection('plot');

    foreach ($batiments as $batiment) {
        // on génère un MongoId manuellement pour éviter tout risque de doublon
        $batiment['_id'] = new MongoId();
        
        // sort location data
        ksort($batiment['location']['coordinates']);
        $collection->insert($batiment);
    }
    echo 'took '.timing($start).'s';
}

function getCities($dept = null) {
    global $cnx;
    
    $cond = $dept ? ['code' => [ '$regex' => '^FR' . $dept]] : [];
    
    $start = microtime(true);
    $output = Array();

    $c = $cnx->getCollection('city');
    $cursor = $c->find($cond);

    while($cursor->hasNext()) {
        $p = $cursor->getNext();
        $output[ $p['code'][0] ] = [ 'code' => [$p['code']], '$ref' => 'city', '$id' => $p['_id'] ];
    }

    printf('Fetching the %s cities database took %d sec.' . RN, $dept, timing($start));
    return $output;
}

