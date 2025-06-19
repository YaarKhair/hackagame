<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<form method="POST" action="index.php">
  <input type="hidden" name="h" value="domodstaffmessage">
  <input type="hidden" name="action" value="add">
  <input type="text" name="message">
  <input type="submit" value="Add Staff Message">
</form>
<br>
<form method="POST" action="index.php">
  <input type="hidden" name="h" value="domodstaffmessage">
  <input type="hidden" name="action" value="remove">
  <input type="submit" value="Remove Current Staff Message">
</form>