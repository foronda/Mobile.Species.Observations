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
	 <link rel="stylesheet" type="text/css" href="style/jquery.qtip.css">
	 <style type="text/css" media="screen">
		html { height: 100% }
		body { font-size: 13px; height: 100%; margin: 0; padding: 0 }
		#map_canvas { height: 100% }
		div#contentarea { width : 100%; }
	</style>
	<script type="text/javascript" src="javascript/jquery.js"></script> 
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
	</script>
	</head>
	<body>
	<div id="header"></div>
	<script type="text/javascript">
		function clearText(field)
		{	 
			if (field.defaultValue == field.value) field.value = '';
			else if (field.value == '') field.value = field.defaultValue;
		}
	</script>
	<form name="form1" method="post" action="insert.php">
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
	<table>
		<tr> 
          <td width="25%">
			<div align="left">Name: </div>
		  </td>
		  <td colspan="3"> 
			<div align="left"> <input name="name" title="Enter Your Name (e.g. Sam Droege)" type="text" size="40" maxlength="40">
		  </td>
		</tr>
        <tr> 
          <td>
			<div align="left">Screen Name: </div>
		  </td>
		  <td colspan="3"> 
			<div align="left"> <input name="screen_name" title="Enter Screen Name (e.g. Voice0568)" type="text" size="40" maxlength="40">
		  </td>
		</tr>
        <tr>
          <td>
		    <div align="left">Species Seen And|Or Comment: </div>
		  </td>
          <td colspan="3">
		    <div align="left"> <input name="species" title="Enter Species (e.g. Crickets)" type="text" size="40" maxlength="40">
		  </td>
        </tr>
		<tr>
		  <td>
			<div align="left">Species Sound: </div>
		  </td>
          <td colspan="3">
		    <div align="left"> <input name="specSound" title="Enter Sound Link (e.g. http://tinyvox.com/96Q)" type="text" size="40" maxlength="100">
		  </td>
		</tr>
		<tr>
		  <td>
			<div align="left">Latitude: </div>
		  </td>
          <td colspan="3">
		    <div align="left"> <input name="lat" title="Enter Latitude (e.g. 43.19190399)" type="text" size="40" maxlength="40">
		  </td>
		</tr>
		<tr>
		  <td>
			<div align="left">Longitude: </div>
		  </td>
          <td colspan="3">
		    <div align="left"> <input name="long" title="Enter Longitude (e.g. -89.64453178)" type="text" size="40" maxlength="40">
		  </td>
		</tr>
		<tr>
		  <td>
			<div align="left">Date Seen: </div>
		  </td>
          <td colspan="3">
		    <div align="left"> <input name="seendate" title="Enter Seen Date (e.g. Fri Apr 27 01:10:57 2012) type="text" size="40" maxlength="40">
		  </td>
		</tr>
	</table>
	<tr>
	  <td colspan="3" class="style2">
		<div align="center">
          <input type="submit" name="Submit" id="Submit" onClick="alert('The record has been submitted')" value="Submit" />
      </td>
    </tr>
	</table>
	</div>
	</body>	
</html>