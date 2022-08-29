<?php
session_start();
$status = $_SESSION['loggedin'];
$username = $_SESSION['username'];
$pid = $_SESSION['pid'];
if ($username == "" or $status == "") {
  echo "Please login";
  header('Location: login.php');
}
include "config.php";

// GET ALL EPICS ASSOCIATED WITH userid
try {
  $id = $_SESSION['pid'];
  $date = date("Y-m-d");
  $sql = "SELECT epicplayers.*, epic.id as epicid, epic.name, epic.details, epic.finance FROM epicplayers JOIN epic ON(epic.id = epicplayers.epicid) WHERE epicplayers.userid = $id AND epicplayers.status = 'active' AND epic.datebegin <= '$date' AND epic.dateend >= '$date' AND epic.status = 'active'";
  $statement = $connection->prepare($sql);
  $statement->execute();

  $epics = $statement->fetchALL();
} catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
}

// GET ALL SCRUM ASSOCIATED WITH userid
try {
  $id = $_SESSION['pid'];
  $date = date("Y-m-d");
  $sql = "SELECT scrumplayers.*, scrum.name, scrum.details, scrum.epicid FROM scrumplayers JOIN scrum ON(scrum.id = scrumplayers.scrumid) WHERE scrumplayers.userid = $id AND scrumplayers.status = 'active' AND scrum.datebegin <= '$date' AND scrum.status = 'active'";
  $sstatement = $connection->prepare($sql);
  $sstatement->execute();

  $scrums = $sstatement->fetchALL();
} catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
}

// GET ALL BACKLOG ITEMS ASSOCIATED WITH userid
try {
  $id = $_SESSION['pid'];
  $sql = "SELECT backlog.*, scrum.id as si FROM backlog JOIN scrum ON(backlog.scrumid = scrum.id) WHERE backlog.grabid = $id AND scrum.status = 'active' AND backlog.status != 'completed'";
  $blstatement = $connection->prepare($sql);
  $blstatement->execute();

  $items = $blstatement->fetchALL();
} catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
}


?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SCRUM Board</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="theme.css" type="text/css">
</head>

<body>
    <nav class="navbar navbar-expand-md navbar-light bg-light">
        <div class="container"> <button class="navbar-toggler navbar-toggler-right border-0" type="button" data-toggle="collapse" data-target="#navbar6">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbar6"> <a class="navbar-brand text-primary d-none d-md-block" href="index.php">
                    <i class="fa d-inline fa-lg fa-circle"></i> SCRUM
                </a>
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"> <a class="nav-link" href="#">Welcome
                            <?php echo $username; ?>!</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="#">You Are At Scrum Home</a> </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="py-5" style="background-image: url('/img/parentback.svg');background-size:cover;">
        <?php include "./view/nav.php"; ?>
        <div class="col-md-9 rounded" style="background-color:white;">

            <!-- SHOW ALL THE SCRUMS ASSOCIATED WITH PARENT ID -->

            <h3 class="pt-2">My Scrums</h3>
            <?php
            if ($scrums && $sstatement->rowCount() > 0) {
              foreach ($scrums as $row) { ?>

            <div class="card">
                <h5 class="card-header">
                    <?php echo $row['name']; ?>
                </h5>
                <div class="card-body">
                    <h5 class="card-title">
                        <?php echo $row['details']; ?>
                    </h5>
                    <p class="card-text"></p>

                    <a href="viewscrum.php?id=<?php echo $row['scrumid']; ?>&eid=<?php echo $row['epicid']; ?>" class="btn btn-primary">Go To Scrum</a>
                    <?php
                    if ($row['position'] == "owner") { ?>
                    <a class="btn btn-danger" href="editscrum.php?id=<?php echo $row['scrumid']; ?>&eid=<?php echo $row['epicid']; ?>">Edit</a>
                    <?php

                  } ?>
                </div>
            </div>
            <?php

          }
        } else {
          echo "You have no Scrums Yet.";
        } ?>




            <!-- SHOW ALL EPICS ASSOCIATED WITH PARENT ID -->
            <div class="float-right"><a href="createepic.php">Create New Epic</a></div>
            <h3 class="pt-2">My Epics</h3>

            <?php
            if ($epics && $statement->rowCount() > 0) {
              foreach ($epics as $row) { ?>
            <div class="card">
                <h5 class="card-header">
                    <?php echo $row['name'];
                    if ($row['finance'] == 'yes') {
                      //GET finance total
                      $epicid = $row['epicid'];
                      $financenum = $connection->query("SELECT SUM(cost) FROM finance WHERE epicid = $epicid")->fetchColumn();
                      echo " : $" . $financenum;
                    } ?>
                </h5>
                <div class="card-body">
                    <h5 class="card-title">
                        <?php echo $row['details']; ?>
                    </h5>
                    <p class="card-text"></p>

                    <a href="viewepic.php?id=<?php echo $row['epicid']; ?>" class="btn btn-primary">Go To Epic</a>
                    <?php
                    if ($row['position'] == "owner") { ?>
                    <a class="btn btn-danger" href="editepic.php?id=<?php echo $row['epicid']; ?>">Edit</a>
                    <?php

                  }

                  ?>
                </div>
            </div>


            <?php
          }
        } else {
          echo "You have no Epic Yet.";
        }  ?>


            <!-- SHOW ALL THE ITEMS ASSOCIATED WITH PARENT ID -->

            <h3 class="pt-2">My Items</h3>
            <?php
            if ($items && $blstatement->rowCount() > 0) {
              foreach ($items as $row) { ?>

            <div class="card mb-2">
                <h5 class="card-header">
                    <?php echo $row['name']; ?>
                </h5>
                <div class="card-body">
                    <h5 class="card-title">
                        <?php echo $row['details']; ?>
                    </h5>
                    <p class="card-text"></p>

                    <a href="grabbeditem.php?id=<?php echo $row['id']; ?>&scrumid=<?php echo $row['scrumid']; ?>" class="btn btn-primary">Go To Item</a>
                </div>
            </div>
            <?php
          }
        } else {
          echo "You have no Items Yet. <br><br>";
        } ?>


        </div>
    </div>
    </div>
    </div>
    </div>
    <div class="py-3">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0">Powered By DTEKED LLC ©
                        <?php echo date("Y"); ?> DTEKED LLC. All rights reserved</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
