<?php
/**
 * @author Christopher Bitler
 */

include '../lib/Database.php';
include '../lib/Secrets.php';

session_start();

header("Access-Control-Allow-Origin: *.foodtrax.io");

if (!$_SESSION['id']) {
    die('err'); // Empty array
}

$ownerId = $_SESSION['id'];

// Connect to the database
$databaseCredentials = (new Secrets())->readSecrets();
$database = new Database(
    $databaseCredentials['db_user'],
    $databaseCredentials['db_pass'],
    $databaseCredentials['db_host'],
    $databaseCredentials['db_database']
);

$database->connect();

// Get the list of trucks and return them
$results = $database->query(
    'SELECT * FROM `truck_locations_memory` AS tlm LEFT JOIN `truck_information` AS ti ON ti.truck_id=tlm.truck_id WHERE `owner_id`=:id;',
    [
        ':id' => $ownerId
    ]
);
$trucks = [];


foreach ($results as $truck) {
    $trucks[] = [
        'name' => $truck['name'],
        'description' => $truck['description'],
        'twitter' => $truck['twitter'],
        'facebook' => $truck['facebook'],
        'website' => $truck['website'],
        'id' => $truck['truck_id']
    ];
}

echo json_encode($trucks);