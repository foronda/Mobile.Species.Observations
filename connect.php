<?php

// Connect to Mongo and set DB and Collection
try
{
	$mongo = new Mongo();
	$db = $mongo->selectDB('twitter');
	$collection = $db->selectCollection('tweets');
}

catch(MongoConnectionException $e)
{
	die("Failed to connect to Twitter Database ". $e->getMessage());
}

?>