<?php

//Disable debug mode
error_reporting(0);

// Create MongoDB Connection
include 'connect.php';

// The hotspots array will contain the data that will be returned
$tweets = array();

// Return a cursor of tweets from MongoDB
$cursor = $collection->find();
$mapCursor = $collection->find();

// Try catch for catching whether there are tweets to display
$count = 0;
try 
{
	$count = $cursor->count();
} 
catch (MongoCursorException $e) 
{
	die(json_encode(array('error'=>'error message:' .$e->getMessage())));
}

if (!isset($_GET['viewall']))
{
	$currentPage = (isset($_GET['page'])) ? (int) $_GET['page'] : 1;
	$tweetsPerPage = 10;
	$skip = ($currentPage - 1) * $tweetsPerPage;
	
	// Used for counting the Total Tweets 
	// Needed for page indexing
	$totalTweets = $cursor->count();
	$totalPages = (int) ceil($totalTweets / $tweetsPerPage);

	// Sorts by twitter id
	$cursor->sort(array('_id' => -1))->skip($skip)
		->limit($tweetsPerPage);
}
else
{
	$currentPage = 1;
	$totalPages = 1;
	$cursor->sort(array('_id' => -1));
}


// Loops through the cursor again specifically for querying all geo locations
// Unlike table display of tweets, this cursor is not limited by pages. 
foreach($cursor as $id => $value)
{ 
	$mapLocations[] = array
	(
		'id'=>$value['_id'],
		'screen_name'=>$value['screen_name'],
		'name'=>$value['name'],
		'tweet'=>$value['tweet'],
		'hashtags'=>$value['hashtags'],
		'lat'=>$value['geo']['lat'],
		'long'=>$value['geo']['long'],
		//'interLocation'=>$value['geo']['interLocation'],
		'date'=>$value['date'],
		'img'=>$value['img'],
		'specImg'=>$value['specImg'],
		'specSound'=>$value['specSound'],
		'specVid'=>$value['specVid']
	);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
	 <link rel="SHORTCUT ICON" href="images/logo.ico" type="image/x-icon">
	 <title>Mobile Species Observations</title>
	 <meta name="viewport" content="initial-scale=1.0, user-scalable=no">         
	 <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	 <link type="text/css" rel="stylesheet" href="style/site.css">
	 <link type="text/css" rel="stylesheet" href="style/uploadify.css">
	 <link type="text/css" rel="stylesheet" href="style/confirm.css">
	 <link type="text/css" rel="stylesheet" href="style/tabs.css">
	 <link rel="stylesheet" type="text/css" media="screen" href="style/coda-slider.css">
	 <link rel="stylesheet" type="text/css" href="style/jquery.qtip.css" >
	 <style type="text/css" media="screen">
		html { height: 100% }
		body { font-size: 13px; height: 100%; margin: 0; padding: 0 }
		#map_canvas { height: 100% }
		div#contentarea { width : 100%; }
	</style>
	<!-- Load JavaScript files -->
	<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDZQqZmW836bUVNPZ6kAHgZRMNAR3XY9No&amp;sensor=false"></script>
	<script type="text/javascript" src="javascript/sorttable.js"></script>
	<script type="text/javascript" src="javascript/jquery.js"></script>  
	<script type="text/javascript" src="javascript/jquery.uploadify.js"></script>
	<script type='text/javascript' src='javascript/jquery.simplemodal.js'></script>
	<script type="text/javascript" src="javascript/jquery.pageswitch.js" charset="utf-8"></script>
	<script type="text/javascript" src="javascript/jquery.editable.js" charset="utf-8"></script>
	<script type='text/javascript' src='javascript/confirm.js'></script>
	<script type="text/javascript" src="javascript/markerclusterer.js"></script>
	<script type="text/javascript" src="javascript/jquery.qtip.min.js"></script>
	<script type="text/javascript" >
		$(document).ready(function()
		{
			// Match all <span/> links with a title tag and use it as the content (default).
			$('span[title]').qtip
			({
				position: { at: 'bottom center', my: 'top center' },
				style: { classes: 'ui-tooltip-rounded ui-tooltip-green' }
			});
		});
		$(document).ready(function()
		{
			initialize();
		});
		/* Future implementation of file upload functionality still to be decided
		$(function() {
			$('#file_upload').uploadify({
			'height'   : 15,
			'width'	   : 75,
			'swf'      : 'player/uploadify.swf',
			'uploader' : 'php/uploadify.php'
			// Put your options here
			});
		});*/
		
		// Javascript functions below no longer implemented, prior use was to 
		// implement slide panels, or toggle divs
		/*
		$(document).ready(function(){
			$(".flipmap").click(function(){
				if($('.map').css('display') == 'none')
				{
					$(".map").slideToggle("fast");
				}
				if($('.panel').css('display') != 'none')
				{
					$(".panel").slideToggle("fast");
				}
			  });
		});
		$(document).ready(function(){
			$(".flip").click(function(){
				if($('.panel').css('display') == 'none')
				{
					$(".panel").slideToggle("fast");
				}
				if($('.map').css('display') != 'none')
				{
					$(".map").slideToggle("fast");
				}
			  });
		});
		$(document).ready(function(){
			$(".flipView").click(function(){
				alert($('.panel').css('display'));
				if($('.panel').css('display') != 'none')
				{
					$(".map").slideToggle("fast");
				}
			  });
		});
		$(document).ready(function() {	
			$('#tabs a:eq(0)').pageswitch();		// select the first a-tag and use standard settings for the animation				
			$('#tabs a:eq(1)').pageswitch();
			$('#tabs a:eq(2)').pageswitch();
			$('#tabs a:eq(3)').pageswitch();
			$('#tabs a:eq(8)').pageswitch({		// select the second a-tag
				url:		'form.php',			// overwrites the a-href
				properties: { marginLeft: -$('body').width() },	// manipulates the margin of the target	
				options: 	{ duration: 1000 }		// sets the duration of animation
			});											
		});*/
	</script>
	<script type="text/javascript">
		// Declares a global array instance of google.maps LatLng objects 
		var latlngBounds = new google.maps.LatLngBounds();
		
		function initialize() 
		{
			// Converts MongoDB information to JSON, ready for Javascript
			var tweets = <?php echo json_encode($mapLocations); ?>;
			
			// Sets google maps options
			var myOptions = 
			{
				//Centers data based LatLng Bounds
				center: new google.maps.LatLng(0, 0),
				zoom: 0,
				mapTypeId: google.maps.MapTypeId.TERRAIN
			};

			// Sets Marker Clusterer Options
			var mcOptions =
			{
				gridSize: 25, 
				maxZoom: 13
			};

			// Generates Google Map and applies the defined options above.
			var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
			var markers = [];                       //Array needed to pass to MarkerClusterer
			var marker;
			
			// Loops through each tweet and draws the marker on the map.    
			for (var i = 0; i < tweets.length; i++)
			{
				var tweet = tweets[i];
				
				// Infowindow for displaying information for onClick event      
				// Content must be inside the google.maps.event function 
				// Otherwise the same content will be entered on all markers    
				var infoWindow = new google.maps.InfoWindow({});
				
				if(tweet.lat != null || tweet.long != null) 
				{
					// Adds the marker into map, and pushes it to the array
					marker = addMarker(tweet.lat, tweet.long, map);
					markers.push(marker);
					
					google.maps.event.addListener(marker, 'click', (function(marker, i) 
					{
						return function() 
						{
							// Generates a table for infoWindow
							var content = "<table class='popup'>";

							// Check if image exits, otherwise show no image icon
							if(tweets[i].specImg != null) 
							{
								content += "<tr><th width=75 ><a href=" + tweets[i].specImg + ">";
								content += "<img height=75 width=75 src=" + tweets[i].specImg + "><\/a>";
							}
							else
							{
								content += "<tr><th width=75><img height=75 width=75 src=images/noimage.jpg>";
							}
							// Concatanate screen name and tweet
							// Will work on trimming information
							content += "<\/th><td>" + tweets[i].screen_name + " seen...<br>"; 
							content += "''" + tweets[i].tweet +  "''<br>";
							content += "on " + tweets[i].date.substr(0, 20) + tweets[i].date.substr(26);
							
							if(tweets[i].specSound != null)
							{
								content += "<br><object type=application/x-shockwave-flash data=player/dewplayer-mini.swf?mp3=" + tweets[i].specSound + ".mp3 " + 
										   "width=120 height=20 id=dewplayer-mini><param name=wmode value=transparent /><param name=movie value=" + 
											tweets[i].specSound + ".mp3 /><\/object>";
							}
							
							content += "<\/td><\/table>";
							
							// Zoom into marker on click
							map.setZoom(15);
							map.setCenter(marker.getPosition());
							
							// Sets the infoWindow content to the marker
							infoWindow.setContent(content);
							infoWindow.open(map, marker);
						}
					})(marker, i)); 
				}
			} 
			
			// Function to set map bounds on infowindow close
			closeInfoWindow = function() 
			{
				map.fitBounds(latlngBounds);
			};
			
			// Once infowindow is closed, zoom back out to map bounds
			google.maps.event.addListener(infoWindow,'closeclick', closeInfoWindow);
			
			// Declares a new clusterer object
			var markerCluster = new MarkerClusterer(map, markers, mcOptions);

			// Sets map zoom level to display all markers
			map.fitBounds(latlngBounds);
		}
			
        // This function takes in geo data and map as parameters,
        // needed for displaying the marker on the map.
        function addMarker(lat, lng, map)
        {
                var myLatLng = new google.maps.LatLng(lat, lng);
                var marker = new google.maps.Marker({
                        position: myLatLng,
                        icon: "images/markers/greenCircle.png",
                        //shadow: "images/markers/circle_s.png",
                        title: "Click To Display Information",
                });
                
                // Adds all points into a google.maps.LatLngBounds using extend() method
                latlngBounds.extend(myLatLng);
                
                return marker;
        }
        
        function closeMarker()
        {
                window.alert("closeclick fired");
                map.fitBounds(latlngBounds);
                marker.infoWindow.close();
        }
	</script>
	</head>
	<body onload="initialize()">
	<div id="header"></div>
	<table>
	<tr>
		<th class="blackMenu">
			<div class="tabs">
			<ul>
				<li><a href="map.php"><span title="Map points of species observations.">OBSERVATION MAPPING</span></a></li>
				<li><a href="grid.php"><span title="A detailed table of species observations.">OBSERVATION INFO</span></a></li>
				<li><a href="form.php"><span title="Enter a new species observation using this form.">CREATE NEW OBSERVATION</span></a></li>
			</ul>
			</div>
		</th>
	</tr>
	</table>
	<div class="map">
	<table>
		<tr class="darkgreen">
			<td align="center">
				<div id="map_canvas" style="width:1000px; height:744px"></div>	
			</td>
		</tr>
	</table>
	</div>
	<table>
	<tr>
		<th class="blackMenu">
			<div class="tabs">
				<ul>
					<li>
						<?php if($currentPage !== 1 && !isset($_GET['viewall'])): ?>
							<a href="<?php echo $_SERVER['PHP_SELF'].'?page='.($currentPage - 1);?>"><span>Previous</span></a>
						<?php endif; ?>
					</li>
					<li>
						<?php if(!isset($_GET['viewall'])): ?>
							<a href="<?php echo $_SERVER['PHP_SELF'].'?viewall'; ?>"><span>VIEW ALL OBSERVATIONS</span></a>
						<?php endif; ?>
					</li>
					<li>
						<?php if($currentPage !== $totalPages && !isset($_GET['viewall'])): ?>
							<a href="<?php echo $_SERVER['PHP_SELF'].'?page='.($currentPage + 1);?>"><span>Next</span></a>
						<?php endif; ?>
					</li>				
					<li>
						<?php if(isset($_GET['viewall'])): ?>
							<a href="<?php echo $_SERVER['PHP_SELF']; ?>"><span>LIMIT OBSERVATIONS</span></a>
						<?php endif; ?>
					</li>
				</ul>
			</div>
		</th>
	</tr>
	</table>
	</body>
</html>