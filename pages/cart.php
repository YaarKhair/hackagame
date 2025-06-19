<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
    $count = count($_SESSION['cart']);
    $array = array_count_values($_SESSION['cart']); 

    $total_price = 0;
?>
	<div class="row">
		<h2>Shopping cart</h2>
		<div class="shop-cart col w100">
			<div class="row th dark-bg">
				<div class="col w30">Item</div>
				<div class="col w25">Type</div>
				<div class="col w25">Quantity</div>
				<div class="col w20">Price</div>
			</div>
<?php
    // list articles
    if($count > 0) {
        foreach($array as $product_id => $quantity) {
            $result = mysqli_query ($link, "SELECT title, price, in_shop FROM product WHERE id = $product_id");
            $row = mysqli_fetch_assoc($result); // we're assuming it exists?
            
            $product_name = $row['title'];
            $product_price = $row['price'] * $quantity;
            $type = $row['in_shop'];
            if($type == 1) $type = 'Software';
            if($type == 2) $type = 'Hardware';
            if($type == 3) $type = 'ISP';      
						if($type == 4) $type = 'Perk';
						if($type == 5) $type = 'Server';
            $total_price += $product_price;

            echo "<div class='row mv10'>
                    <div class='col w30'><a href='?h=docart&action=remove&product_id=$product_id'>[X]</a>&nbsp;$product_name</div>
                    <div class='col w25'>$type</div>
                    <div class='col w25'>$quantity</div>
                    <div class='col w20'>$currency".number_format($product_price)."</div>
                </div>";
        }
        
    }
    else echo '<div class="row">Your cart is empty</div>';
?>
			<div id="shop-checkout" class="row">
				<div class="col w80">
					<form action="index.php" method="POST">
						<input type="hidden" name="h" value="doshop">
						<input type="submit" value="Checkout">
					</form>
				</div>
				<div class="col w20">Total price:<br><strong><?php echo $currency.number_format($total_price); ?></strong></div>
			</div>
		</div>
	</div>