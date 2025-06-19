<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$product_id = 0;
	if (!empty($_GET['product_id'])) $product_id = intval($_GET['product_id']);
?>	
					<h1>Product Info</h1>
					<div class="row th">
						<div class="col w60">Description</div>
						<div class="col w20">Size</div>
						<div class="col w20">Shop Price</div>
					</div>
<?php
	$result = mysqli_query($link, "SELECT * FROM `product` WHERE `id` = ".$product_id);
	while ($row = mysqli_fetch_assoc($result)) {
		$description = nl2br($row['description']);
		$size = DisplaySize($row['size']);
		if ($size == 0) $size = "N/A";
		$price = $currency.number_format($row['price']);
?>
			<div class='light-bg'>
				<div class="row hr-light">
					<div class="col w60">
					<h2><?php echo $row['title']; ?></h2>
					<?php echo $description; ?>
					</div>
					<div class="col w20"><?php echo $size; ?>
					</div>
					<div class="col w20"><?php echo $price; ?>
					</div>
				</div>
			</div>	
<?php
	}
?>