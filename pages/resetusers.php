<?php
	session_start();
	// modules
	include_once("../modules/connectdb.php");
	include_once("../modules/settings.php");
	include_once("../modules/functions.php");
	//var_dump($link);
	//$result = mysqli_query($link, "UPDATE hacker SET password = '', pass_resettoken = '0', force_passchange = 1, salt = ''");
	//echo 'test';
	//echo mysqli_get_value("password", "hacker", "id", 8157);
	//$result = mysqli_query($link, "UPDATE hacker SET lockout_till = '$now' WHERE id = 8157");
?>