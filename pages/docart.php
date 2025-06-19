<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php   
    // Action
    $action = 'buy';
    if(!empty($_GET['action'])) $action = sql($_GET['action']);
    if($action != 'buy' && $action != 'remove') return "Invalid action.";
    
    if($action == 'buy') {
        // Product
        if(empty($_POST['product_id'])) return "Invalid transaction.";
        $product_id = intval($_POST['product_id']);
            
        // qunaitiy
        $quantity = 1; 
        if(!empty($_POST['quantity'])) $quantity = intval($_POST['quantity']);
        if($quantity < 1) return "Invalid quantity.";
        
        // Sorting the products into ISP, Hardware and software
        $software = array();
        $hardware = array();
        $isp = array();
		$perks = array();
        
        foreach($_SESSION['cart'] as $cart_product_id) {
            $type = mysqli_get_value("in_shop","product","id",$cart_product_id);
            if($type == 1) $software[] = $cart_product_id;
            if($type == 2) $hardware[] = $cart_product_id;
            if($type == 3) $isp[] = $cart_product_id;
			if($type == 4) $perks[] = $cart_product_id;
        }
        
        // Not allowing to add more than instance of hardware
        if(in_array($product_id,$hardware) || in_array($product_id,$isp) || in_array($product_id, $perks)) return "You can not add more than one instance of this product.";
        
        // Verifiying the product stuff
        $result = mysqli_query($link, "SELECT level, buy_multiple FROM product WHERE id = $product_id");
        if (mysqli_num_rows($result) == 0) return "Product not found.";
        $row = mysqli_fetch_assoc($result);
        
        if ($row['level'] > EP2Level(GetHackerEP($hackerdata['id']))) return "Your level is too low to purchase this item.";
        if ($row['buy_multiple'] == 0 && $quantity > 1) return "You can only buy one instance of this item.";
        
        // download queue check
        //$items = count($software);
        //if($items + $quantity > DownloadQueueMax($hackerdata['id'])) return "You can not download $quantity files. Your download queue has $slot_left slots left.";
        
        // add the items to the cart
        for($i = 1; $i <= $quantity; $i++) $_SESSION['cart'][] = $product_id;
        PrintMessage("Success", "Item added successfully to the shopping cart.");    
    } 
    if($action == 'remove') {
        $product_id = 0;
        if(!empty($_REQUEST['product_id'])) $product_id = intval($_REQUEST['product_id']);
        while(in_array($product_id,$_SESSION['cart'])) {
            $key = array_search($product_id,$_SESSION['cart']);
            unset($_SESSION['cart'][$key]);
        }
        PrintMessage("Success","Item successfully removed from the shopping cart.");
    }
    include("pages/shop.php");
?>