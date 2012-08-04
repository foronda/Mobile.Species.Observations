<?php 
	// Helper functions for validating fields to insert to database
	// Checks whether the values are exists in the JSON query

	function GetTweet(&$item)
	{
		// Performs a case insensitive string replacement of tweet
		// Removes any variance of "DevDemoHi"
		if(isset($item->text))
			return str_ireplace("@DevDemoHi", " ", $item->text);
		else 
			return null;
	}

	function GetLat(&$item)
	{
		if(isset($item->geo))
			return $item->geo->coordinates[0];
		else 
			return null;
	}

	function GetLong(&$item)
    {
		if(isset($item->geo))
			return $item->geo->coordinates[1];
		else
			return null;
	}
	
	function GetHash(&$item)
	{
		if(isset($item->entities->hashtags[0]))	
			return  $item->entities->hashtags[0]->text;
		else
			return null;
	}
	
	function GetProfImg(&$item)
	{
		if(isset($item->user['profile_image_url']))
			return $item->user['profile_image_url'];
		else
			return null;
	}
	
	/* Function that checks the entities array parsed from twitter feed.
	** This entities array contains the media url when users upload images.
	** This url obtained is then returned to the function call to be stored in mongo
	** If there are no images uploaded, it scans for 'http' string for instagram uploaded images
	*/ 
	function GetSpecImg(&$item)
	{
		if(isset($item->entities->media[0]))
		{
			return $item->entities->media[0]->media_url;
		}
		else if(isset($item->entities->urls))
		{
			for($i = 0; $i < count($item->entities->urls); $i++)
			{
				// Checks if string 'instagr.am' exists in the current url index
				if(strpos($item->entities->urls[$i]->expanded_url, 'instagr.am'))
				{
					return $item->entities->urls[$i]->expanded_url;
				}
			}
		}
		else
			return null;
	}
	
	/* Function that loops throught the URL array parsed from the twitter feed.
	** It then scans for a specific tinyvox method for uploading sound/mp3 files.
	** This sound (tinyvox) url is then returned to the function call to be stored in mongo
	*/
	function GetSpecSound(&$item)
	{
		$urls = $item->entities->urls;
		if(isset($urls))
		{
			for($i = 0; $i < count($urls); $i++)
			{
				// Checks if string 'tinyvox' exists in the current url index
				if(strpos($urls[$i]->expanded_url, 'tinyvox'))
				{
					return $urls[$i]->expanded_url;
				}
			}
		}
		else
			return null;
	}
	
	/* Function that loops through the URL array parsed from the twitter feed
	** and then scans for a specific yfrog method for uploading videos.
	** This video (yfrog) url is then returned to the function call to be stored in mongo.
	*/
	function GetSpecVid(&$item)
	{
		$urls = $item->entities->urls;
		if(isset($urls))
		{
			for($i = 0; $i < count($urls); $i++)
			{
				// Checks if string 'yfrog' exists in the current url index
				if(strpos($urls[$i]->expanded_url, 'yfrog'))
					return $urls[$i]->expanded_url;
			}
		}
		else
			return null;
	}
	
	/* Function that queries google api for reverse geocoding
	** Parameters, $lat and $long
	*/
	function GetLocationInfo($lat, $long)
	{	
		if(isset($lat) && isset($long))
		{
			$url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$long."&sensor=true";
			$geocode = file_get_contents($url);
			$geocode = json_decode($geocode);
			
			//var_dump($geocode);
			$addComp = $geocode->results[2]->address_components;
			
			//Declare variables to store in array
			$interLocation = $geocode->results[0]->formatted_address;	
									// Geocode JSON Types
							
			$zipCode = null;		// postal_code
			$city = null;			// locality
			$county = null;			// administrative_area_level_2
			$state = null;			// administrative_area_level_1
			$country = null;		// country
			
			// Due to array order variation
			// Loops Through address_components array 
			// Set the variables to correct instances
			for($i = 0; $i < count($addComp); $i++)
			{
				$type = $addComp[$i]->types[0];
				
				if($type == "postal_code")
					$zipCode = $addComp[$i]->long_name;
				else if($type == "locality")
					$city = $addComp[$i]->long_name;
				else if($type == "administrative_area_level_2")
					$county = $addComp[$i]->long_name;
				else if($type == "administrative_area_level_1")
					$state = $addComp[$i]->long_name;
				else if($type == "country")
					$country = $addComp[$i]->long_name;
			}
			
			$locArray = array (
								'interLocation' => $interLocation,
								'zipCode' => $zipCode,
								'city' => $city,
								'county' => $county,
								'state' => $state ,
								'country' => $country
							);
			//var_dump($locArray);
			return $locArray;
		}
		else
		{
			return null;
		}
	}
	
	/* Function that determines ID type of id field
	** Two types of ID: tweetID(18 long) && ObjectId (24 long)
	** TweetID: Generated from twitter JSON 
	** ObjectID: Automatically generated from Manual Species Insertion
	*/
	function IsObjectId($id)
	{
		if(strlen($id) == 18)
			return false;
		else if(strlen($id) == 24)
			return true;
		else
			return -1;
	}
	
	/* Function that gets which id to filter queries by
	** Used for updating documents
	*/
	function GetFilter($id)
	{
		if(IsObjectId($id))
			return array("_id" => new MongoId($id));
		else if(!IsObjectId($id))
			return array("_id" => $id);
		else
			return null;
	}
	
	function GetGeocodeArray($value)
	{
		// Gets the array containing geocoded data given lat long parameters
		$locInfo = GetLocationInfo($value['geo']['lat'], $value['geo']['long']);
		
		// Declare and set geocoded information
		$interLocation = $locInfo['interLocation'];
		$zipCode = $locInfo['zipCode'];
		$city = $locInfo['city'];
		$county = $locInfo['county'];
		$state = $locInfo['state'];
		$country = $locInfo['country'];
		
		// This insert query only update fields assigned not replace the whole geo array
		return array("geo.interLocation" => $interLocation, "geo.zipCode" => $zipCode, "geo.city" => $city, 
					 "geo.county" => $county, "geo.state" => $state, "geo.country" => $country);
	}
	
	function GetNewInsertQuery()
	{
	
	}
?>
