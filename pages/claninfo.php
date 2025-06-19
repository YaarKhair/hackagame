<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	include ("modules/permissions.php");
	
	if (!empty($_GET['id'])) {
		$id = intval($_GET['id']);
	}
	else $id = $hackerdata['clan_id'];
	
	$query = "SELECT * FROM clan WHERE id = ".$id;
	$result = mysqli_query($link, $query);
	if (mysqli_num_rows($result) == 0) { 
		PrintMessage ("Error", "Clan not found.", "40%"); 
	}
	else {
		// clan data
		$servercount = 0;
		$row = mysqli_fetch_assoc($result);
		$code = sha1($hackerdata['started'].$hackerdata['last_login']);	
		
		if ($row['active'] == 0 && !$is_staff) {
			PrintMessage ("Error", "This clan is no longer active...", "40%");
		}
		else {
			$result2 = mysqli_query($link, "SELECT count(id) as servercount FROM server WHERE hacker_id = ".$row['founder_id']);
			$row2 = mysqli_fetch_assoc($result2);
			$servercount += $row2['servercount']; // servers of the founder
			
			
			// memberlist
			$result2 = mysqli_query($link, "SELECT hacker.id, hacker.alias, count(server.id) as servercount FROM hacker LEFT JOIN server on hacker.id = server.hacker_id WHERE hacker.clan_id = ".$id." AND hacker.id <> ".$row['founder_id']." GROUP BY hacker.id");
			$memberlist = "";
			if (mysqli_num_rows($result2) == 0) $membercount = 0;
			else {
				$membercount = mysqli_num_rows($result2);
				while ($row2 = mysqli_fetch_assoc($result2)) {
					$memberlist .= ShowHackerAlias($row2['id'], 0, false, true, true, false) . ', ';
					$servercount += $row2['servercount']; // + servers of the members
				}
				$memberlist = substr($memberlist, 0, strlen($memberlist)-2); // remove last comma
				if ($memberlist == "") { $memberlist = "-"; } 
			}
			//if (!HasInstalled($hackerdata['id'],"SERVERSNIFFER")) $servercount = "?";
	
			// avatar
			$avatar = ShowAvatar ($id, 0, "", "clan");
			
			// clan topic, if any
			$message = replaceBBC($row['extra_info']);
			if(strlen($message) == 0) $message = '//undefined;';
			
			// Wars won and lost
			$wars_won = $row['wars_won'];
			$wars_lost = $row['wars_lost'];
			
			// is the clan active?
			$active = 'Yes';
			if($row['active'] == 0) $active = 'No';
			
			// Gateway status
			$gateway_status = '<div class="red">Offline</div>';
			if(IsOnlineServer(GetGateway($id))) $gateway_status = '<div class="green">Online</div>';
		}
	}
?>
	<h2>Clan Info</h2>
	<div class="row dark-bg">
		<div class="col w50">
			<div class="row mv10">
				<div class="col w45">Clan:</div>
				<div class="col w55"><?php echo $row['alias']; ?></div>
			</div>
			<div class="row mv10">
				<div class="col w45">Short-tag:</div>
				<div class="col w55"><?php echo $row['shorttag']; ?></div>
			</div>
			<div class="row mv10">
				<div class="col w45">Leader:</div>
				<div class="col w55"><?php echo ShowHackerAlias($row['founder_id'], 0, false, true, true, false); ?></div>
			</div>
			<div class="row mv10">
				<div class="col w45">Started:</div>
				<div class="col w55"><?php echo Number2Date($row['started']); ?></div>
			</div>
			<div class="row mv10">
				<div class="col w45">Color:</div>
				<div class="col w55"><div style="width: 25px; height: 25px; background-color: #<?php echo $row['color']; ?>">&nbsp;</div></div>
			</div>
			<div class="row mv10">
				<div class="col w45">Gateway status:</div>
				<div class="col w55"><?php echo $gateway_status; ?></div>
			</div>
			<div class="row mv10">
				<div class="col w45">War Statistics:</div>
				<div class="col w55">Won: <span class="green"><?php echo $wars_won; ?></span><br>Lost: <span class="red"><?php echo $wars_lost; ?></span></div>
			</div>
			<div class="row mv10">
				<div class="col w45">Servers:</div>
				<div class="col w55"><?php echo $servercount; ?></div>
			</div>
			<div class="row mv10">
				<div class="col w45">Members (<?php echo $membercount.' / '.(GetClanSize($id) - 1); ?>):</div>
				<div class="col w55"><?php echo $memberlist; ?></div>
			</div>
			<div class="row mv10">
				<?php echo $avatar; ?>
			</div>
		</div>
		<div class="col w50 light-bg">
			<h2>Clan Text</h2>
			<?php echo $message; ?>
		</div>
	</div>
	<!-- Council row -->
	<?php if($hackerdata['clan_council'] == 1 && $hackerdata['clan_id'] == $id) { ?>
	<div class="row light-bg mv10">
		<form method="POST" action="index.php">
			<input type="hidden" name="h" value="editclan">
			<input type="submit" value="Edit Clan">
		</form>
		<?php if(isFounder($hackerdata['id'])) { ?>
		<input type="button" value="Forgot Password" onclick="window.location = '<?php echo $gameurl; ?>/?h=doresetclanpass';">
		<?php } ?>
	</div>
	<?php } ?>
	<!-- Other clan leaders' row -->
	<?php 
		if(isFounder($hackerdata['id']) && $hackerdata['clan_id'] != $id) { 
			$code = SHA1($hackerdata['started'].$hackerdata['last_login']);
	?>
	<div class="row light-bg mv10">
		<form action="index.php" method="POST">
			<input type="hidden" name="h" value="dowar">
			<input type="hidden" name="action" value="declare">
			<input type="hidden" name="id" value="<?php echo $id; ?>">
			<input type="hidden" name="code" value="<?php echo $code; ?>">
			<input type="submit" value="Declare War">
		</form>
	</div>
	<?php }
		if($is_staff) {
	?>
		<div class="row">
			<div class="accordion">
				<input id="modclaninfo" type="checkbox" class="accordion-toggle">
				<label for="modclaninfo">Moderator Actions</label>
				<div class="accordion-box">
					<div class="row mv5">
						<div class="col w30">Modlog:</div>
						<div class="col w70"><a href="?h=modlog&entity=clan&alias=<?php echo $row['alias']; ?>">View</a></div>
					</div>
					<div class="row mv5">
						<div class="col w30">Clan Forums:</div>
						<div class="col w70"><a href="?h=forum&board_id=<?php echo mysqli_get_value("id", "board", "clan_id", $id); ?>">Open Clan Forums</a></div>
					</div>
					<div class="row mv5">
						<div class="col w30">Active:</div>
						<div class="col w70"><?php echo $active; ?></div>
					</div>
					<div class="row mv5">
						<div class="col w30">Bank Account:</div>
						<div class="col w70"><?php echo number_format($row['bankaccount']); ?></div>
					</div>
					<div class="row mv5">
						<div class="col w30">Last Founder Login:</div>
						<div class="col w70"><?php echo Number2Date($row['last_login']); ?></div>
					</div>
					<div class="row mv5">
						<div class="col w30">Gateway IP:</div>
						<div class="col w70"><?php echo GetGatewayIP($id); ?></div>
					</div>
					<div class="row mv5">
						<div class="col w30">Delete Avatar:</div>
						<div class="col w70">
							<form action="index.php" method="POST" class="alt-design">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="deleteavatar">
								<input type="hidden" name="entity" value="clan">
								<input type="hidden" name="clan_id" value="<?php echo $row['id']; ?>">
								<input type="submit" value="Delete">
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>

	<?php 
		}
	?>