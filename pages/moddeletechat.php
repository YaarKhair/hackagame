<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
    // Not an admin or mod?
	$admin = InGroup($hackerdata['id'], 1);
   	$mod = InGroup($hackerdata['id'], 2);
   	if(!$admin && !$mod) return 'Session error.';

   	// Get the ids
   	$id_array = array();
   	if(!empty($_POST['id'])) $id_array = $_POST['id'];

   	$id_array = array_map("intval", $id_array);	// Sanitize all inputs and make sure they're all integers
    
    $error = 0;
   	foreach($id_array as $id) {
   	    $result = mysqli_query($link, "UPDATE chat SET deleted = 1 WHERE id = $id");
  	    if(!$result) $error = 1;	// There was an error in any of the queries
   	}

   	if($error != 1) echo '<script>location.href="?h=modchathistory";</script>'; 
 	else return 'Error.';
?>