<?php
/**
 * @author Christopher Bitler
 */

include '../lib/Database.php';
include '../lib/Secrets.php';

$particleTimeout = 60*10; // 10 minutes in seconds
$webTimeout = 60*60*5; // 5 hours in seconds
$query = 'UPDATE `truck_information`,(
            SELECT `ti`.`truck_id` FROM `truck_locations_memory` AS tlm 
	          LEFT JOIN `truck_information` AS ti ON ti.truck_id=tlm.truck_id WHERE timestampdiff(SECOND, `date`, now()) > :diff AND `locationSetByWeb`=:setWeb
          ) as `timed_out_trucks` SET `offline`=1 WHERE `truck_information`.`truck_id` = `timed_out_trucks`.`truck_id`;';

// Connect to the database
$databaseCredentials = (new Secrets())->readSecrets();
$database = new Database(
    $databaseCredentials['db_user'],
    $databaseCredentials['db_pass'],
    $databaseCredentials['db_host'],
    $databaseCredentials['db_database']
);

$database->connect();

$database->update($query,
    [
        ':diff' => $particleTimeout,
        ':setWeb' => 0
    ]
);

$database->update($query,
    [
        ':diff' => $webTimeout,
        ':setWeb' => 1
    ]
);