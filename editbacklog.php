<?php
require "sess.php";
include "config.php";

function escape($html) {
  return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
//DELETE SCRUM
if (isset($_POST['deletesubmit'])) {

    try {
        $id = $_POST['id'];
        $sql = "UPDATE backlog SET `status` = 'deleted' WHERE id = $id";
        $connection->exec($sql);
    } catch(PDOException $error) {
        echo $sql . "<br>" . $error->getMessage();
      }
      header("Location: index.php");
      die();
      }

// UPDATE SCRUM INFORMATION
if (isset($_POST['submit'])) {

try {
    $id = escape($_POST['id']);
    $name = escape($_POST['name']);
    $details = escape($_POST['details']);

  $sql = "UPDATE backlog SET `name` = '$name', details = '$details' WHERE id = $id";
  $connection->exec($sql);
} catch(PDOException $error) {
  echo $sql . "<br>" . $error->getMessage();
}
}

//POPULATE FORM WITH DATA FROM URL GET
try {
    $id = $_GET['id'];
$sql = "SELECT * FROM backlog WHERE id = $id";
$statement = $connection->prepare($sql);
$statement->execute();
$result = $statement->fetch();
} catch(PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }

?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Backlog Item</title>
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
          <li class="nav-item"> <a class="nav-link" href="#">You Are At Edit Backlog Item</a> </li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="py-5" style="background-image: url('/img/parentback.svg');background-size:cover;" >
<?php include "./view/nav.php"; ?>
        <div class="col-md-9 rounded" style="background-color:white;">

        <h1>Edit A Backlog Item</h1>
    <p class="mb-3">Please fill out all of the following information.</p>
<form method="post">
  <div class="form-group">
    <label for="name">Backlog Name:</label>
    <input type="text" class="form-control" name="name" id="name" value="<?php echo $result['name']; ?>">
  </div>
  <div class="form-group">
  <label for="details">Details:</label>
    <input type="text" class="form-control" name="details" id="details" value="<?php echo $result['details']; ?>">
  </div>
<input type="hidden" name="id" value="<?php echo $result['id']; ?>">
  <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>

</form>
<div class="float-right">
<form method="post">

<input type="hidden" name="id" value="<?php echo $result['id']; ?>">
  <button type="deletesubmit" name="deletesubmit" value="deletesubmit" class="btn btn-danger">DELETE</button>
</form>

</div>
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
          <p class="mb-0">Powered By DTEKED © <?php echo date("Y"); ?> DTEKED LLC. All rights reserved</p>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
