<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	echo '<div id="shop-wrapper">';
	if(empty($_SESSION['cart'])) $_SESSION['cart'] = array();
	
	if(empty($_SESSION['shop'])) $_SESSION['shop'] = 'none'; // if not set, set it
	if (!empty($_GET['shop'])) $_SESSION['shop'] = sql($_GET['shop']); // if send via GET, GET it
	$shop = $_SESSION['shop'];
	
	if ($shop != "isp" && $shop != "software" && $shop != "hardware" && $shop != "perk" && $shop != "server") return "Wrong shop ID";
	
	switch ($shop) {
		case "software":
			$query = "SELECT * FROM product WHERE in_shop = 1 ORDER BY productgroup_id, price";
			$logo = "1338supplysoftware.png";
			$efficiency = GetEfficiency ($hackerdata['id'], "INTERNET"); // for download times.
			break;
		case "hardware":
			$query = "SELECT * FROM product WHERE in_shop = 2 ORDER BY productgroup_id, price";
			$logo = "1338supplyhardware.png";
			break;
		case "isp":
			$query = "SELECT * FROM product WHERE in_shop = 3 OR in_shop = 5 ORDER BY productgroup_id, price";
			$logo = "skynet.png";
			if ($hackerdata['network_id'] == 1) PrintMessage ("warning", "<font color=\"red\"><img src=\"/images/noob_wiz.png\" align=\"right\">Buying any internet connection will disconnect you from n00bNET and connect you to the internet. On the internet you are no longer protected from other hackers.<br><br>Be sure you are ready for this!</font>", "60%");
			break;
		case "perk":
			$query = "SELECT * FROM product WHERE in_shop = 4 ORDER BY productgroup_id, price";
			$logo = "perk_shop.png";
			break;
		/*case "server":
			$query = "SELECT * FROM product WHERE in_shop = 5 ORDER BY productgroup_id, price";
			$logo = "skynet.png";*/
	}		
	
	// Display Image
	echo '<img src="images/'.$logo.'" title="'.$shop.' Shop"/><br><br>';
	if ($shop == "software") PrintMessage("info", "In an effort to evade law enforcement the IP address of this server will change every $shop_ipchange_interval hours. If we change our IP, your pending file transfers will fail.");
	include 'cart.php';

	$result = mysqli_query($link, $query);
	if (mysqli_num_rows($result) == 0) 
		return "Shop not found!";
		
	$products = array();
		
	$productgroup_id = 0;
	while ($row = mysqli_fetch_array($result)) {
		// software groups
		$productgroup_name = mysqli_get_value("name", "productgroup", "id", $row['productgroup_id']);
		$productgroup_id = $row['productgroup_id'];	

		// Description and Extra
		$description = nl2br($row['description']);
		if ($row['spreading'] == 1) $description .= "<br><br><strong>This virus spreads to neighbouring systems of the same hacker.</strong>";
		$extra = '';
		if ($shop == "software") $extra = "&nbsp;[".DisplaySize($row['size'])."]&nbsp;[D/L: ".DownloadTime($hackerdata['id'], $row['size'])." minutes]";
		if ($shop == "isp") $extra = "&nbsp;[".round(DownloadSpeed($row['efficiency']/60))."MB/s]&nbsp;[".DownloadMax($row['efficiency'])." simultaneous File Transfer(s)]";
		$extra .= '[Level: '.$row['level'].']';
		
		// Multiple quantity
		$multiple = '<input type="hidden" name="quantity" value="1">';
		if ($row['buy_multiple'] == 1) $multiple = '<input type="text" name="quantity" value="1" size="2" maxlength="3">';
		
		// Disabled Image
		if ($row['level'] > EP2Level(GetHackerEP($hackerdata['id']))) $id = "disabledimage";
		else $id = "";
		
		// checkbox on images if they have it installed, else normal picture
		if (!HasProductInstalled($hackerdata['id'], $row['id'])) $image = '<img src="images/'.$row['image'].'" title="'.$row['title'].'" id="'.$id.'" />';
		else $image = '<img style="background:url(images/'.$row['image'].')" src="images/check.gif" alt="" />';
		
		// Insert them into an array for later display
		$products[$productgroup_name][] = array("title" => $row['title'], "image" => $image, "extra" => $extra, "description" => $description, "price" => $row['price'], "multiple" => $multiple, "id" => $row['id']);
		$products[$productgroup_name]['name'] = $productgroup_name;
	}
	
	foreach($products as $category) {
?>
		<div class="accordion">
		   <input id="<?php echo str_replace(" ", "_", $category['name']); ?>" type="checkbox" class="accordion-toggle">
		   <label for="<?php echo str_replace(" ", "_", $category['name']); ?>"><?php echo $category['name']; ?></label>
		   <div class="accordion-box">

			   <div class="row th hr">
				   <div class="col w60">Item</div>
				   <div class="col w15">Price</div>
				   <div class="col w25">Quantity</div>
			   </div>
		
<?php		foreach($category as $item) { 
			if(!is_array($item)) continue; ?>
			<div class="shop-item row hr">
                <div class="col w60">
					<?php echo $item['image']; ?>
					<h2><?php echo $item['title'].'<p class="shop-item-details">'.$item['extra'].'</p>'; ?></h2>
					<?php echo $item['description']; ?>
				</div>
				
				<div class="col w15">
                	<p><?php echo $currency.number_format($item['price']); ?></p>
                </div>
				
				<div class="col w25">
                	<form method="post" action="index.php#<?php echo str_replace(" ", "_", $category['name']); ?>" class="alt-design">
						<input type="hidden" name="h" value="docart">
						<input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
						<?php echo $item['multiple']; ?>
                        <input type="submit" value="Add to cart">
                    </form>
                </div>
			</div>
	<?php	} ?>
		</div>
	</div>
<?php	} ?>
	</div>
