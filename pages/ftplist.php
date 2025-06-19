<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($hackerdata['network_id'] != 2) return "You are connected to ".mysqli_get_value("name", "network", "id", 1).". The function you are trying to use is unavailable from this network.";
	
	// search box
	$output = '<div class="accordion">
        <input id="other_tools" type="checkbox" class="accordion-toggle" checked>
        <label for="other_tools">Connect to FTP by IP</label>
        <div class="accordion-box">
			<h3>Connect to FTP server</h3>
			<FORM NAME="tool3" ACTION="index.php" METHOD="GET" class="light-bg">
				<INPUT TYPE="hidden" NAME="h" VALUE="ftp">
				<div class="row mv5">
					<div class="col w20">IP:</div>
					<div class="col w80"><input type="text" name="server_ip"></div>
				</div>
				<div class="row mv5">
					<input type="submit" value="Connect">
				</div>
			</form><br>	
			</div>
		</div>';

	$output .=  '<form method="POST" action="index.php" name="hf_form">
			<input type="hidden" name="h" value="doftpsearch">
			<input type="text" name="searchfor" class="icon search" placeholder="Search file"> <input type="submit" value="Search!">
		</form>
		<script type="text/javascript">document.hf_form.searchfor.focus();</script>
		<br><br>';
	
	$types = array();
	$types[] = array("title" => "Official Game FTP Servers", "query" => "SELECT server.ip, server.hacker_id, server.ftp_title, count(inventory.id) as numFiles FROM server LEFT JOIN product on server.product_id = product.id LEFT JOIN inventory ON server.id = inventory.server_id WHERE server.ftp_public = 1 AND server.hacker_id = 1 AND product.code = 'SERVERFTP' GROUP BY server.id ORDER BY server.ftp_title", "mods only" => 0);
	$types[] = array("title" => "Public FTP Servers", "query" => "SELECT server.ip, server.hacker_id, server.ftp_title, count(inventory.id) as numFiles FROM server LEFT JOIN product on server.product_id = product.id LEFT JOIN inventory ON server.id = inventory.server_id WHERE server.ftp_public = 1 AND server.hacker_id <> 1 AND product.code = 'SERVERFTP' GROUP BY server.id ORDER BY server.ftp_title", "mods only" => 0);
	$types[] = array("title" => "Private FTP Servers", "query" => "SELECT server.ip, server.hacker_id, server.ftp_title, count(inventory.id) as numFiles FROM server LEFT JOIN product on server.product_id = product.id LEFT JOIN inventory ON server.id = inventory.server_id WHERE server.ftp_public = 0 AND product.code = 'SERVERFTP' GROUP BY server.id ORDER BY server.ftp_title", "mods only" => 1);
	
	foreach($types as $ftp) { 
		$display_output = false;
		if($ftp['mods only'] == 1 && (InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2))) $display_output = true;
		if($ftp['mods only'] == 0) $display_output = true;
		$output.= '<h2>'.$ftp['title'].'</h2>
		<div class="row th">
			<div class="col w30">FTP Server</div>
			<div class="col w40">Owner</div>
			<div class="col w15">Files</div>
			<div class="col w15">Actions</div>
		</div>';

		$result = mysqli_query($link, $ftp['query']);
		if(mysqli_num_rows($result) == 0) $output .= "<div id='row'>".PrintMessage("Info", "There are no {$row['title']} online.");
		else {
			$output .=  "<div class='light-bg'>";
			while($row = mysqli_fetch_assoc($result)) {
				$title = $row['ftp_title'];
				if ($title == "") $title = $row['ip'];
				$output .=  "<div class='row hr-light'>";
				$output .=  "<div class='col w30'>$title</div>";
				$output .=  "<div class='col w40'>".ShowHackerAlias($row['hacker_id'], 1)."</div>";
				$output .=  "<div class='col w15'>{$row['numFiles']}</div>";
				$output .=  '<div class="col w15"><input type="button" value="Connect" onclick="redirect(\'?h=ftp&server_ip='.$row['ip'].'\');"></div>';
				$output .=  "</div>";
			}
			$output .=  "</div>";
		}
		if($display_output) { echo $output.'<br>'; $output = ''; }
	}
?>