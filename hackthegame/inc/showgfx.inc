<?php
  	  // welke page tonen?
  	  if (empty($_GET['gfx'])) { echo "screenshot not found"; exit; }
  	  else { $gfx=$_GET['gfx']; }
?>

<div align="center">
 <img src="gfx/<?php echo $gfx; ?>" border="0" alt="<? echo $gfx; ?>" />
</div>