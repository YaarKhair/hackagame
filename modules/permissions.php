<?php
   	$is_admin = InGroup($hackerdata['id'], 1);
   	$is_mod = InGroup($hackerdata['id'], 2);
   	$is_supporter = InGroup($hackerdata['id'], 6);
   	if ($is_admin || $is_mod) $is_staff = true;
   	else $is_staff = false;
?>