<?php
/**
 * @author Christopher Bitler
 */

include '../lib/Database.php';
include '../lib/Secrets.php';

session_start();

header("Access-Control-Allow-Origin: *.foodtrax.io");

$username = $_POST['username'];
$email = $_POST['email'];
$truckList = $_POST['trucks'];
$password = $_POST['password'];

// Verify we have expected parameters
if (!$username || !$password) {
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

// Hash the password we were given
$password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 13]);

// Check if the user exists
$results = $database->query(
    'SELECT `username` FROM `users` WHERE `username` = :username',
    [':username' => $username]
);

if (count($results) > 0) {
    die(json_encode(['result' => 'false']));
}

$insertResult = $database->update(
    'INSERT INTO `users` (`username`,`password`,`email`,`truck_list_approval`) VALUES (:username, :password, :email, :trucklist)',
    [
        ':username' => $username,
        ':password' => $password,
        ':email' => $email,
        ':trucklist' => $truckList
    ]
);

echo json_encode(['result' => $insertResult]);