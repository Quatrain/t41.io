<?php
define('RN', "\r\n");
define('MONGOHOST', 'mongodb://127.0.0.1');
define('MONGODB', 't41');

define('DATADIR', substr(getcwd(),0, strrpos(getcwd(),'/')) . '/data/');

define('MYSQLHOST', '127.0.0.1');
define('MYSQLLOGIN', 'saiku');
define('MYSQLPASSWD', 'saiku');
define('MYSQLDATABASE', 'cime_italic2');

setlocale(LC_ALL, 'FR_fr');
ini_set('memory_limit', '-1');


/**
 * Crée un MongoId invariant à partir de la chaine
 * passée en paramètre
 * @param string $string
 * @return string
 */
function mkMongoId($string, $asString = false) {

    $string = str_replace('-','',$string);
    
	if (substr($string, 0, 1) != '@' && $asString) {
		$string = '@' . $string;
	}
	$string = str_pad(strtolower($string), 12, chr(255));

	$hex = null;
	for ($i = 0 ; $i < strlen($string) ; $i++) {
		$hex .= dechex(ord($string[$i]));
	}
	$length = strlen($hex);
	//var_Dump($string, $hex);
	$pos = $length > 24 ? $length - 24 : 0;
	return $asString ? substr($hex, $pos, 24) : new \MongoId(substr($hex, $pos, 24));
}


class MongoConnection {

    protected $_rsc;

    protected $_db;

    protected $_collections = [];

    public function __construct() {
        $this->_rsc = new \MongoClient(MONGOHOST, ['connectTimeoutMS' => 86400, 'connect' => true]);
        $this->_db = $this->_rsc->{MONGODB};
    }

    public function getCollection($collection) {
        if (! isset($this->_collections[$collection])) {
            $this->_collections[$collection] = $this->_db->{$collection};
        }
        return $this->_collections[$collection];
    }
}