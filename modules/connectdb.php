<?php
	// connect to db
	$link = @mysqli_connect("localhost","hackerforever","insert-db-password-here", "hf");
	if (mysqli_connect_error()) {
		die('Could not connect to the database');
	}
?>
