<?php
 
 // Class for authentication
 include 'twitteroauth.php';
 
 // Helper Class for Parsing Information
 include 'helper.php';
 
 // Create MongoDB Connection
 include 'connect.php';
 
 // Twitter uses long int, so you may need to instruct the PHP/MongoDB to use long ints
 ini_set('mongo.native_long', 1);

 // Twitter Access Tokens
 $oauth_access_token = "453392093-7XhoM3gPqzUNKbK7ys2v50ZSBnTw0oSFmOfIWvUy";
 $oauth_access_token_secret = "9QAeumXw79XI3sACBeuUBNqsw0R8TzLTShFC0RMZ2s";

 // Function to connect using specified access tokens
 function getConnectionWithAccessToken($oauth_token, $oauth_token_secret) 
 {
 	$connection = new TwitterOAuth("6YS35FePtR8NK0v2on9oA", "tSXJ99W49SfAhOY1WMTUHm0syJhMxQhH57Ru35mUE", $oauth_token, $oauth_token_secret);
  	return $connection;
 }
 
 $connection = getConnectionWithAccessToken($oauth_access_token, $oauth_access_token_secret);

 // Processes Tweeter Arrays and insert into MongoDB 
 InsertTimeline($connection, $collection);
 InsertMentions($connection, $collection);
 //var_dump($connection);
 //InsertDirectMessages($connection, $collection);

 echo "<br />";  
 printf("Successful!");
 echo "<br />"; 

?>

<?php

  function InsertTimeline(&$connection, &$collection)
  {
 	// Connects and retrieves necessary tweets via oauth library and curl
    // It then converts it into JSON format
 	$usertimeline = $connection->get("statuses/user_timeline");
 	$usertimeline = json_decode($usertimeline);
	
 	// Loop array and create separate documents for each tweet
 	if(!(is_null($usertimeline))) 
	{
		$timelineCount = 0;
 	
		foreach ($usertimeline as $id => $item) 
 		{
			// Debugs usertimeline array
		 	// var_dump($item);
	
			// Collects Necessary Tweet Document	
			// Data does not need checks since data is guaranteed inside a tweet
			$id = $item->id_str;
			//$mongoId = new MongoId($id);
			//echo "ID: ".$id." MongoID: ".$mongoId."<br>";
			
			$screen_name = $item->user->screen_name;
			$name = $item->user->name;
			$img = $item->user->profile_image_url;
			$date = $item->created_at;
			
			// Calls helper functions to gather valid documents to insert
		    $lat = GetLat($item);
			$long = GetLong($item);	

			$tweet = GetTweet($item);
			$hashtags = GetHash($item);
			$specImg = GetSpecImg($item);
			$specSound = GetSpecSound($item);
			$specVid = GetSpecVid($item);
			
			try
			{
	   			// Generates a MongoDB Array and Inserts
				// Geocode left initially null since it is updated using geocode.php
				// This is because of Google API Geocode Query Limit
				// Geocode.php will be ran every 24hours instead of 10 minutes of collect.php
		   		$tweet = array("_id" => $id, "screen_name" =>  $screen_name,
			   				"name" => $name, "tweet" => $tweet, "hashtags" => $hashtags,
			   				"date" => $date, "img" => $img, "specImg" => $specImg, 
							"specSound" => $specSound, "specVid" => $specVid,
			   				"geo" => array("lat" => $lat, "long" => $long, "interLocation" => null, 
											"zipCode" => null, "city" => null, "county" => null, 
											"state" => null, "country" => null));
			
				// Since tweet id is used as _id field, update prevents mongo from 
				//(die)ing on duplicate id entries. With params 'safe' & 'upsert'
				// all duplicate entries are ignored, but catches other insertion errors.	
				$collection->insert($tweet, array('safe' =>True,));
				//$collection->update(array("_id" => $id), $tweet, array('safe' =>True, 'upsert' => true));
			} 
			catch(MongoException $e)	
			{
				// Error code 11000 results from duplicate error, skip 
				if($e->getCode() == 11000)
					$timelineCount--;
				else
					die('Failed to insert '.$e->getMessage());
			}
		$timelineCount++;
 		}
		printf("Total timelines inserted: %d", $timelineCount);
 		echo "<br />";
	}
  }

?>

<?php 

  function InsertMentions(&$connection, &$collection)
  {
 	$mentions = $connection->get("statuses/mentions");
	$mentions = json_decode($mentions);
    //var_dump($mentions);
 	if(!(is_null($mentions)))
	{
		$mentionCount = 0;
		$findImg = 'http';
 		// Loop array and create separate documents for each tweet
 		foreach($mentions as $id=> $item)
 		{
	
			// Collects Necessary Tweet Document	
			$id = $item->id_str;
			$screen_name = $item->user->screen_name;
			$name = $item->user->name;
			$img = $item->user->profile_image_url;
			$date = $item->created_at;
			
			$tweet = GetTweet($item); 
			$lat = GetLat($item);
			$long = GetLong($item);
			
			$hashtags = GetHash($item);
			$specImg = GetSpecImg($item);
			$specSound = GetSpecSound($item);
			$specVid = GetSpecVid($item);
			
			try
			{
		   		// Generates a MongoDB Array and Inserts
				// Geocode left initially null since it is updated using geocode.php
				// This is because of Google API Geocode Query Limit
				// Geocode.php will be ran every 24hours instead of 10 minutes of collect.php
		   		$tweet = array("_id" => $id, "screen_name" =>  $screen_name,
			   				"name" => $name, "tweet" => $tweet, "hashtags" => $hashtags,
			   				"date" => $date, "img" => $img, "specImg" => $specImg, 
							"specSound" => $specSound, "specVid" => $specVid,
			   				"geo" => array("lat" => $lat, "long" => $long, "interLocation" => null, 
													"zipCode" => null, "city" => null, "county" => null, 
													"state" => null, "country" => null));
			
				$collection->insert($tweet, array('safe' =>True,));
				// Function belows allows one to update fields without erasing...
				//$collection->update(array("_id" => $id), $tweet, array('safe' =>True, 'upsert' => true));
			}	 
			catch(MongoCursorException $e)	
			{
				// Error code 11000 results from duplicate error, skip 
				if($e->getCode() == 11000)
					$mentionCount--;
				else
					die('Failed to insert '.$e->getMessage());
			}
			$mentionCount++;
 		}
 		printf("Total mentions inserted: %d", $mentionCount);
 		echo "<br />";
	} 
  }
?>

<?php 

  function InsertDirectMessages(&$connection, &$collection)
  {
 	$direct_messages = $connection->get("direct_messages");
	// Convert JSON to a PHP array
 	$direct_messages = json_decode($direct_messages);
 	
 	if(!(is_null($direct_messages)))
    {
		$dmCount = 0;
 
	 	// Loop array and create separate documents for each tweet
	 	foreach($direct_messages as $id=> $item)
 		{
			// Insert Direct message identifier on tweet text.
			$tweet = "[DM] ";
	
			// Collects Necessary Tweet Document	
			$id = $item->sender->id_str;
			$screen_name = $item->sender_screen_name;
			$name = $item->sender->name;
			
			$tweet .= GetTweet($item); 
			$lat = GetLat($item);
			$long = GetLong($item);
			$hashtags = GetHash($item);
			$img = GetProfImg($item); 
			$specImg = GetSpecImg($item);
			$specSound = GetSpecSound($item);
			$specVid = GetSpecVid($item);
			$hashtags = GetHash($item); 
			
			
			$date = $item->created_at;
			try
			{
		   		// Generates a MongoDB Array and Inserts
		   		$tweet = array("_id" => new $id, "screen_name" =>  $screen_name,
			   				"name" => $name, "tweet" => $tweet, "hashtags" => $hashtags,
			   				"date" => $date, "img" => $img, "specImg" => $specImg, 
							"specSound" => $specSound, "specVid" => $specVid,
			   				"geo" => array("lat" => $lat, "long" => $long));
			
				$collection->insert($tweet, array('safe' => true));
			}	 
			catch(MongoCursorException $e)	
			{
				// Error code 11000 results from duplicate error, skip 
				if($e->getCode() == 11000)
					$dmCount--;
				else 
					die('Failed to insert '.$e->getMessage());
			}
			$dmCount++;
 		}
		printf("Total direct messages inserted: %d ", $dmCount); 
 		echo "<br />"; 
	} 
  }
?>