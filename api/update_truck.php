<?php
include '../lib/Database.php';
include '../lib/Secrets.php';

header("Access-Control-Allow-Origin: *");

$json = file_get_contents("php://input");

$truckData = json_decode($json, true);

$particleId = $truckData['coreid'];
$data = $truckData['data'];
$latlon = explode(",", $data);

file_put_contents('/tmp/test', print_r($truckData, true));

$lat = $latlon[0];
$lon = $latlon[1];

$databaseCredentials = (new Secrets())->readSecrets();
$database = new Database(
    $databaseCredentials['db_user'],
    $databaseCredentials['db_pass'],
    $databaseCredentials['db_host'],
    $databaseCredentials['db_database']
);

$database->connect();

$results = $database->query('SELECT * FROM `particle_to_truck` WHERE `particle_id` = :particle',
    [
        'particle' => $particleId
    ]
);

if (count($results) == 0) {
    die(json_encode(['result' => false, 'result_count' => count($results)]));
}

$truckId = $results[0]['truck_id'];

file_put_contents('/tmp/test2', print_r($results, true));

$insertResult = $database->update('INSERT INTO `truck_locations` (`truck_id`, `lat`, `long`) VALUES (:truckId, :lat, :lon, :date)',
    [
        'truckId' => $truckId,
        'lat' => $lat,
        'lon' => $lon,
        'date' => $truckData['published_at']
    ]
);

echo json_encode(['result' => $insertResult]);