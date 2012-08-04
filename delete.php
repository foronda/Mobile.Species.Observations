<?php
	
	include 'helper.php';
	
	$mongo = new Mongo();
	$db = $mongo->twitter;
	$collection = $db->tweets;
	
	if(isset ($_GET['id']))
	{
		// For custom tweet id's, no need to use new MongoId for removing
		// Tweet Id's are 18 long, while auto generated ObjectID's are 24 long
		if(IsObjectId($_GET['id']))
			$collection->remove(array("_id" => new MongoId($_REQUEST['id'])));
		else if(!IsObjectId($_GET['id']))
			$collection->remove(array("_id" => $_REQUEST['id']));
	}
?>

<?php
	header("Location: http://hawaiiwetlands.org:8080/grid.php");
?>