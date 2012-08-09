<?php

// Connect to Mongo and set DB and Collection
try
{
	$mongo = new Mongo();
	$db = $mongo->selectDB('twitter');
	// If admin user is added, authentication is needed
	// even if keyFile authentication exists
	//$db->authenticate("dbusername","password");
	$collection = $db->selectCollection('tweets');
}

catch(MongoConnectionException $e)
{
	die("Failed to connect to Twitter Database ". $e->getMessage());
}

?>