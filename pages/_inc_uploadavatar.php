<?php
	
	// Remove avatar
	$remove_avatar = false;
	if(!empty($_POST['remove_avatar'])) $remove_avatar = $_POST['remove_avatar'];
	if(CheckBox($remove_avatar) == 1) DeleteAvatar($entity_id, $entity);

	if ($entity == "clan") {
		if ($hackerdata['donator_till'] > $now) $avatar_size = $premium_clan_avatarsize;
		else $avatar_size = $clan_avatarsize;
		$height = $clanavatar_h;
		$width = $clanavatar_w;
	}
	else {
		if ($hackerdata['donator_till'] > $now) $avatar_size = $premium_hacker_avatarsize;
		else $avatar_size = $hacker_avatarsize;
		$height = $hackeravatar_h;
		$width = $hackeravatar_w;
	}

	$avatar = "";
	$avatar = @$_FILES['avatar']['name'];
	$size = @$_FILES['avatar']['size'];
	$tmp_name = @$_FILES["avatar"]["tmp_name"];
	
	if ($avatar != "") {
		$ext = explode(".", $avatar);
		$ext = strtolower(array_pop($ext));
		if (!in_array($ext, $allowed_avatar_extensions)) return "You can only upload (".implode(",", $allowed_avatar_extensions).") files.";
		$uploaded_type = exif_imagetype($tmp_name);
		if ($uploaded_type != 1 && $uploaded_type != 2 && $uploaded_type != 3) return "You can only upload image files.";
		if ($size > $avatar_size) return "Image too large. A maximum of ".round($avatar_size/1000)."kb is allowed.";
		
		$target = "./uploads/$entity/$entity_id.$ext";
		DeleteAvatar($entity_id, $entity);
		if(move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
			chmod($target,0644);
			$dimensions = getimagesize($target);   
			list($w, $h) = getimagesize($target);
			if ($w > $width || $h > $height || empty($target)) {
				@unlink($target);
				return"Avatar is too large or image is invalid. Maximum size is $height x $width";
			}
		}
	}	
?>