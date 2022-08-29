<?php
require "sess.php";
include "config.php";


// GET ALL BACKLOG ITEMS ASSOCIATED WITH userid
try {
  $id = $_SESSION['pid'];
  $sql = "SELECT * FROM backlog WHERE grabid = $id";
  $statement = $connection->prepare($sql);
  $statement->execute();
  
  $items = $statement->fetchALL();
  } catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
  }


?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SCRUM My Items</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css">
  <link rel="stylesheet" href="theme.css" type="text/css">
</head>

<body >
  <nav class="navbar navbar-expand-md navbar-light bg-light">
    <div class="container"> <button class="navbar-toggler navbar-toggler-right border-0" type="button" data-toggle="collapse" data-target="#navbar6">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbar6"> <a class="navbar-brand text-primary d-none d-md-block" href="/index.php">
          <i class="fa d-inline fa-lg fa-circle"></i>
          <b> SCRUM</b>
        </a>
        <ul class="navbar-nav mx-auto">
          <li class="nav-item"> <a class="nav-link" href="#">Welcome <?php echo $username;?>!</a> </li>
          <li class="nav-item"> <a class="nav-link" href="#">You Are At My Items</a> </li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="py-5" style="background-image: url('/img/parentback.svg');background-size:cover;" >
<?php include "./view/nav.php"; ?>
        <div class="col-md-9 rounded" style="background-color:white;">
       <h1>My Items</h1>
       <?php
if ($items && $statement->rowCount() > 0) {
foreach ($items as $row) {?>

<h3><a href="grabbeditem.php?id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></h3>
<p><?php echo $row['details']; ?></p></a>
<hr>
<?php
}
} else {
    echo "You have no grabbed items!";
}
?>



		</div>
        </div>
      </div>
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
