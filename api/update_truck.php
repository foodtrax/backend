<?php
/**
 * @author Christopher Bitler
 */

include '../lib/Database.php';
include '../lib/Secrets.php';

header("Access-Control-Allow-Origin: *.foodtrax.io");

$json = file_get_contents("php://input");

// Decode the json payload
$truckData = json_decode($json, true);

// Get the particle ID and event data (lat,lon)
$particleId = $truckData['coreid'];
$data = $truckData['data'];
$latlon = explode(",", $data);

$lat = $latlon[0];
$lon = $latlon[1];

// Connect to the database
$databaseCredentials = (new Secrets())->readSecrets();
$database = new Database(
    $databaseCredentials['db_user'],
    $databaseCredentials['db_pass'],
    $databaseCredentials['db_host'],
    $databaseCredentials['db_database']
);

$database->connect();

// Verify it is a valid particle
$results = $database->query('SELECT * FROM `particle_to_truck` WHERE `particle_id` = :particle',
    [
        'particle' => $particleId
    ]
);

// If there is no valid particle, exit
if (count($results) == 0) {
    die(json_encode(['result' => false]));
}

$truckId = $results[0]['truck_id'];

// Insert the location
$insertResult = $database->update('INSERT INTO `truck_locations` (`truck_id`, `lat`, `long`, `date`) VALUES (:truckId, :lat, :lon, NOW())',
    [
        ':truckId' => (int)$truckId,
        ':lat' => (double)$lat,
        ':lon' => (double)$lon,
    ]
);

// Update truck to say it is not offline.
$updateResult = $database->update(
    'UPDATE `truck_information` SET `offline`=0,`locationSetByWeb`=0 WHERE `truck_id`=:id',
    [
        ':id' => $truckId
    ]
);

echo json_encode(['result' => $insertResult && $updateResult]);
