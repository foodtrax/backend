<?php
include '../lib/Database.php';
include '../lib/Secrets.php';

header("Access-Control-Allow-Origin: *");

if (!$_SESSION['id']) {
    die("Invalid parameters");
}

// Get the particle ID and event data (lat,lon)
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
    'SELECT owner_id FROM `truck_information` WHERE `truck_id`= :truckid',
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

$truckId = $results[0]['truck_id'];

// Insert the location
$insertResult = $database->update('INSERT INTO `truck_locations` (`truck_id`, `lat`, `long`, `date`) VALUES (:truckId, :lat, :lon, FROM_UNIXTIME(:datetime))',
    [
        ':truckId' => (int)$truckId,
        ':lat' => (double)$lat,
        ':lon' => (double)$lon,
        ':datetime' => time()
    ]
);

echo json_encode(['result' => $insertResult]);