<?php

require 'config.inc.php';

$skipped = $ok = $nok = 0;

$m = new \MongoClient(MONGOHOST);
$db = $m->{MONGODB};
$collection = $db->road;
$collplot = $db->plot;

$res = $collection->find(['bbox' => null, 'city.code' => '/^FR13/i']);
foreach (iterator_to_array($res) as $road) {
	$long = $lat = [];
	
	echo $road['code'][0] . ' - ' . $road['type'] . ' ' . $road['label'][0]['v'] . " : ";
	
	$plots = $collplot->find([ 'road.$id' => $road['_id']->__toString() ], ['location']);
	
	foreach (iterator_to_array($plots) as $plot) {
		$long[] = $plot['location']['coordinates'][0];
		$lat[]  = $plot['location']['coordinates'][1];
	}
	
	if (count($long) == 0 || count($lat) == 0) {
		echo "SKIPPED\n";
		$skipped++;
		continue;
	}
	
	$bbox = [(float) max($long), (float) min($lat), (float) min($long), (float) max($lat)];
	if ($collection->update(['_id' => $road['_id']], ['$set' => ['bbox' => $bbox]])) {
		echo "OK\n";
		$ok++;
	} else {
		echo "NOK\n";
		$nok++;
	}
}
echo "\n---\n";
printf("TOTAL OK : %d\n", $ok);
printf("TOTAL NOK : %d\n", $nok);
printf("TOTAL SKIPPED : %d\n", $skipped);
