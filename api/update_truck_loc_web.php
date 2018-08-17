<?php
/**
 * @author Christopher Bitler
 */

include '../lib/Database.php';
include '../lib/Secrets.php';

session_start();

header("Access-Control-Allow-Origin: *.foodtrax.io");

if (!$_SESSION['id']) {
    die("Invalid parameters");
}

// Get the truckId and event data (lat,lon)
$id = $_POST['truckId'];
$lat = $_POST['lat'];
$lon = $_POST['lon'];

// Connect to the database
$databaseCredentials = (new Secrets())->readSecrets();
$database = new Database(
    $databaseCredentials['db_user'],
    $databaseCredentials['db_pass'],
    $databaseCredentials['db_host'],
    $databaseCredentials['db_database']
);

$database->connect();

$checkTruckOwner = $database->query(
    'SELECT truck_id, owner_id FROM `truck_information` WHERE `truck_id`= :truckid',
    [
        ':truckid' => $id
    ]
);

if (count($checkTruckOwner) === 0) {
    die(json_encode(['result' => false]));
}

$truckOwner = $checkTruckOwner[0]['owner_id'];

if ($truckOwner !== $_SESSION['id']) {
    die(json_encode(['result' => false]));
}

$truckId = $checkTruckOwner[0]['truck_id'];

// Insert the location
$insertResult = $database->update('INSERT INTO `truck_locations` (`truck_id`, `lat`, `long`, `date`) VALUES (:truckId, :lat, :lon, NOW())',
    [
        ':truckId' => (int)$truckId,
        ':lat' => (double)$lat,
        ':lon' => (double)$lon
    ]
);

// Update truck to say it is not offline.
$updateResult = $database->update(
    'UPDATE `truck_information` SET `offline`=0,`locationSetByWeb`=1 WHERE `truck_id`=:id',
    [
        ':id' => $truckId
    ]
);

echo json_encode(['result' => $insertResult && $updateResult]);
