<?php 
header("Last-Modified: " . gmdate("D, d M Y H:i:S") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");  
include_once("../modules/connectdb.php");
include_once("../modules/functions.php");
date_default_timezone_set('Europe/Amsterdam');
$date_format = "YmdHis";				// database format for dates (MUST BE SAME AS IN SETTINGS.PHP)
$now = date($date_format);	// now!
$id = intval($_GET['id']); // this can be forged. anyone can check the inbox count of anyone. big deal?
$code = sql($_GET['code']);
// is this you or did you forge the id?
$result = mysqli_query($link, "SELECT started, last_login FROM hacker WHERE id = $id");
if (mysqli_num_rows($result) == 0) {
	echo 0;
	@mysqli_free_result($result);
	@mysqli_close($link);
	exit;
}	
else {
	$row = mysqli_fetch_assoc($result);
	//wrong code? kick!
	if (sha1($row['started'].$row['last_login']) != $code) {
		echo 0;
		@mysqli_free_result($result);
		@mysqli_close($link);
		exit;
	}	
	$output = '';
	$result = mysqli_query($link, "SELECT id FROM im WHERE reciever_id = $id AND unread = 1 AND reciever_del = 0 AND date < ".$now);
	$output = mysqli_num_rows($result);
	echo $output;
	@mysqli_free_result($result);
	@mysqli_close($link);
}
?>