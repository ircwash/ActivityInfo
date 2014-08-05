<?php

ini_set( 'display_errors', true);
require_once ('activityinfo_client.php');
require_once ('activityinfo_activity.php');

$ini = parse_ini_file('import.ini');

$client = new activityinfo_client($ini['username'], $ini['password']);

$activity = new activityinfo_activity( $client, $ini['database'], $ini['activity'] );

$properties = array(
	'activityId' => $activity->id,
	'partnerId' => 16,				 	// IRC
	'locationId' => 199381355,			// Netherlands
	// 'startDate' => '2014-01-01',
	// 'endDate' => '2014-06-30'
);

$data = array(
	'Staff member' => 'Harry',
	'Project' => 'C14.09 Communications',
	'Hours spent' => 7,
	'Hourly rate' => 100
);

// Add this site
$activity->addSite( $data, $properties);

// Try creating attribute
print_r( $activity->getAttributeId( 'Staff member', 'Harry Oosterveen'));

?>