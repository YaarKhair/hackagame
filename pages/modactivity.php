<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	$data = array();
	$last_date = date('Ymd', strtotime($now));
	foreach(range(1, 14) as $number) {
		$date = date('Ymd', strtotime("$last_date - 1 days"));
		$result = mysqli_query($link, "SELECT DISTINCT hacker.id as active_hackers FROM hacker LEFT JOIN log on hacker.id = log.hacker_id WHERE log.event = 'interval' AND log.details = 'docaptcha' AND hacker.banned_date = '0' AND log.date >= '$date' AND log.date <= '$last_date'");
		$num = mysqli_num_rows($result);
		$data[] = array("from" => date('Y-m-d', strtotime($last_date)), "to" => date('Y-m-d', strtotime($date)), "active_hackers" => $num);
		$last_date = $date;
	}	
	
	// Build data for the graph
	require 'modules/libchart/classes/libchart.php';
	$chart = new LineChart(800, 600);
	$serie1 = new XYDataSet();
	$data = array_reverse($data);
	foreach($data as $arr)
		$serie1->addPoint(new Point("{$arr['from']} => {$arr['to']}", $arr['active_hackers']));

	$chart->setDataSet($serie1);
	$chart->setTitle("Activity in the last 2 weeks");
	$chart->render("images/graph_activity.png");

	echo "<img src='images/graph_activity.png?".rand(0,1000000)."'><br><br>";
	$data = array_reverse($data);


	?>
		<div class="row th">
			<div class="col w50">Day</div>
			<div class="col w50">Active Hackers</div>
		</div>
		<div class="light-bg">
			<?php
				foreach($data as $arr) echo '<div class="row mv5"><div class="col w50">'.$arr['from'].' => '.$arr['to'].'</div><div class="col w50">'.$arr['active_hackers'].'</div></div>';
			?>
		</div>
