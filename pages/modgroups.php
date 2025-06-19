<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	echo '<h1>User Groups</h1>';
	$side = 'left';
	$result = mysqli_query($link, "SELECT * FROM permgroup ORDER BY name ASC");
 	while ($row = mysqli_fetch_assoc($result)) 
	{
		$result2 = mysqli_query($link, "SELECT hacker.id FROM hacker_permgroup INNER JOIN hacker ON hacker_permgroup.hacker_id = hacker.id WHERE hacker_permgroup.permgroup_id = {$row['id']}");
		$list = "";
		
		while ($row2 = mysqli_fetch_assoc($result2)) 
		{
			if (InGroup($hackerdata['id'], 1)) $list .= '<a href="?h=doadminaction&action=dogroup&addremove=2&group_id='.$row['id'].'&id='.$row2['id'].'"><span class="red">X</span></a>&nbsp;';
			$list .= ShowHackerAlias($row2['id'])."<br>";
		}
		
		// display the actual list
		$title = $row['name'];
		if ($row['hidden'] == 1) $title .= '&nbsp;**HIDDEN**';
?>
		<div class="accordion">
			<input id="active-24h" type="checkbox" class="accordion-toggle">
			<label for="active-24h"><?php echo $title; ?></label>
			<div class="accordion-box">
				<?php echo $list; ?>
			</div>	
		</div>
<?php
 	}
?>