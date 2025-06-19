<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php mysqli_query($link, "UPDATE hacker SET adblock = adblock +1 WHERE id = {$hackerdata['id']}"); ?>
<?php
	PrintMessage("info", "<img align='right' src='images/block.jpg' width='200px'><big>This game is being run as a FREE service for you.</big><br><br>We can only survive by showing a non-intrusive ad on the bottom of this page.<br><br>We noticed you use an ad-blocker. Please add us to your list of exceptions.<br><br>If you are unable to for whatever reason, please contact us: info@hackerforever.com");
?>