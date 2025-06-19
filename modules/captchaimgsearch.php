<?php
	//include("/var/www/modules/connectdb.php");
	include("/var/www/modules/settings.php");
	//include("/var/www/modules/functions.php");
	include("/var/www/modules/imageresize.php");
	
	// Initials
	$objects = array();
	$colors = array("red", "blue", "green", "yellow", "grey", "black", "white", "brown", "burgundy", "violet", "cyan", "gold", "khaki");
	$result = mysqli_query($link, "SELECT captcha_objects.id, captcha_objects.name, count(captcha_images.id) FROM captcha_objects LEFT JOIN captcha_images ON captcha_objects.id = captcha_images.object_id GROUP BY captcha_objects.id, captcha_objects.name ORDER BY COUNT(captcha_images.id), RAND()");
	while($row = mysqli_fetch_assoc($result)) $objects[$row['id']] = $row['name'];
	foreach($objects as $id => $object) {
		shuffle($colors);
		$query = urlencode($colors[0].' '.$object);
		$url = "https://ajax.googleapis.com/ajax/services/search/images?v=1.0&q=$query&userip={$_SERVER['SERVER_ADDR']}&rsz=8";

		// sendRequest
		// note how referer is set manually
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, $gameurl);
		$body = curl_exec($ch);
		curl_close($ch);

		// now, process the JSON string
		$json = json_decode($body, true);
		// now have some fun with the results...
		if(isset($json['responseData']['results'])) {
			$count = 0;
			foreach($json['responseData']['results'] as $result) {
				$contents = base64_encode(file_get_contents($result['unescapedUrl']));
				$resize = new ResizeImage(base64_decode($contents));
				$resize->resizeTo(100, 100);
				$code = $resize->saveImage();
				if(strlen($code) > 0) {
					$result = mysqli_query($link, "INSERT INTO captcha_images (image_code, object_id, approved) VALUES ('$code', $id, 0)");
					$count++;
				}
			}
		}
		if($count > 0) AddLog(0, "hacker", "captcha_bot", "Added $count images of ".mysqli_get_value("name", "captcha_objects", "id", $id).". Awaiting approval.", $now);
	}
	sleep(10);
?>