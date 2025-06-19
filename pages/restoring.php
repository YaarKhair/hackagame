<?php
	if($hackerdata['restoring_till'] > $now) {
		PrintMessage("warning", "Your system is restoring from being hacked. <br> It will be done restoring in ".Seconds2Time(SecondsDiff($now, $hackerdata['restoring_till'])));	
	} else {
		PrintMessage("error", "You are not supposed to be here.");	
	}
?>