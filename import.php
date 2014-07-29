<?php
require_once ('activityinfo_client.php');

$ini = parse_ini_file('import.ini');

$ai = new activityinfo_client($ini['username'], $ini['password']);

$databases = $ai->getDatabase(1383);

print_r( $databases );

// foreach ($databases as $database) {
//     print_r($database);
//     print_r($ai->getDatabase($database['id']));
// }

// print_r( $ai->getSites($ini['partner']));

// print_r( $ai->getAdminLevels( 'NL'));
// print_r( $ai->getEntities( 1403));
// print_r( $ai->getLocations( 1317));

?>