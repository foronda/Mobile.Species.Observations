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
			// Match all <A/> links with a title tag and use it as the content (default).
			$('span[title]').qtip
			({
				position: { at: 'bottom center', my: 'top center' },
				style: { classes: 'ui-tooltip-rounded ui-tooltip-green' }
			});
		});
		$(function() {
			$('#file_upload').uploadify({
			'height'   : 15,
			'width'	   : 75,
			'swf'      : 'player/uploadify.swf',
			'uploader' : 'php/uploadify.php'
			// Put your options here
			});
		});
		$(function() {
		  $(".editable_tweet").editable("http://hawaiiwetlands.org:8080/save.php", { 
			  indicator : "<img src='images/indicator.gif'>",
			  tooltip   : "Click to edit...",
			  style  : "display: inline",
			  id	 : 'tweetId',
			  name	 : 'newTweet',
			  select : true,
		  });
		  $(".editable_screenName").editable("http://hawaiiwetlands.org:8080/save.php", { 
			  indicator : "<img src='images/indicator.gif'>",
			  tooltip   : "Click to edit...",
			  style  : "display: inline",
			  id	 : 'screenNameId',
			  name	 : 'newScreenName',
			  select : true,
		  });
		  $(".editable_lat").editable("http://hawaiiwetlands.org:8080/save.php", { 
			  indicator : "<img src='images/indicator.gif'>",
			  tooltip   : "Click to edit...",
			  style  : "display: inline",
			  id	 : 'latId',
			  name	 : 'newLat',
			  select : true,
		  });
		  $(".editable_long").editable("http://hawaiiwetlands.org:8080/save.php", { 
			  indicator : "<img src='images/indicator.gif'>",
			  tooltip   : "Click to edit...",
			  style  : "display: inline",
			  id	 : 'longId',
			  name	 : 'newLong',
			  select : true,
		  });
		   $(".editable_media").editable("http://hawaiiwetlands.org:8080/save.php", { 
			  indicator : "<img src='images/indicator.gif'>",
			  tooltip   : "Click to edit...",
			  style  : "display: inline",
			  id	 : 'imageId',
			  name	 : 'newImage',
			  select : true,
		  });
		  $(".editable_interLocation").editable("http://hawaiiwetlands.org:8080/save.php", { 
			  indicator : "<img src='images/indicator.gif'>",
			  tooltip   : "Click to edit...",
			  style  : "display: inline",
			  id	 : 'interLocationId',
			  name	 : 'newinterLocation',
			  select : true,
		  });
		   $(".editable_date").editable("http://hawaiiwetlands.org:8080/save.php", { 
			  indicator : "<img src='images/indicator.gif'>",
			  tooltip   : "Click to edit...",
			  style  : "display: inline",
			  id	 : 'dateId',
			  name	 : 'newDate',
			  select : true,
		  });
		});   
	</script>
	</head>
	<body>
	<div id="header"></div>
	<table>
	<tr>
		<th class="blackMenu">
			<div class="tabs">
			<ul>
				<li><a href="display.php"><span title="Map points of species observations.">OBSERVATION MAPPING</span></a></li>
				<li><a href="grid.php"><span title="A detailed table of species observations.">OBSERVATION INFO</span></a></li>
				<li><a href="form.php"><span title="Enter a new species observation using this form.">CREATE NEW OBSERVATION</span></a></li>
			</ul>
			</div>
		</th>
	</tr>
	</table>
	<table class="sortable" id="alternatecolor">
		<thead>
			<tr>
				<th width="3%"></th>
				<th width="10%">Actions</th>
				<th width="10%">Screen Name</th>
				<th width="30%">Comment</th>
				<!--<th width="10%">Hashtag</th>-->
				<th width="10%">Media</th>
				<!--<th width="1%">Sound</th>
				<th width="10%">Video</th>-->
				<th width="7%">Latitude</th>
				<th width="7%">Longitude</th>
				<th width="10%">Interpolated Location</th>
				<th width="10%">Date</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$i = 0; 
			foreach($cursor as $id => $value) 
			{?>
					
				<tr class="<?php echo "d".($i & 1)?>">
				<td>
					<?php 
						
						if($currentPage > 1)
						{
							echo ($currentPage-1)*10 + $i+1;
						}
						else
						{
							echo $i+1;
						}
					?>
				</td>
				<td>
				<div id='content'>
					<!--confirm-delete id passed to jQuery function -->
					<div id='confirm-delete'>
						<a class="confirm" id="test" href="delete.php?id=<?php echo $value['_id'];?>">Delete</a>
					</div>
					<!-- modal content -->
					<div id='confirm'>
						<div class='header'><span>Confirm</span></div>
						<div class='message'></div>
						<div class='buttons'>
							<div class='no simplemodal-close'>No</div><div class='yes'>Yes</div>
						</div>
					</div>
					<!-- preload the images -->
					<div style='display:none'>
						<img src='images/confirm/header.gif' alt=''>
						<img src='images/confirm/button.gif' alt=''>
					</div>
				</div>
				</td>
				<td class="editable_screenName" id="<?php echo $value['_id'];?>"><?php echo $value['screen_name'];?></td>
				<td class="editable_tweet" id="<?php echo $value['_id'];?>"><?php echo $value['tweet'];?></td>
				<?php
				/*
						// Trims string to only display tweet 
						$tweet = $value['tweet'];
						
						// If hashtag exists, trims from hashtag on
						if (strpos($tweet, '#'))
							echo strstr($tweet, '#',true);
						// If @ exists,trims from @ on
						else if(strpos($tweet, '@'))
							echo strstr($tweet, '@', true);
						// else if(strpos($tweet, 'http'))
							// echo strstr($tweet, 'http', true);
						else
							echo $tweet;*/
					?>
				</td>
				<!--<td>
					<?php //echo $value['hashtags'];?>
				</td>-->
				<td>
				<!--<td class="editable_media" id="<?php //echo $value['_id'];?>">-->
					<?php
						if(empty($value['specImg']))
						{
							//echo '<input type="file" id="file_upload" />';
						}
						/*
						else if(!strpos(($value['specImg']), '.'))
						{ ?>
							<a href="<?php echo $value['specImg']; ?> ">Link</a> <?php echo 
						} */
						else 
						{ ?>
							<a href="<?php echo $value['specImg']; ?> ">
							<img height="75px" width="75px" src="<?php echo $value['specImg']; ?>" <?php } ?> </a>
					<?php 
						if(empty($value['specSound']))
						{
							//echo "N/A";
						}
						else if(strpos($value['specSound'], 'http'))
						{ ?>
							<object type="application/x-shockwave-flash" data="player/dewplayer-mini.swf?mp3=<?php echo $value['specSound']?>.mp3" width="50px" height="20" id="dewplayer-mini">
								<param name="wmode" value="transparent" />
								<param name="movie" value="<?php echo $value['specSound']?>.mp3" />
							</object>
						<?php }
						else
						{
						?>
							<object type="application/x-shockwave-flash" data="player/dewplayer-mini.swf?mp3=<?php echo $value['specSound']?>.mp3" width="50px" height="20" id="dewplayer-mini">
								<param name="wmode" value="transparent" />
								<param name="movie" value="<?php echo $value['specSound']?>.mp3" />
							</object>
						<?php } ?>
					<?php //echo $value['geo']['lat'];?>
				
					<?php 
						if(empty($value['specVid']))
						{
							//echo "N/A";
						}
						else
						{?>
							<a  href='<?php echo $value['specVid']?>'><img width="20px" height="20px" src="images/play.png"/></a>
						<?php } ?>
					<?php //echo $value['geo']['long'];?>
				</td>
				<td class="editable_lat" id="<?php echo $value['_id'];?>"><?php echo $value['geo']['lat']; ?></td>
				<td class="editable_long" id="<?php echo $value['_id'];?>"><?php echo $value['geo']['long']; ?></td>
				<td class="editable_interLocation" id="<?php echo $value['_id'];?>"><?php echo $value['geo']['interLocation']; ?></td>
				<td class="editable_date" id="<?php echo $value['_id'];?>"><?php echo $value['date']; ?></td>
					<?php
						// Trims the date into a more cleaner format
						//echo substr($value['date'], 0, 20); 
						//echo substr($value['date'], 26);
					?>

				</td>
				
				<?php 
					$i++; // Used to alternate row colors
				?>
				</tr>
				<?php
				// Debugs tweets array
				//var_dump($tweets);
				//echo json_encode($tweets);
			}
		?>
		</tbody>
		</table>
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