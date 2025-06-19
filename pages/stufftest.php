<?php
$from = "hacker";
$to = "hacker";
$row['id'] = 1;
$amount = 100;
$row2['alias'] = 'chaozz';
$reason = "test";
if ($from == "hacker") $link = '@';
else $link = '#';
if ($to == "hacker") SendIM (0, $row['id'], "Cyber Bank", "The ".$from." [[".$link.$row2['alias']."]] has sent you ".$currency.number_format($amount)."<br>Reason: ".$reason, $now);	// instant message
?>