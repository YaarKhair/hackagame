<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
	<script>
		function check() {
			var input = confirm("Are you sure you want to overclock your PC? \n Overclocking your PC will increase 10% success chance but will also disable you from performing PC hacks for an hour and server hacks for two hours");
			if(input == false) {
				document.getElementById("overclock").checked = false;
			}
		}
 	</script>
<?php
	// first check if you have an offence tool
	$result = mysqli_query($link, "SELECT count(product.id) as NumItems, product.id, product.title FROM inventory INNER JOIN product ON inventory.product_id = product.id WHERE inventory.hacker_id = {$hackerdata['id']} AND product.code = 'PCHACK' AND inventory.server_id = 0 GROUP BY product.id");

	$_SESSION['dopchack'] =1;
	echo '
			<h1>Hack a PC</h1>
			<form method="POST" action="index.php" name="hf_form" class="light-bg">
				<input type="hidden" name="h" value="dopchack">
			<div class="row hr-light">
				<div class="col w35">
					<input type="text" class="icon address" placeholder="IP Address" name="ip"><br>';
					if($now >= $hackerdata['next_overclock']) echo '<input type="checkbox" name="overclock" id="overclock" onclick="check();"><label for="overclock">Overclock</label>';
				echo '</div>
				<div class="col w65">
					<p>Select Tool:</p>';
					if (mysqli_num_rows($result) == 0) PrintMessage ("Error", "Your HDD does not contain any PC hacking tools.");
					else while ($row = mysqli_fetch_assoc($result))
						echo '<INPUT TYPE="radio" NAME="product_id" ID="'.$row['id'].'" VALUE="'.$row['id'].'" checked><label for="'.$row['id'].'">'.$row['title'].' ('.$row['NumItems'].')</label><br>';

					
				echo '</div>
			</div>	
		';
        AddFormHash ("pc");

		echo '<div class="row"><input type="submit" value="Initiate hack"></div></form>';
		echo '<script type="text/javascript">document.hf_form.username.focus();</script>';	
?>