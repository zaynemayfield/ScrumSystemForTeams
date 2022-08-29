<?php
require "sess.php";
include "config.php";


//ADD PLAYER AS OWNER
if (isset($_GET['ownerid'])) {
  try{
  $id = $_GET['ownerid'];
  $sql = "UPDATE epicplayers SET position = 'owner' WHERE id = $id";
  $connection->exec($sql);
  } catch (PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }
}


// ADD NEW PLAYER TO EPIC
if(isset($_POST['playersubmit'])){
$email = strtolower($_POST['email']);

$sql = "SELECT *
        FROM user
        WHERE email='$email'";
$statement = $connection->prepare($sql);
$statement->bindParam('$email', $email);
$statement->execute();
$data = $statement->fetch(PDO::FETCH_ASSOC);
    if ($data['email'] == $email){
$userid = $data['id'];
$id = $_POST['id'];
$position = $_POST['position'];
try{
    $sql = "INSERT INTO epicplayers (userid, epicid, `status`, position) VALUES ($userid, $id, 'active', '$position')";
    $connection->exec($sql);
  } catch(PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }
}
}

//DELETE EPIC
if (isset($_POST['deletesubmit'])) {

    try {
        $id = $_POST['id'];
        $sql = "UPDATE epic SET `status` = 'deleted' WHERE id = $id";
        $connection->exec($sql);
    } catch(PDOException $error) {
        echo $sql . "<br>" . $error->getMessage();
      }
      header("Location: index.php");
      die();
      }

// UPDATE EPIC INFORMATION
if (isset($_POST['submit'])) {

try {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $details =  $_POST['details'];
    $datebegin = $_POST['datebegin'];
    $dateend = $_POST['dateend'];

  $sql = "UPDATE epic SET `name` = '$name', details = '$details', datebegin = '$datebegin', dateend = '$dateend' WHERE id = $id";
  $connection->exec($sql);
} catch(PDOException $error) {
  echo $sql . "<br>" . $error->getMessage();
}
}

//POPULATE FORM WITH DATA FROM URL GET
try {
    $id = $_GET['id'];
$sql = "SELECT * FROM epic WHERE id = $id";
$statement = $connection->prepare($sql);
$statement->execute();
$result = $statement->fetch();
} catch(PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }

//POPULATE CURRENT PLAYERS
try {
$sql = "SELECT * FROM epicplayers WHERE epicid = $id AND `status` = 'active'";
$statement = $connection->prepare($sql);
$statement->bindParam('$id', $pid, PDO::PARAM_STR);
$statement->execute();

$epicplayers = $statement->fetchALL();
} catch (PDOException $error) {
echo $sql . "<br />" . $error->getMessage();
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Epic</title>
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
          <li class="nav-item"> <a class="nav-link" href="#">You Are At Edit Epic</a> </li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="py-5" style="background-image: url('/img/parentback.svg');background-size:cover;" >
<?php include "./view/nav.php"; ?>
        <div class="col-md-9 rounded" style="background-color:white;">

        <h1>Edit An Epic</h1>
    <p class="mb-3">Please fill out all of the following information.</p>
<form method="post">
  <div class="form-group">
    <label for="name">Epic Name:</label>
    <input type="text" class="form-control" name="name" id="name" value="<?php echo $result['name']; ?>">
  </div>
  <div class="form-group">
  <label for="details">Details:</label>
    <input type="text" class="form-control" name="details" id="details" value="<?php echo $result['details']; ?>">
  </div>
  <div class="form-group">
    <label for="datebegin">Begin Date:</label>
    <input type="date" class="form-control" name="datebegin" id="datebegin" value="<?php echo $result['datebegin']; ?>">
  </div>
  <div class="form-group">
    <label for="dateend">End Date:</label>
    <input type="date" class="form-control" name="dateend" id="dateend" value="<?php echo $result['dateend']; ?>">
  </div>
<input type="hidden" name="id" value="<?php echo $result['id']; ?>">
  <button type="submit" name="submit" value="submit" class="btn btn-primary">Update Epic</button>

</form>
<div class="float-right">
<form method="post">

<input type="hidden" name="id" value="<?php echo $result['id']; ?>">
  <button type="deletesubmit" name="deletesubmit" value="deletesubmit" class="btn btn-danger">Delete Epic</button>
</form>

</div>
<br>

<h1>Current Players</h1>
<?php
if ($epicplayers && $statement->rowCount() > 0) {
foreach ($epicplayers as $row) {
    $pid =$row['userid'];
    $sql = "SELECT * FROM user WHERE id = $pid";
    $statement = $connection->prepare($sql);
$statement->execute();
$user = $statement->fetch();
    ?>

<?php echo $user['fname'] ." ". $user['lname']; ?> - <a href="editepic.php?id=<?php echo $result['id']; ?>&pid=<?php echo $row['id']; ?>">REMOVE </a> -
<?php if ($row['position'] == "owner") { ?>
Is Owner <br>
<?php } else { ?>
<a href="editepic.php?id=<?php echo $result['id']; ?>&ownerid=<?php echo $row['id']; ?>"> Make Owner</a><br>

<?php
}}
}
?>
<hr>



<h1>Add Players to Epic</h1>
<form method="post">
  <div class="form-group">
    <label for="email">Player Email:</label>
    <input type="email" class="form-control" name="email" id="email">
  </div>
  <label>Position:</label>
  <div class="form-check-inline">
  <label class="form-check-label">
    <input type="radio" class="form-check-input" name="position" value="player" id="position" >Player
  </label>
</div>
<div class="form-check-inline">
  <label class="form-check-label">
    <input type="radio" class="form-check-input" name="position" value="master" id="position" >Scrum Master
  </label>
</div>
<input type="hidden" name="id" value="<?php echo $result['id']; ?>">
  <button type="playersubmit" name="playersubmit" value="playersubmit" class="btn btn-primary">Add Player</button>
</form>
<br>









<hr>

<h3><a href="viewepic.php?id=<?= $_GET['id']; ?>">Continue</a></h3>


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
