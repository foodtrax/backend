<?php
include '../lib/Database.php';
include '../lib/Secrets.php';

session_start();

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

// Hash the password we were given
$password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 13]);

// Get the user's hashed password from the database
$results = $database->query(
    'SELECT `password` FROM `users` WHERE `username` = :username',
    [':username' => $username]
);

// Exit early if the user doesn't exist
if (count($results) === 0) {
    die(json_encode(['result' => 'false']));
}

// Grab the hashed password from the query results
$dbPassword = $results[0]['password'];

// If the passwords match, set the session
echo $dbPassword . "..." . $password;
if ($dbPassword === $password) {
    $_SESSION['name'] = $username;
    echo json_encode(['result' => true]);
}