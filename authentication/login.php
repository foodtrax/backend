<?php
include '../lib/Database.php';
include '../lib/Secrets.php';

session_start();

header("Access-Control-Allow-Origin: *");

$username = $_POST['username'];
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

// Get the user's hashed password from the database
$results = $database->query(
    'SELECT `id`,`password` FROM `users` WHERE `username` = :username',
    [':username' => $username]
);

// Exit early if the user doesn't exist
if (count($results) === 0) {
    die(json_encode(['result' => 'false']));
}

// Grab the hashed password from the query results
$dbPassword = $results[0]['password'];

// If the passwords match, set the session
if (password_verify($password, $dbPassword)) {
    $_SESSION['id'] = $results[0]['id'];
    echo json_encode(['result' => true]);
} else {
    echo json_encode(['result' => false]);
}