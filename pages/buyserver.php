<?php
if (!IsUnhackable($hackerdata['id'])) return "This page is under construction";

?>
<h1>Buy a server</h1>
<form action="?h=dobuyserver" method="POST" class="light-bg">
  <div class="row hr-light">
    <div class="col w50">
      Number of servers:
    </div>
    <div class="col w50">
      <select name="server_count">
        <option>0</option>
      </select>
    </div>
  </div>
</form>