<?php
/**
 * @author Christopher Bitler
 */

include '../lib/Database.php';
include '../lib/Secrets.php';

session_start();

header("Access-Control-Allow-Origin: *.foodtrax.io");

$id = $_POST['truckid'];
$name = htmlspecialchars($_POST['truckname'], ENT_QUOTES);
$twitter = htmlspecialchars($_POST['twitter'], ENT_QUOTES);
$facebook = htmlspecialchars($_POST['facebook'], ENT_QUOTES);
$website = htmlspecialchars($_POST['website'], ENT_QUOTES);
$description = htmlspecialchars($_POST['truckdesc'], ENT_QUOTES);

// Verify we have expected parameters
if (!$id || !$name || !$description || !$_SESSION['id']) {
    die("Invalid parameters");
}

// Connect to database
$databaseCredentials = (new Secrets())->readSecrets();
$database = new Database(
    $databaseCredentials['db_user'],
    $databaseCredentials['db_pass'],
    $databaseCredentials['db_host'],
    $databaseCredentials['db_database']
);

$database->connect();

// Verify that they are the owner
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

// Update truck information
$updateTruckInformation = $database->update(
    'UPDATE `truck_information` SET `name`=:name,`description`=:desc, `twitter`=:twitter, `facebook`=:facebook, `website`=:website WHERE `truck_id`= :truckid',
    [
        ':name' => $name,
        ':desc' => $description,
        ':truckid' => $id,
        ':twitter' => $twitter,
        ':facebook' => $facebook,
        ':website' => $website
    ]
);

die(json_encode(['result' => $updateTruckInformation]));