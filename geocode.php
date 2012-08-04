<?php
 include 'helper.php';
 include 'connect.php';
 // Twitter uses long int, so you may need to instruct the PHP/MongoDB to use long ints
 //ini_set('mongo.native_long', 1);
 
 // Call Functions To Process Geocode Data, Update MongoDB With new Information
 GeocodeDB($collection);
 
 ?>
 
 <?php 
 
	/* Function to do reverse geocoding of database documents containing lat and long values
	**
	*/
	function GeocodeDB($collection)
	{
		// Returns a cursor of all items in the database;
		$cursor = $collection->find();
		$geoCount = 0;
		
		// Loop array and create separate documents for each tweet
		if($cursor->count() > 0) 
		{
			foreach ($cursor as $id => $value) 
			{
				// If interlocation is already there, no need to update?
				// Edited lat and long are handled by save.php UpdateField()
				if(!isset($value['geo']['interLocation']))
				{
					//id used as identity field for upsert
					$id = $value['_id'];
				
					try
					{					 
						// Updates field based on _id field
						$updateData = array('$set' => GetGeocodeArray($value));
						$dbOptions = array('safe' =>True, 'upsert' => true);
						
						//echo "ID:".$id." ScreenName:".$value['screen_name']."<br>";
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
					$geoCount++;
				}
			}
			printf("Total Database Records Reverse Geocoded: %d", $geoCount);
			echo "<br />";
		}
	}
 ?>