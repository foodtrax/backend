<?php
include '../lib/Database.php';
include '../lib/Secrets.php';

header("Access-Control-Allow-Origin: *");

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
$results = $database->query('SELECT * FROM `truck_locations_memory` AS tlm LEFT JOIN `truck_information` AS ti ON ti.truck_id=tlm.truck_id;', []);
$trucks = [];

foreach ($results as $truck) {
    $trucks[] = [
        'name' => $truck['name'],
        'description' => $truck['description'],
        'twitter' => $truck['twitter'],
        'lat' => $truck['lat'],
        'long' => $truck['long']
    ];
}

echo json_encode($trucks);