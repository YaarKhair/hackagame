<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish = $time;
	$total_time = round(($finish - $start), 4);
?>
				<div class="col w50">
                    <p>&copy; 2008-<?php echo date('Y'); ?> <?php echo $gameurl; ?></p>
                    <p>The Online Hacking Multiplayer Game</p>
					<p>Page loaded in <?php echo $total_time; ?> seconds</p>
                </div>
                <div class="col w50 right">
                    <p>A <a target="_blank" href="http://www.chaozz.nl/">chaozz games</a> production</p>
                    <p>Development by Elmar Wenners and Ali S.</p>
                    <p>Design by <a target="_blank" href="https://www.facebook.com/chrisfml">Chris Foteinos</a></p>
                </div>