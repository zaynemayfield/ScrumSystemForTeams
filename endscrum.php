<?php
require "sess.php";
include "config.php";

$redi = $_GET['id'];

//HANDLE MOVING ITEMS BACK TO BACKLOG
if (isset($_GET['blidremove'])) {

  try {
    $sql = "UPDATE backlog SET `number` = `number`+1 WHERE scrumid IS NULL";
    $connection->exec($sql);
  } catch (PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }

  try {
    $blid = $_GET['blidremove'];
    $scrumid = $_GET['id'];
    $sql = "UPDATE backlog SET scrumid = NULL, `status` = 'new', `number` = '1' WHERE id = $blid";
    $connection->exec($sql);
  } catch (PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }
  header("Location: endscrum.php?id=$redi");
  die();
}

//HANDLE DELETING BACKLOG ITEM
if (isset($_GET['deleteblid'])) {
  $id = $_GET['deleteblid'];
  $sql = "DELETE FROM backlog WHERE id = $id";
  $connection->exec($sql);
  header("Location: endscrum.php?id=$redi");
  die();
}

//HANDLE ENDING SCRUM
if (isset($_GET['done'])) {
  $id = $_GET['done'];
  $sql = "UPDATE scrum SET `status` = 'done' WHERE id = $id";
  $connection->exec($sql);
  header("Location: index.php");
  die();
}


// GET ALL GRABBED ITEMS ASSOCIATED WITH THIS SCRUM
try {
    $scrumid = $_GET['id'];
  $sql = "SELECT * FROM backlog WHERE scrumid = $scrumid AND (`status` = 'grabbed' OR `status` = 'scrummed' OR `status` = 'review') ORDER BY `number` ASC";
    $gstatement = $connection->prepare($sql);
    $gstatement->execute();
    $grabbed = $gstatement->fetchALL();
    } catch (PDOException $error) {
    echo $sql . "<br />" . $error->getMessage();
    }

?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="refresh" content="180">
  <title>End Scrum</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css">
  <link rel="stylesheet" href="theme.css" type="text/css">
</head>

<body >
  <nav class="navbar navbar-expand-md navbar-light bg-light border-bottom border-dark">
    <div class="container"> <button class="navbar-toggler navbar-toggler-right border-0" type="button" data-toggle="collapse" data-target="#navbar6">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbar6"> <a class="navbar-brand text-primary d-none d-md-block" href="/index.php">
          <i class="fa d-inline fa-lg fa-circle"></i>
          <b> SCRUM</b>
        </a>
        <ul class="navbar-nav mx-auto">
          <li class="nav-item"> <a class="nav-link" href="#">Welcome <?php echo $username;?>!</a> </li>
          <li class="nav-item"> <a class="nav-link" href="#">You Are At End Scrum</a> </li>
          <li class="nav-item"> <a class="nav-link" href="index.php">SCRUM HOME</a> </li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="py-2" style="background-color:#eeeeee;" >


<div class="row">
        <div class="col-md rounded pt-2 mt-2 mr-3 ml-3" >
<!-- SHOW BACKLOG ITEMS THAT HAVE BEEN GRABBED -->
<h5>
  <strong> > Unfinished Items:</strong>
</h5>
<?php
if ($grabbed && $gstatement->rowCount() > 0) {
foreach ($grabbed as $row) {
  ?>
<div class="row bg-white shadow-sm border-0 border-white mb-1">
<div class="col-9">
<?php echo $row['name']; ?> : <?php echo $row['details']; ?></strong>
<?php if (empty($row['comments'])) {
} else { ?>
<br>
<span class="pl-3 text-success"><strong><?php echo $row['comments']; ?></strong></span>
  <?php
}
try {
  $backlogid = $row['id'];
$sql = "SELECT * FROM files WHERE backlogid = $backlogid";
$fstatement = $connection->prepare($sql);
    $fstatement->execute();
    $files = $fstatement->fetchALL();
}  catch (PDOException $error) {
  echo $sql . "<br>" . $error->getMessage();
}
if ($files && $fstatement->rowCount() > 0) { ?>
  <br> <span class="pl-3 text-dark">Files:
<?php  foreach ($files as $file) {
if (empty($file['fileurl'])) {
} else { ?>
<strong> - <a href="<?php echo $file['fileurl']; ?>"> <?php echo $file['name']; ?> </a></strong></span>
  <?php

} } } ?>
</div>
<div class="col col-3 text-right">
<?php echo $row['points']; ?> |
<a href="endscrum.php?id=<?php echo $_GET['id']; ?>&blidremove=<?php echo $row['id']; ?>" class="text-success"><i class="fa fa-upload" aria-hidden="true"></i> Move To Backlog |</a>
<a href="endscrum.php?id=<?php echo $_GET['id']; ?>&deleteblid=<?php echo $row['id']; ?>" class="text-danger">Delete Item |</a>
</div>
</div>



<?php
}} else{ ?>
		</div>
<a class="btn btn-danger btn-sm float-right mr-2" href="endscrum.php?id=<?php echo $_GET['id']; ?>&done=<?php echo $_GET['id']; ?>">
  END SCRUM
</a>
<?php } ?>
</div>
  </div>
  <div class="py-3">
    <div class="container">
      <div class="row">
        <div class="col-md-12 text-center">
          <p class="mb-0">Powered By DTEKED © <?php echo date("Y"); ?> DTEKED LLC. All rights reserved</p>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
