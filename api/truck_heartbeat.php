<?php
include '../lib/Database.php';
include '../lib/Secrets.php';

header("Access-Control-Allow-Origin: *");

$json = file_get_contents("php://input");

// Decode the json payload
$truckData = json_decode($json, true);

// Get the particle ID and event data (lat,lon)
$particleId = $truckData['coreid'];
$requestType = $truckData['name'];

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

if ($requestType === 'H') {
    $update = $database->update(
        'UPDATE `truck_location_memory` SET `date`=NOW() WHERE `truck_id`=:id',
        [
            ':id' => $truckId
        ]
    );

    echo json_encode(['result' => $update]);
} else if ($requestType === 'F') {
    $update = $database->update(
        'UPDATE `truck_information` SET `offline`=1 WHERE `truck_id`=:id',
        [
            ':id' => $truckId
        ]
    );

    echo json_encode(['result' => $update]);
}