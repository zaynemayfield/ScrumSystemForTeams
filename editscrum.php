<?php
require "sess.php";
include "config.php";

$redi = "?id=" . $_GET['id'] . "&eid=" . $_GET['eid'];

//ADD PLAYER AS OWNER
if (isset($_GET['ownerid'])) {
  try {
    $id = $_GET['ownerid'];
    $sql = "UPDATE scrumplayers SET position = 'owner' WHERE id = $id";
    $connection->exec($sql);
  } catch (PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }
  header("Location: editscrum.php$redi");
  die();
}


//ADD BACKLOG TO SCRUM
if (isset($_GET['blid'])) {
  $blid = $_GET['blid'];
  $scrumid = $_GET['id'];
  $number = $_GET['number'];

  try {
    $order = $connection->query("SELECT COUNT(id) FROM backlog WHERE scrumid = $scrumid")->fetchColumn();
    $order = $order + 1;
  } catch (PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }


  try {
  $sql = "UPDATE backlog SET scrumid = $scrumid, `status` = 'scrummed', `number` = $order WHERE id = $blid";
  $connection->exec($sql);
  } catch(PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }

  try {
    $sql = "UPDATE backlog SET `number` = `number`-1 WHERE scrumid IS NULL AND `number` > $number";
    $connection->exec($sql);
  } catch (PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }
  header("Location: editscrum.php$redi");
  die();
}

//remove BACKLOG from SCRUM
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
  } catch(PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }
  header("Location: editscrum.php$redi");
  die();
}

// ADD NEW PLAYER TO SCRUM
if(isset($_POST['playersubmit'])){
  $userid = $_POST['userid'];
  $id = $_GET['id'];
  $position = "player";
  $isthere = $connection->query("SELECT COUNT(id) FROM scrumplayers WHERE scrumid = $id AND userid = $userid")->fetchColumn();
echo $isthere;
  if ($isthere < 1) {
    try {
      $sql = "INSERT INTO scrumplayers (userid, scrumid, `status`, position) VALUES ($userid, $id, 'active', '$position')";
      $connection->exec($sql);
    } catch (PDOException $error) {
      echo $sql . "<br>" . $error->getMessage();
    }
  }


}

//DELETE SCRUM
if (isset($_POST['deletesubmit'])) {

    try {
        $id = $_POST['id'];
        $sql = "UPDATE scrum SET `status` = 'deleted' WHERE id = $id";
        $connection->exec($sql);
    } catch(PDOException $error) {
        echo $sql . "<br>" . $error->getMessage();
      }
      header("Location: index.php");
      die();
      }

//REMOVE PLAYER
if (isset($_GET['removeid'])){
  $removeid = $_GET['removeid'];
  $scrumid = $_GET['id'];
  $sql = "DELETE FROM scrumplayers WHERE scrumid = $scrumid AND userid = $removeid";
  $connection->exec($sql);
  header("Location: editscrum.php$redi");
  die();
}

// UPDATE SCRUM INFORMATION
if (isset($_POST['submit'])) {

try {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $details =  $_POST['details'];
    $datebegin = $_POST['datebegin'];
    $dateend = $_POST['dateend'];

  $sql = "UPDATE scrum SET `name` = '$name', details = '$details', datebegin = '$datebegin', dateend = '$dateend' WHERE id = $id";
  $connection->exec($sql);
} catch(PDOException $error) {
  echo $sql . "<br>" . $error->getMessage();
}
}

//POPULATE FORM WITH DATA FROM URL GET
try {
    $id = $_GET['id'];
$sql = "SELECT * FROM scrum WHERE id = $id";
$statement = $connection->prepare($sql);
$statement->execute();
$result = $statement->fetch();
} catch(PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }

//POPULATE CURRENT PLAYERS
try {
$sql = "SELECT * FROM scrumplayers WHERE scrumid = $id AND `status` = 'active'";
$sstatement = $connection->prepare($sql);
$sstatement->execute();

$scrumplayers = $sstatement->fetchALL();
} catch (PDOException $error) {
echo $sql . "<br />" . $error->getMessage();
}

//POPULATE AVAILABLE PLAYERS FROM EPIC PLAYERS
try {
    $eid = $_GET['eid'];
    $sql = "SELECT * FROM epicplayers WHERE epicid = $eid AND `status` = 'active'";
    $estatement = $connection->prepare($sql);
    $estatement->execute();
    $epicplayers = $estatement->fetchALL();
    } catch (PDOException $error) {
    echo $sql . "<br />" . $error->getMessage();
    }

//GET BACKLOG TO BE ABLE TO ADD TO SCRUM
try {
  $eid = $_GET['eid'];
  $sql = "SELECT * FROM backlog WHERE epicid = $eid AND scrumid is null ORDER BY `number` ASC";
  $bstatement = $connection->prepare($sql);
  $bstatement->execute();
  $backlog = $bstatement->fetchALL();
  } catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
  }

  //GET BACKLOG Already ADDed TO SCRUM
try {
  $id = $_GET['id'];
  $sql = "SELECT * FROM backlog WHERE scrumid = $id AND `status` = 'scrummed' ORDER BY `number` ASC";
  $astatement = $connection->prepare($sql);
  $astatement->execute();
  $added = $astatement->fetchALL();
  } catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
  }
// COUNT BACKLOG POINTS
$scrumbacklogpoints = $connection->query("SELECT SUM(points) FROM backlog WHERE scrumid = $id")->fetchColumn();
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Scrum</title>
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
          <li class="nav-item"> <a class="nav-link" href="#">You Are At Edit Scrum</a> </li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="py-5" style="background-image: url('/img/parentback.svg');background-size:cover;" >
<?php include "./view/nav.php"; ?>
        <div class="col-md-9 rounded pt-3" style="background-color:white;">

        <h3>Edit A Scrum   -   <a href="viewscrum.php?id=<?php echo $_GET['id']; ?>&eid=<?php echo $_GET['eid']; ?>">Go To Scrum</a></h3>
<form method="post">
  <div class="form-inline mb-2">
    <label for="name">Scrum Name: </label>
    <input type="text" class="form-control" name="name" id="name" value="<?php echo $result['name']; ?>">
  </div>
  <div class="form-inline mb-2 mt-2">
  <label for="details">Details: </label>
    <input type="text" class="form-control" name="details" id="details" value="<?php echo $result['details']; ?>">
  </div>
  <div class="form-inline mb-2">
    <label for="datebegin">Begin Date: </label>
    <input type="date" class="form-control" name="datebegin" id="datebegin" value="<?php echo $result['datebegin']; ?>">
    <label for="dateend" class="pl-2"> End Date: </label>
    <input type="date" class="form-control" name="dateend" id="dateend" value="<?php echo $result['dateend']; ?>">
  </div>
<input type="hidden" name="id" value="<?php echo $result['id']; ?>">
  <button type="submit" name="submit" value="submit" class="btn btn-primary">Update Scrum</button>

</form>
<div class="float-right">
<form method="post">

<input type="hidden" name="id" value="<?php echo $result['id']; ?>">
  <button type="deletesubmit" name="deletesubmit" value="deletesubmit" class="btn btn-danger">Delete Scrum</button>
</form>

</div>
<br>
<hr>
<h3>Current Players</h3>
<?php
if ($scrumplayers && $sstatement->rowCount() > 0) {
foreach ($scrumplayers as $row) {
    $pid =$row['userid'];
    $sql = "SELECT * FROM user WHERE id = $pid";
    $pstatement = $connection->prepare($sql);
$pstatement->execute();
$user = $pstatement->fetch();
    ?>

<?php echo $user['fname'] ." ". $user['lname']; ?> - <a href="editscrum.php?id=<?php echo $_GET['id']; ?>&eid=<?php echo $_GET['eid']; ?>&removeid=<?php echo $pid; ?>"> REMOVE </a> -
<?php if ($row['position'] == "owner") { ?>
Is Owner <br>
<?php
} else { ?>
<a href="editscrum.php?id=<?php echo $_GET['id']; ?>&eid=<?php echo $_GET['eid']; ?>&ownerid=<?php echo $row['id']; ?>"> Make Owner</a><br>

<?php

}
}
}
?>
<hr>



<h3>Add Players to Scrum from Epic</h3>
<?php
if ($epicplayers && $estatement->rowCount() > 0) {?>
    <form id="epicplayers" method="post">

<div class="form-group">
				<label for="userid">Select Available Epic Players to Join Scrum:</label><br>
			<select name="userid" id="userid" form="epicplayers" class="selectpicker">
      <?php
foreach ($epicplayers as $row) {
    $pid =$row['userid'];
    $sql = "SELECT * FROM user WHERE id = $pid";
    $statement = $connection->prepare($sql);
$statement->execute();
$user = $statement->fetch();?>
			<option value="<?php echo $user['id']; ?>"><?php echo $user['fname'] ." ". $user['lname']; ?></option>
			<?php } ?>
			</select>
			</div><br>
<input type="hidden" name="id" value="<?php echo $result['id']; ?>">
  <button type="playersubmit" name="playersubmit" value="playersubmit" class="btn btn-primary">Add Player</button>

</form>
<?php
}
?>
<br>

<!-- LIST and be able to remove each backlog item from the scrum back to epic. -->
<h3>Backlog Items on Scrum | Total Points: <?php echo $scrumbacklogpoints; ?></h3>
<?php
if ($added && $astatement->rowCount() > 0) { ?>
  <ol>
  <?php
  foreach ($added as $row) {?>
  <li><a href="editscrum.php?id=<?php echo $_GET['id']; ?>&eid=<?php echo $_GET['eid']; ?>&blidremove=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></li>

  <?php } ?>
  </ol>
  <?php } ?>
  <hr>


<!-- LIST and be able to add each backlog item to the scrum. -->
<h3>Add Backlog Items to Scrum</h3>
<?php
if ($backlog && $bstatement->rowCount() > 0) { ?>
  <ol>
  <?php
  foreach ($backlog as $row) {?>
  <li><a href="editscrum.php?id=<?php echo $_GET['id']; ?>&eid=<?php echo $_GET['eid']; ?>&blid=<?php echo $row['id']; ?>&number=<?php echo $row['number']; ?>"><?php echo $row['name']; ?></a></li>

  <?php } ?>
  </ol>
  <?php } ?>





<hr>
    <h3><a href="viewscrum.php?id=<?php echo $_GET['id']; ?>&eid=<?php echo $_GET['eid']; ?>">Continue</a></h3>
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
