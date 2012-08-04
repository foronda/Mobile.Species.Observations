<?php
	// Helper Class for Parsing Information
	include 'helper.php';
	
	// Connect to mongo db
	$mongo = new Mongo();
	$db = $mongo->twitter;
	$collection = $db->tweets;
		
	// Using isset function avoids throwing an error
	if(isset($_POST['tweetId']))
	{
		UpdateField($collection, $_POST['tweetId'], "tweet", $_POST['newTweet']);
	}
	else if(isset($_POST['screenNameId']))
	{
		UpdateField($collection, $_POST['screenNameId'], "screen_name", $_POST['newScreenName']);
	}
	else if(isset($_POST['latId']))
	{
		UpdateField($collection, $_POST['latId'], "geo.lat", $_POST['newLat']);
		IsReverseGeocodable($collection, $_POST['latId']);
	}
	else if(isset($_POST['longId']))
	{
		UpdateField($collection, $_POST['longId'], "geo.long", $_POST['newLong']);
		IsReverseGeocodable($collection, $_POST['longId']);
	}
	else if(isset($_POST['imageId']))
	{
		UpdateField($collection, $_POST['imageId'], "specImg", $_POST['newImage']);
	}
	else if(isset($_POST['dateId']))
	{
		UpdateField($collection, $_POST['dateId'], "date", $_POST['newDate']);
	}
?>

<?php
	
	/* A function that updates mongodb fields given the following parameters
	** $collection = mongodb database connection
	** $id = document is found by filtering by document id
	** $fieldId = the fieldId to update
	** $newValue = the new value to set to fieldId
	*/
	function UpdateField(&$collection, $id, $fieldId, $newValue)
	{
		// Removes \t from string, when tabs aren't deleted correctly during edit
		$newValue = str_replace("\t", '', $newValue);
		
		//Updates Data on table
		print $newValue;
						
		// Generates Query Data and Options
		$updateData = array('$set' => array($fieldId =>$newValue));					
		$dbOptions = array('safe' =>True, 'upsert' => true);
		
		// Function belows allows one to update fields based on id parameter swithout erasing...
		$collection->update(GetFilter($id), $updateData, $dbOptions);
	}
	
	/* Function that check if edited lat&long fields has been entered 
	** If so, it is then geocodable, update geo array based on id field
	*/
	function IsReverseGeocodable(&$collection, $id)
	{
		$cursor = $collection->find(GetFilter($id));
		if($cursor->count() > 0)
		{
			foreach($cursor as $id => $value)
			{
				try
				{					 
					// Updates field based on _id field
					$updateData = array('$set' => GetGeocodeArray($value));
					$dbOptions = array('safe' =>True, 'upsert' => true);
					
					// Function belows allows one to update fields based on id parameter swithout erasing...
					$collection->update(GetFilter($id), $updateData, $dbOptions);
				}
				catch(MongoException $e)	
				{
					// Error code 11000 results from duplicate error, skip 
					if($e->getCode() == 11000)
						$geoCount--;
					else
						die('Failed to insert '.$e->getMessage());
				}
			}
		}
	}
?>

<html>
<script type="text/javascript">
alert("\tInformation has been updated!\t");
</script>
</html>

<?php

?>