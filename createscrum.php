<?php
require "sess.php";
include "config.php";


if (isset($_POST['submit'])) {

try {
  $newscrum = array(
    "epicid"  => $_POST['epicid'],
    "userid"  => $_SESSION['pid'],
    "name"      => $_POST['name'],
    "details"   => $_POST['details'],
    "datebegin" => $_POST['datebegin'],
    "dateend"   => $_POST['dateend'],
  );

  $sql = sprintf(
      "INSERT INTO %s (%s) values (%s)",
      "scrum",
      implode(", ", array_keys($newscrum)),
      ":" . implode(", :", array_keys($newscrum))
  );

  $statement = $connection->prepare($sql);
  $statement->execute($newscrum);
} catch(PDOException $error) {
  echo $sql . "<br>" . $error->getMessage();
}
  $lastid = $connection->lastInsertId();
  $userid = $_SESSION['pid'];
  try{
  $sql = "INSERT INTO scrumplayers (userid, scrumid, `status`, position) VALUES ($userid, $lastid, 'active', 'owner')";
  $connection->exec($sql);
} catch(PDOException $error) {
  echo $sql . "<br>" . $error->getMessage();
}
$eid = $_POST['epicid'];
  header("Location: editscrum.php?id=$lastid&eid=$eid");
  die();
}

?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Scrum</title>
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
          <li class="nav-item"> <a class="nav-link" href="#">You Are At Create Scrum</a> </li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="py-5" style="background-image: url('/img/parentback.svg');background-size:cover;" >
<?php include "./view/nav.php"; ?>
        <div class="col-md-9 rounded" style="background-color:white;">
       
        <h1>Create A Scrum</h1>
    <p class="mb-3">Please fill out all of the following information.</p>
<form method="post">
  <div class="form-group">
    <label for="name">Scrum Name:</label>
    <input type="text" class="form-control" name="name" id="name" >
  </div>
  <div class="form-group">
  <label for="details">Details:</label>
    <input type="text" class="form-control" name="details" id="details" >
  </div>
  <div class="form-group">
    <label for="datebegin">Begin Date:</label>
    <input type="date" class="form-control" name="datebegin" id="datebegin" >
  </div>
  <div class="form-group">
    <label for="dateend">End Date:</label>
    <input type="date" class="form-control" name="dateend" id="dateend" >
  </div>
<input type="hidden" name="epicid" id="epicid" value="<?php echo $_GET['id']; ?>">
  <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>

</form>
<br>








<hr>
		</div>
        </div>
      </div>
    </div>
  </div>
  <div class="py-3">
    <div class="container">
      <div class="row">
        <div class="col-md-12 text-center">
          <p class="mb-0">Powered By DTEKED ?? <?php echo date("Y"); ?> DTEKED LLC. All rights reserved</p>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
