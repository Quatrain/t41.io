<?php

require 'config.inc.php';

$countries = json_decode(file_get_contents('countries.json'));

$fh = fopen('boundingboxes.txt','r');
$geo = [];
while (($data = fgetcsv($fh, 1000, "\t")) !== FALSE) {
	/**
		[0] => country_name
		[1] => ISO_3166-1_Alpha-2_country_code
		[2] => FIPS_country_code
		[3] => min_lon
		[4] => min_lat
		[5] => max_lon
		[6] => max_lat
		[7] => centre_lon
		[8] => centre_lat
		[9] => Continent

	 */
	$geo[$data[1]] = $data;
}

$m = new \MongoClient(MONGOHOST);
$db = $m->{MONGODB};
$collection = $db->country;

foreach ($countries as $country) {
	$data = [
				'_id' => mkMongoId($country->code), 
				'code' => ['@' . strtolower($country->code), $country->code, $country->isoNumeric, $country->geonameId],
				'label' => [['k' => '__', 'v' => $country->label]],
				'continent' => ['name' => $country->continentName, 'code' => $country->continent],
				'languages' => explode(',', $country->languages),
				'currency' => $country->currency,
				'capital' => [['k' => '__', 'v'  => $country->capital]],
				'population' => (int) $country->population,
				'area' => (int) $country->areaInSqKm,
				'source' => ['un','gns']
			];
	
	if (isset($geo[$country->code])) {
		$geodata = $geo[$country->code];
		$data['location'] = ['type' => 'Point', 'coordinates' => [(float) $geodata[7], (float) $geodata[8]]];
		$data['bbox'] = [(float) $geodata[3], (float) $geodata[4], (float) $geodata[5], (float) $geodata[6]];
	}
	
	$collection->insert($data);
}
