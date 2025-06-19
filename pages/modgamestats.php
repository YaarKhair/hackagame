<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	//include("../modules/connectdb.php");
    //include("../modules/settings.php");
    //include("../modules/functions.php");
	
	// what year do we show the stats from?
	$show_year = substr($now, 0, 4); // the current year
	if (!empty($_GET['year'])) $show_year = intval($_GET['year']);

	// show stats per month
	$result = mysqli_query ($link, "SELECT count(id) AS signups, substring(started, 5, 2) AS month FROM `hacker` WHERE left(started, 4) = '$show_year' GROUP BY month ORDER BY month ASC");

	$signups = array(); 
	while ($row = mysqli_fetch_assoc($result)) {
		$signups[] = array($row['month'], $row['signups']);
	}
	
	//Include the code
	require_once 'modules/phplot/phplot.php';

	//create a PHPlot object with 800x600 pixel image
	$plot = new PHPlot(860,480);

	// Add the data
	$plot->SetDataValues($signups);
	
	// Do some customization
	$plot->SetPlotType('bars');
	$plot->SetDataType('text-data');

	//Set titles
	$plot->SetTitle("Signups Chart for $show_year");
	$plot->SetXTitle('Months');
	$plot->SetYTitle('Number Of Signups');

	//Turn off X axis ticks and labels because they get in the way:
	$plot->SetXTickLabelPos('none');
	$plot->SetXTickPos('none');
	
	// disable output
	$plot->SetPrintImage(false);
	$plot->DrawGraph();

	// Get the base64 of the image
	$base64 = $plot->EncodeImage();
	echo "<img src='$base64'>";

?>