<?php
	include("modules/permissions.php");

	// Get the id
	if(!isset($id)) $id = $hackerdata['id'];
	if(!empty($_GET['id'])) $id = intval($_GET['id']);
	
	// Query the database
	$result = mysqli_query($link, "SELECT hacker.*, clan.alias as previous_clan, country.name FROM hacker LEFT JOIN clan ON hacker.previous_clanid = clan.id LEFT JOIN country ON hacker.country = country.code WHERE hacker.id = $id");
	if(mysqli_num_rows($result) == 0) return 'Hacker not found.';
	$row = mysqli_fetch_assoc($result);
	
	// Do some checks on the account to see if you can view it
	if (substr($row['email'], 0, 2) == "**" && !$is_staff) return "You can not view profiles of disabled accounts.";
	
?>
<div id="profile-wrapper">
	<h1><?php echo ShowHackerAlias($row['id'], 0, 0, true, false, 0, 0, 0); ?></h1>
	<div class="row hr-light mv10">
		<div class="col w25">
			<p class="mb10"><?php echo ShowAvatar($row['id'], 0, '', "hacker", false); ?></p>
			<?php if($row['publicstats'] == 1) { ?><p class="mb10"><?php echo '<a href="?h=personalstats&hacker_id='.$row['id'].'">'.PrintIcon('stats', 'dark').'View Stats</a>'; ?></p><?php } ?>
			<?php if($hackerdata['id'] != $row['id']) { ?><p class="mb10"><?php echo '<a href="?h=dowriteim&username='.$row['alias'].'">'.PrintIcon('mail', 'dark').'Send message</a>'; ?></p><?php } ?>
			<?php if($row['id'] == $hackerdata['id']) { ?><p class="mb10"><?php echo '<a href="?h=edithacker">'.PrintIcon('edit', 'dark').'Edit Profile</a>'; ?></p><?php } ?>
		</div>
		
		<div class="col w75 hr-light" id="profile-content">
			<?php echo ReplaceBBC($row['extra_info']); ?>
		</div>
	</div>
	
	<div id="profile-info" class="hr-light">
		<div class="row mv10">
			<div class="col w25"><?php echo PrintIcon('connection', 'large').GetStatus($row['id']); ?></div>
			<div class="col w25"><?php if($hackerdata['id'] == $row['id']) echo '<a href="?h=selectethic">'; ?><?php echo PrintIcon('hat', 'large').mysqli_get_value('ethic', 'ethic', 'id', $row['ethic_id']); ?><?php if($hackerdata['id'] == $row['id']) echo '</a>'; ?></div>
			<div class="col w25"><?php echo PrintIcon('joined', 'large').Number2Date($row['started']); ?></div>
			<div class="col w25"><?php echo PrintIcon('country', 'large').'<img src="./images/flags_new/'.$row['country'].'.png" title="'.$row['country'].'" class="badge"/>'; ?></div>
		</div>
		<div class="row mv10">
			<div class="col w25"><?php echo '<a href="?h=claninfo&id='.$row['clan_id'].'">'.PrintIcon('clan', 'large').mysqli_get_value('alias', 'clan', 'id', $row['clan_id']).'</a>'; ?></div>
			<div class="col w25"><?php echo PrintIcon('clan-previous', 'large').$row['previous_clan']; ?></div>
			<div class="col w25"><?php echo PrintIcon('last-seen', 'large').Number2Date($row['last_click']); ?></div>
			<div class="col w25"><?php echo PrintIcon('group', 'large').GetGroups($row['id']); ?></div>
		</div>
		<div class="row mv10">
			<div class="col w25"><?php if ($row['clan_id'] != $staff_clanid) echo PrintIcon('level', 'large').EP2Level($row['ep']); else echo PrintIcon('level', 'large')."N/A"; ?></div>
			<div class="col w25"><?php echo PrintIcon('rank-ep', 'large').GetWorldRankEP($row['id']); ?></div>
			<div class="col w25"><?php echo PrintIcon('rank-hp', 'large').GetWorldRankHP($row['id']); ?></div>
			<div class="col w25">&nbsp;</div>
		</div>
	</div>
	<?php if(strlen($row['prev_alias']) > 0) { ?>
	<p class="mv10 italic">&mdash; previously known as "<?php echo $row['prev_alias']; ?>"</p>
	<?php } ?>
</div>
<?php
	// Prepare admin stuff if you are an admin
	if($is_admin) include 'modadminprofile.php';
	if($is_staff) include 'modmodprofile.php';
	if($is_staff) include 'modticketsstafflogs.php';
?>