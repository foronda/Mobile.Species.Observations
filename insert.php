<?php

	 // Helper Class for Parsing Information
	 include 'helper.php';

	 $mongo = new Mongo();
	 $db = $mongo->twitter;
	 $collection = $db->tweets;

	 // variables to insert into mongo
	 $name = $_POST['name'];
	 $screen_name = $_POST['screen_name'];
	 $tweet = $_POST['species'];
	 $date = $_POST['seendate'];
	 
	 $specSound = $_POST['specSound'];
	 if(!$specSound == "")
	 {
		// Link not submitted, voicemail mp3 link inserted
		if(!strpos($specSound, 'http'))
			// adds a backslash to voice
			$voice = substr("voice\\", 0, 6);
			$specSound = $voice.$specSound;
	 }
	 else
	 {
		$specSound = null;
	 }

	 // Gets Geo Data
	 if(isset($_POST['lat']) && $_POST['long'])
	 {
		$lat = $_POST['lat'];
		$long = $_POST['long'];
		$locInfo = GetLocationInfo($lat, $long);
	 
		 // Declare and set geocoded information
		 $interLocation = $locInfo['interLocation'];
		 $zipCode = $locInfo['zipCode'];
		 $city = $locInfo['city'];
		 $county = $locInfo['county'];
		 $state = $locInfo['state'];
		 $country = $locInfo['country'];
	}
	else
	{
		$lat = NULL;
		$long = NULL;
		$interLocation = NULL;
		$zipCode = NULL;
		$city = NULL;
		$county = NULL;
		$state = NULL;
		$country = NULL;
	}
				
	 // pending values
	 $hashtags = NULL;
	 $img = NULL;
	 $specImg = NULL;
	 $specVid = NULL;

	 try
	 {
		// Generates a MongoDB Array and Inserts
		$tweet = array("screen_name" =>  $screen_name,
					"name" => $name, "tweet" => $tweet, "hashtags" => $hashtags,
					"date" => $date, "img" => $img, "specImg" => $specImg, 
					"specSound" => $specSound, "specVid" => $specVid,
					"geo" => array("lat" => $lat, "long" => $long, "interLocation" => $interLocation, 
									"zipCode" => $zipCode, "city" => $city, "county" => $county, 
									"state" => $state, "country" => $country));

		// Since tweet id is used as _id field, update prevents mongo from 
		//(die)ing on duplicate id entries. With params 'safe' & 'upsert'
		// all duplicate entries are ignored, but catches other insertion errors.	
		$collection->insert($tweet, array('safe' =>True));
	 } 
	 catch(MongoException $e)	
	 {
		// Error code 4 results from duplicate error, skip 
		if($e->getCode() == 11000);
		else
			die('Failed to insert '.$e->getMessage());
	 }
?>
<html>
<script type="text/javascript">
alert("Data has been submitted!");
</script>
</html>

<?php
	header("Location: http://hawaiiwetlands.org:8080/");
?>

