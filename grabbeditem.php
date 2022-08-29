<?php
require "sess.php";
include "config.php";

function escape($html) {
  return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
$redi = $_GET['id'];
//DELETE UPLOADED FILE
if (isset($_GET['delete'])) {
    $fileid = $_GET['delete'];
    $sql = "SELECT fileurl FROM files WHERE id = $fileid";
      $statement = $connection->prepare($sql);
      $statement->execute();
    $filename = $statement->fetch(PDO::FETCH_ASSOC);
 $file = $filename['fileurl'];
        unlink(realpath($file));
        $sql = "DELETE FROM files WHERE id = $fileid";
        $connection->exec($sql);
        $scrumid = $_GET['scrumid'];
        $epicid = $_GET['eid'];
        header("Location: viewscrum.php?id=$scrumid&eid=$epicid");
 die();
}

//UPLOAD FILE
if (isset($_POST['savefile'])) {

  try {
    if ($_FILES['myimage']['size'] != 0) {
    $folder = "./files/";
    $name = $_FILES['myimage']['name'];
      $ext = pathinfo($name, PATHINFO_EXTENSION);
      $newname = time() . "_" . $_SESSION['pid'] . "." . $ext;
    move_uploaded_file($_FILES["myimage"]["tmp_name"], "$folder" . $newname);
    $file = $folder . $newname;
    } else {$file = "";}

    $new_user = array(
      "userid" => $_SESSION['pid'],
      "backlogid" => $_POST['id'],
      "description" => $_POST['description'],
      "fileurl" => $file,
      "name" => $_POST['name'],
      "status" => "active"
    );

    $sql = sprintf(
      "INSERT INTO %s (%s) values (%s)",
      "files",
      implode(", ", array_keys($new_user)),
      ":" . implode(", :", array_keys($new_user))
    );

    $statement = $connection->prepare($sql);
    $statement->execute($new_user);
  } catch (PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }
}

// UPDATE BACKLOG COMMENT
if (isset($_POST['submit'])) {

try {
    $id = $_POST['id'];
    $comments = escape($_POST['comments']);
    $scrumid = $_POST['scrumid'];
    $epicid = $_POST['epicid'];
  $sql = "UPDATE backlog SET comments = '$comments' WHERE id = $id";
  $connection->exec($sql);
} catch(PDOException $error) {
  echo $sql . "<br>" . $error->getMessage();
}
 header("Location: viewscrum.php?id=$scrumid&eid=$epicid");
 die();
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

//Show Uploaded files for this backlog
try {
  $id = $_GET['id'];
$sql = "SELECT * FROM files WHERE backlogid = $id";
$fstatement = $connection->prepare($sql);
$fstatement->execute();
$files = $fstatement->fetchALL();
} catch(PDOException $error) {
  echo $sql . "<br>" . $error->getMessage();
}

?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>View Backlog Item</title>
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

        <h3 class="pt-2">Backlog Item: </h3>
        <a class="btn btn-primary btn-sm float-right" href="viewscrum.php?id=<?php echo $_GET['scrumid']; ?>&eid=<?php echo $result['epicid']; ?>">
  Back To Scrum
</a>
        <h4><?php echo $result['name']; ?></h4>
    <p class="mb-3"><?php echo $result['details']; ?></p>
<form method="post">
  <div class="form-group">
    <label for="Comments">Comments:</label>
    <input type="text" class="form-control" name="comments" id="comments" value="<?php echo $result['comments']; ?>">
  </div>
<input type="hidden" name="id" value="<?php echo $result['id']; ?>">
<input type="hidden" name="scrumid" value="<?php echo $result['scrumid']; ?>">
<input type="hidden" name="epicid" value="<?php echo $result['epicid']; ?>">
  <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit Comment</button>

</form>
<br>
<?php
if ($files && $fstatement->rowCount() > 0) { ?>
<hr>
<h4>Attached Files</h4>
<?php foreach ($files as $row) { ?>

  <a target=”_blank” href="<?php echo $row['fileurl']; ?>"><?php echo $row['name']; ?></a>: Submitted: <?php $t = date("F j, Y", strtotime($row['time']));
        echo $t; ?> - <?php echo $row['description']; ?> | <?php if ($row['userid'] == $_SESSION['pid']){?> <a href="grabbeditem.php?id=<?php echo $_GET['id']; ?>&scrumid=<?php echo $_GET['scrumid']; ?>&eid=<?php echo $result['epicid']; ?>&delete=<?php echo $row['id'] ?>">DELETE</a> <?php }  ?><br>
<?php } ?>
<?php } ?>
<hr>
<h1>Upload a file for this Backlog Item</h1>
<form method="post" enctype="multipart/form-data">
<div class="form-group">
<label for="name">Name:</label>
<input type="text" name="name" id="name">
</div>
<div class="form-group">
<label for="description">Description:</label>
<input type="text" name="description" id="description">
</div>
<div class="form-group">
            <label for="myimage">Upload File:</label>
            <input type="file" name="myimage" id="myimage" class="form-control">
          </div>
          <input type="hidden" name="id" value="<?php echo $result['id']; ?>">
<input type="hidden" name="scrumid" value="<?php echo $result['scrumid']; ?>">
          <button type="savefile" name="savefile" value="savefile" class="btn btn-primary">Upload File</button>
</form>
<br>

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
