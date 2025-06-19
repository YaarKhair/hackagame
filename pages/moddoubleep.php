<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
	
<h1>Double EP</h1>

<h1>Add a Double EP day</h1>
	<form method="POST" action="index.php">
		<input type="hidden" name="action" value="add">
		<input type="hidden" name="h" value="domoddoubleep">
		Date (yyymmdd): <input type="text" name="date" value="<?php echo substr($now, 0, 8); ?>"><br>
		<input type="submit" value="Add">
	</form>
<?php	

	for ($i = 0; $i <= 1; $i ++)
	{
		if ($i == 0) 
		{
			$result = mysqli_query($link, "SELECT id, date FROM doubleep_date WHERE date >= '$now' ORDER BY date DESC"); // future
			$title = "Future Double EP Dates";
		}
		else 
		{
			$result = mysqli_query($link, "SELECT id, date FROM doubleep_date WHERE date < '$now' ORDER BY date DESC"); // past
			$title = "Past Double EP Dates";
		}
		
		$list = '';
?>
			<div class="accordion">
				<input id="active-24h" type="checkbox" class="accordion-toggle">
				<label for="active-24h"><?php echo $title; ?></label>
				<div class="accordion-box">
<?php
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result))
			{
				if ($i == 0) $list .= '<a href="?h=domoddoubleep&action=delete&id='.$row['id'].'"><span class="red">X</span></a>&nbsp;';
				$list .= Number2Date($row['date']).'<br>';
			}
			echo $list;
		}
?>			
				</div>	
			</div>
<?php
	}	
?>
