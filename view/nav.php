<div class="container">
      <div class="row">
        <div class="col-md-3 mb-2">
          <ul class="list-group">
            <li class=" border-0 list-group-item d-flex justify-content-between align-items-center"><a href="/index.php">SCRUM HOME</a><br><i class="fa fa-home text-muted fa-lg"></i></li>
            <li class=" border-0 list-group-item d-flex justify-content-between align-items-center"><a href="/createepic.php">CREATE EPIC</a><br><i class="fa fa-plus text-muted fa-lg"></i></li>
            <li class=" border-0 list-group-item d-flex justify-content-between align-items-center"><a href="/myitems.php">MY ITEMS</a><br><i class="fa fa-shopping-bag text-muted fa-lg"></i></li>
            <?php
            if (isset($_SESSION["admin"])) {
              if ($_SESSION["admin"] == "true") { ?>
			<li class=" border-0 list-group-item d-flex justify-content-between align-items-center"><a href="/admin.php">ADMIN</a><br><i class="fa fa-cog text-muted fa-lg"></i></li>

			<?php
}
} ?>

            <li class=" border-0 list-group-item d-flex justify-content-between align-items-center"><a href="/logout.php">LOG OUT</a><br><i class="fa fa-sign-out text-muted fa-lg"></i></li>
          </ul>
        </div>
<!-- Need to have my Epics, My Scrums, My Items on the menu. -->
