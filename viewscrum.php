<?php
require "sess.php";
include "config.php";
$redi = $_GET['id'] . "&eid=" . $_GET['eid'];
$scrumid = $_GET['id'];

//HANDLE ADDING BACKLOG ITEM TO EPIC
if (isset($_POST['submitbacklog'])) {
  $epicid = $_GET['eid'];
  try {
    $number = $connection->query("SELECT COUNT(id) FROM backlog WHERE epicid = $epicid AND `status` = 'new'")->fetchColumn();
    $number = $number + 1;
  } catch (PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }
  try {
    $newbacklog = array(
      "epicid"    => $_POST['epicid'],
      "userid"  => $_SESSION['pid'],
      "name"      => $_POST['name'],
      "details"   => $_POST['details'],
      "number"    => $number
    );

    $sql = sprintf(
      "INSERT INTO %s (%s) values (%s)",
      "backlog",
      implode(", ", array_keys($newbacklog)),
      ":" . implode(", :", array_keys($newbacklog))
    );

    $statement = $connection->prepare($sql);
    $statement->execute($newbacklog);
  } catch (PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }
  header("Location: viewscrum.php?id=$redi");
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
  } catch (PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }

  try {
    $sql = "UPDATE backlog SET `number` = `number`-1 WHERE scrumid IS NULL AND `number` > $number";
    $connection->exec($sql);
  } catch (PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }
  header("Location: viewscrum.php?id=$redi");
  die();
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

//HANDLE DAILY SCRUM MEETING
if (isset($_POST['dailyscrum'])) {
  try {
    $now = new DateTime();
    $current = $now->format('Y-m-d');
    $notes = nl2br($_POST['notes']);
    $new_meeting = array(
      "userid" => $_SESSION['pid'],
      "scrumid" => $redi,
      "notes" => $notes,
      "completedpoints" => $_POST['completedpoints'],
      "avgpoints" => $_POST['avgpoints'],
      "grabpoints" => $_POST['grabpoints'],
      "date"            => $current
    );

    $sql = sprintf(
      "INSERT INTO %s (%s) values (%s)",
      "dailyscrum",
      implode(", ", array_keys($new_meeting)),
      ":" . implode(", :", array_keys($new_meeting))
    );

    $statement = $connection->prepare($sql);
    $statement->execute($new_meeting);
  } catch (PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
  }
}

//CHECK IF NEED MEETING
try {
  $sql = "SELECT `date` FROM dailyscrum WHERE scrumid = $scrumid ORDER BY `date` DESC LIMIT 1";
  $statement = $connection->prepare($sql);
  $statement->execute();
  $time = $statement->fetch();
  if (!empty($time)) {
  $then = $time['date'];
  $now = new DateTime();
  $current = $now->format('Y-m-d');
  if (empty($then)) {
    $meeting = "true";
  } else {
    if ($current == $then) {
      $meeting = "false";
    } else {
      $meeting = "true";
    }
  }
  }
  
} catch (PDOException $error) {
  echo $sql . "<br>" . $error->getMessage();
}


// HANDLE ENDING THE SCRUM
if (isset($_GET['endscrum'])) {
  // Change Status to Ended
  //Save all working and not started backlog to unfinishedbacklogids comma seperated
  //Push any Scrum backlog items and working items back to the epic and set grabid to Null and change status to new - Not sure how this affects numbering.
  //Redirect them to viewprevscrum.php to see any data and information


  $grab = $_GET['back'];
  $sql = "UPDATE backlog SET `status` = 'scrummed', grabid = NULL WHERE id = $grab";
  $connection->exec($sql);
  header("Location: viewscrum.php?id=$redi");
  die();
}

// HANDLE PRIORITY
if (isset($_GET['priority'])) {
  $grab = $_GET['priority'];
  echo $grab;
  $sql = "UPDATE backlog SET priority = 'yes' WHERE id = $grab";
  $connection->exec($sql);
  header("Location: viewscrum.php?id=$redi");
  die();
}

// HANDLE NOT PRIORITY
if (isset($_GET['notpriority'])) {
  $grab = $_GET['notpriority'];
  echo $grab;
  $sql = "UPDATE backlog SET priority = 'no' WHERE id = $grab";
  $connection->exec($sql);
  header("Location: viewscrum.php?id=$redi");
  die();
}

//HANDLE REVIEW
if (isset($_GET['review'])) {
  $grab = $_GET['review'];
  $sql = "UPDATE backlog SET `status` = 'review' WHERE id = $grab";
  $connection->exec($sql);
  header("Location: viewscrum.php?id=$redi");
  die();
}

//HANDLE REVIEW TO GRABBED
if (isset($_GET['reviewgrab'])) {
  $grab = $_GET['reviewgrab'];
  $sql = "UPDATE backlog SET `status` = 'grabbed' WHERE id = $grab";
  $connection->exec($sql);
  header("Location: viewscrum.php?id=$redi");
  die();
}


// HANDLE GRABBING
if (isset($_GET['grab'])) {
  $grab = $_GET['grab'];
  $grabid = $_SESSION['pid'];
  $sql = "UPDATE backlog SET `status` = 'grabbed', grabid = $grabid, grabtime = CURRENT_TIMESTAMP WHERE id = $grab";
  $connection->exec($sql);
  header("Location: viewscrum.php?id=$redi");
  die();
}
// HANDLE GOING BACK TO BACKLOG
if (isset($_GET['back'])) {
  $grab = $_GET['back'];
  $sql = "UPDATE backlog SET `status` = 'scrummed', grabid = NULL, grabtime = NULL WHERE id = $grab";
  $connection->exec($sql);
  header("Location: viewscrum.php?id=$redi");
  die();
}
//HANDLE COMPLETED
if (isset($_GET['complete'])) {
  $complete = $_GET['complete'];
  $sql = "UPDATE backlog SET `status` = 'completed', completetime = CURRENT_TIMESTAMP WHERE id = $complete";
  $connection->exec($sql);
  header("Location: viewscrum.php?id=$redi");
  die();
}
//HANDLE GOING BACK TO GRABBED
if (isset($_GET['center'])) {
  $center = $_GET['center'];
  $sql = "UPDATE backlog SET `status` = 'grabbed', completetime = NULL WHERE id = $center";
  $connection->exec($sql);
  header("Location: viewscrum.php?id=$redi");
  die();
}

// GET ALL SCRUMMED ITEMS ASSOCIATED WITH THIS SCRUM
try {
  $scrumid = $_GET['id'];
  $sql = "SELECT * FROM backlog WHERE scrumid = $scrumid AND `status` = 'scrummed' ORDER BY `number` ASC";
  $statement = $connection->prepare($sql);
  $statement->execute();
  $scrummed = $statement->fetchALL();
} catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
}
// GET ALL GRABBED ITEMS ASSOCIATED WITH THIS SCRUM
try {
  $scrumid = $_GET['id'];
  $sql = "SELECT * FROM backlog WHERE scrumid = $scrumid AND `status` = 'grabbed' ORDER BY `number` ASC";
  $gstatement = $connection->prepare($sql);
  $gstatement->execute();
  $grabbed = $gstatement->fetchALL();
} catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
}
// GET ALL REVIEW ITEMS ASSOCIATED WITH THIS SCRUM
try {
  $scrumid = $_GET['id'];
  $sql = "SELECT * FROM backlog WHERE scrumid = $scrumid AND `status` = 'review' ORDER BY `number` ASC";
  $rstatement = $connection->prepare($sql);
  $rstatement->execute();
  $review = $rstatement->fetchALL();
} catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
}
// GET ALL COMPLETED ITEMS ASSOCIATED WITH THIS SCRUM
try {
  $scrumid = $_GET['id'];
  $sql = "SELECT * FROM backlog WHERE scrumid = $scrumid AND `status` = 'completed' ORDER BY grabid DESC";
  $cstatement = $connection->prepare($sql);
  $cstatement->execute();
  $completed = $cstatement->fetchALL();
} catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
}
//GET SCRUMMED
$scrumnum = $connection->query("SELECT SUM(points) FROM backlog WHERE scrumid = $scrumid AND `status` = 'scrummed'")->fetchColumn();
//GET GRABBED
$grabnum = $connection->query("SELECT SUM(points) FROM backlog WHERE scrumid = $scrumid AND `status` = 'grabbed'")->fetchColumn();
//GET REVIEW
$reviewnum = $connection->query("SELECT SUM(points) FROM backlog WHERE scrumid = $scrumid AND `status` = 'review'")->fetchColumn();
//GET COMPLETED
$completenum = $connection->query("SELECT SUM(points) FROM backlog WHERE scrumid = $scrumid AND `status` = 'completed'")->fetchColumn();
//Total Points
$totalpoints = $connection->query("SELECT SUM(points) FROM backlog WHERE scrumid = $scrumid")->fetchColumn();

//POPULATE CURRENT PLAYERS
try {
  $id = $_GET['id'];
  $sql = "SELECT * FROM scrumplayers WHERE scrumid = $id AND `status` = 'active'";
  $sstatement = $connection->prepare($sql);
  $sstatement->execute();

  $scrumplayers = $sstatement->fetchALL();
} catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
}

//GET SCRUM TIME DATA
try {
  $id = $_GET['id'];
  $sql = "SELECT * FROM scrum WHERE id = $id";
  $tstatement = $connection->prepare($sql);
  $tstatement->execute();

  $time = $tstatement->fetch();
} catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
}
$now = strtotime(date("Y/m/d"));
$future = strtotime($time['dateend']);
$datediff = $now - $future;
$timeleft = abs(round($datediff / (60 * 60 * 24)));

function number_of_working_days($startdate, $enddate)
{
  $workingdays = 0;
  $starttimestamp = strtotime($startdate);
  $endtimestamp = strtotime($enddate);
  for ($i = $starttimestamp; $i <= $endtimestamp; $i = $i + (60 * 60 * 24)) {
    if (date("N", $i) <= 5) $workingdays = $workingdays + 1;
  }
  return $workingdays;
}
$startdate = $time['datebegin'];
$enddate = $time['dateend'];

$workingdays = number_of_working_days($startdate, $enddate);
$id = $_GET['id'];
$numplayers = $connection->query("SELECT COUNT(id) FROM scrumplayers WHERE scrumid = $id")->fetchColumn();

$avgpoints = $completenum / $workingdays / $numplayers;


//SHOW DAILY SCRUM MEETING NOTES
try {
  $id = $_GET['id'];
  $sql = "SELECT * FROM dailyscrum WHERE scrumid = $id";
  $dsstatement = $connection->prepare($sql);
  $dsstatement->execute();

  $dailyscrum = $dsstatement->fetchALL();
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
  <title>View Scrum</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css">
  <link rel="stylesheet" href="theme.css" type="text/css">
</head>

<body>
  <nav class="navbar navbar-expand-md navbar-light bg-light border-bottom border-dark">
    <div class="container"> <button class="navbar-toggler navbar-toggler-right border-0" type="button" data-toggle="collapse" data-target="#navbar6">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbar6"> <a class="navbar-brand text-primary d-none d-md-block" href="/index.php">
          <i class="fa d-inline fa-lg fa-circle"></i>
          <b> SCRUM</b>
        </a>
        <ul class="navbar-nav mx-auto">
          <li class="nav-item"> <a class="nav-link" href="#">Welcome <?php echo $username; ?>!</a> </li>
          <li class="nav-item"> <a class="nav-link" href="#">You Are At View Scrum</a> </li>
          <li class="nav-item"> <a class="nav-link" href="index.php">SCRUM HOME</a> </li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="py-2" style="background-color:#eeeeee;">
    <div class="row">
      <div class="col rounded pt-2 ml-4 mr-4 text-black">
        <h4>
          Time Left: <?php echo $timeleft; ?> Days Until Scrum is Finished (<?php echo $time['dateend']; ?>) | Total Weekdays: <?php echo $workingdays; ?> | Total Points: <?php echo $totalpoints; ?> | Avg Points Per Day Per Person: <?php echo $avgpoints; ?>
          <a class="btn btn-primary btn-sm float-right" href="viewepic.php?id=<?php echo $time['epicid']; ?>">
            View Epic
          </a>
          <a class="btn btn-warning btn-sm float-right mr-2" href="endscrum.php?id=<?php echo $_GET['id']; ?>">
            END SCRUM
          </a>
        </h4>
      </div>
    </div>


    <div class="row">
      <div class="col-md rounded pt-2 mt-2 mr-3 ml-3">
        <!-- SHOW BACKLOG ITEMS THAT NEED REVIEW -->

        <?php
        if ($review && $rstatement->rowCount() > 0) { ?>
          <h5>
            <strong> > Review Items (<?php echo $reviewnum; ?>)</strong>
          </h5>
          <?php
          foreach ($review as $row) {
            $pid = $row['grabid'];
            $sql = "SELECT * FROM user WHERE id = $pid";
            $pstatement = $connection->prepare($sql);
            $pstatement->execute();
            $user = $pstatement->fetch();
            ?>
            <div class="row bg-white shadow-sm border-0 border-white mb-1">
              <div class="col-9">
                <strong <?php
                        if ($user['color'] == "#000000") { } else { ?> style="color:<?php echo $user['color']; ?>" <?php
                                                                                                                          } ?>>> (<?php echo $user['fname'] . " " . $user['lname'] . " " . $user['id']; ?> )</strong>
                <strong <?php if ($row['priority'] == "yes") { ?> style="color:red" <?php

                                                                                  } ?>> <?php echo $row['name']; ?> : <?php echo $row['details']; ?></strong>
                <?php if (empty($row['comments'])) { } else { ?>
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
              } catch (PDOException $error) {
                echo $sql . "<br>" . $error->getMessage();
              }
              if ($files && $fstatement->rowCount() > 0) { ?>
                  <br> <span class="pl-3 text-dark">Files:
                    <?php foreach ($files as $file) {
                      if (empty($file['fileurl'])) { } else { ?>
                        <strong> - <a href="<?php echo $file['fileurl']; ?>"> <?php echo $file['name']; ?> </a></strong></span>
                    <?php

                  }
                }
              } ?>
              </div>
              <div class="col col-3 text-right">
                <?php echo $row['points']; ?> |
                <a href="grabbeditem.php?id=<?php echo $row['id']; ?>&scrumid=<?php echo $_GET['id']; ?>" class="text-primary"><i class="fa fa-upload" aria-hidden="true"></i> Comment <i class="fa fa-comment-o" aria-hidden="true"></i> |</a>
                <?php
                if ($_SESSION['pid'] == $row['grabid']) { ?>
                  <a href="viewscrum.php?id=<?php echo $_GET['id']; ?>&reviewgrab=<?php echo $row['id']; ?>&eid=<?php echo $row['epicid']; ?>" class="text-warning">Working &#x2193;&#x2193;</a>
                  <a href="viewscrum.php?id=<?php echo $_GET['id']; ?>&complete=<?php echo $row['id']; ?>&eid=<?php echo $row['epicid']; ?>" class="text-success">Complete &#x2193;&#x2193;&#x2193;</a>
                <?php

              } ?>
              </div>
            </div>


          <?php

        }
      } ?>
      </div>
    </div>




    <div class="row">
      <div class="col rounded pt-2 mt-2 mr-3 ml-3">

        <!-- SHOW BACKLOG ITEMS THAT HAVE NOT BEEN GRABBED OR COMPLETED -->
        <h5>
          <strong> > Backlog Items (<?php echo $scrumnum; ?>)</strong>
        </h5>
        <?php
        if ($scrummed && $statement->rowCount() > 0) {
          foreach ($scrummed as $row) { ?>

            <div class="row bg-white shadow-sm border-0 border-white mb-1">
              <div class="col">
                <strong <?php if ($row['priority'] == "yes") { ?> style="color:red" <?php
                                                                                  } ?>><?php echo $row['number']; ?>. <?php echo $row['name']; ?> : <?php echo $row['details']; ?></strong>
                <?php if (empty($row['comments'])) { } else { ?>
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
              } catch (PDOException $error) {
                echo $sql . "<br>" . $error->getMessage();
              }
              if ($files && $fstatement->rowCount() > 0) { ?>
                  <br> <span class="pl-3 text-dark">Files:
                    <?php foreach ($files as $file) {
                      if (empty($file['fileurl'])) { } else { ?>
                        <strong> - <a href="<?php echo $file['fileurl']; ?>"> <?php echo $file['name']; ?> </a></strong></span>
                    <?php

                  }
                }
              } ?>
              </div>
              <div class="col col-3 text-right">
                <a href="editpoints.php?id=<?php echo $row['id']; ?>"><?php echo $row['points']; ?></a> |
                <a href="grabbeditem.php?id=<?php echo $row['id']; ?>&scrumid=<?php echo $_GET['id']; ?>" class="text-primary"><i class="fa fa-upload" aria-hidden="true"></i> Comment <i class="fa fa-comment-o" aria-hidden="true"></i> |</a>
                <?php if ($row['priority'] == "no") { ?>
                  <a href="viewscrum.php?id=<?php echo $_GET['id']; ?>&priority=<?php echo $row['id']; ?>&eid=<?php echo $row['epicid']; ?>" class="text-danger"><i class="fa fa-exclamation" aria-hidden="true"></i> |</a>
                <?php } else { ?>
                  <a href="viewscrum.php?id=<?php echo $_GET['id']; ?>&notpriority=<?php echo $row['id']; ?>&eid=<?php echo $row['epicid']; ?>" class="text-danger"><i class="fa fa-exclamation" style="color:green" aria-hidden="true"></i></a> |
                <?php } ?>

                <a href="viewscrum.php?id=<?php echo $_GET['id']; ?>&grab=<?php echo $row['id']; ?>&eid=<?php echo $row['epicid']; ?>" class="text-success">Grab &#x2193;</a></div>
            </div>


          <?php
        }
      } ?>

      </div>
    </div>





    <div class="row">
      <div class="col-md rounded pt-2 mt-2 mr-3 ml-3">
        <!-- SHOW BACKLOG ITEMS THAT HAVE BEEN GRABBED -->
        <h5>
          <strong> > Working Items (<?php echo $grabnum; ?>)</strong>
        </h5>
        <?php
        if ($grabbed && $gstatement->rowCount() > 0) {
          foreach ($grabbed as $row) {
            $pid = $row['grabid'];
            $sql = "SELECT * FROM user WHERE id = $pid";
            $pstatement = $connection->prepare($sql);
            $pstatement->execute();
            $user = $pstatement->fetch();
            ?>
            <div class="row bg-white shadow-sm border-0 border-white mb-1">
              <div class="col-9">
                <strong <?php
                        if ($user['color'] == "#000000") { } else { ?> style="color:<?php echo $user['color']; ?>" <?php }
                                                                                                                          ?>>> (<?php echo $user['fname'] . " " . $user['lname'] . " " . $user['id']; ?> )</strong>
                <strong <?php if ($row['priority'] == "yes") { ?> style="color:red" <?php

                                                                                  } ?>> <?php echo $row['name']; ?> : <?php echo $row['details']; ?></strong>
                <?php if (empty($row['comments'])) { } else { ?>
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
              } catch (PDOException $error) {
                echo $sql . "<br>" . $error->getMessage();
              }
              if ($files && $fstatement->rowCount() > 0) { ?>
                  <br> <span class="pl-3 text-dark">Files:
                    <?php foreach ($files as $file) {
                      if (empty($file['fileurl'])) { } else { ?>
                        <strong> - <a href="<?php echo $file['fileurl']; ?>"> <?php echo $file['name']; ?> </a></strong></span>
                    <?php

                  }
                }
              } ?>
              </div>
              <div class="col col-3 text-right">
                <?php echo $row['points']; ?> |
                <a href="grabbeditem.php?id=<?php echo $row['id']; ?>&scrumid=<?php echo $_GET['id']; ?>" class="text-primary"><i class="fa fa-upload" aria-hidden="true"></i> Comment <i class="fa fa-comment-o" aria-hidden="true"></i> |</a>
                <a href="viewscrum.php?id=<?php echo $_GET['id']; ?>&review=<?php echo $row['id']; ?>&eid=<?php echo $row['epicid']; ?>" class="text-info"><i class="fa fa-eye" aria-hidden="true"></i> |</a>
                <?php
                if ($_SESSION['pid'] == $row['grabid']) { ?>
                  <a href="viewscrum.php?id=<?php echo $_GET['id']; ?>&back=<?php echo $row['id']; ?>&eid=<?php echo $row['epicid']; ?>" class="text-danger"> Backlog &#x2191;</a>
                  <a href="viewscrum.php?id=<?php echo $_GET['id']; ?>&complete=<?php echo $row['id']; ?>&eid=<?php echo $row['epicid']; ?>" class="text-success">Complete &#x2193;</a>
                <?php } ?>
              </div>
            </div>


          <?php
        }
      } ?>
      </div>
    </div>








    <div class="row">
      <div class="col-md rounded pt-2 mt-2 ml-3 mr-3">
        <!-- SHOW BACKLOG ITEMS THAT HAVE BEEN COMPLETED -->
        <details>
          <summary>
            <h5 style="display:inline">
              <strong> > Completed Items (<?php echo $completenum; ?>)</strong>
            </h5>
          </summary>
          <?php
          if ($completed && $cstatement->rowCount() > 0) {
            foreach ($completed as $row) {
              $pid = $row['grabid'];
              $sql = "SELECT * FROM user WHERE id = $pid";
              $pstatement = $connection->prepare($sql);
              $pstatement->execute();
              $user = $pstatement->fetch();
              ?>

              <div class="row bg-white shadow-sm border-0 border-white mb-1">
                <div class="col">
                  <strong <?php
                          if ($user['color'] == "#000000") { } else { ?> style="color:<?php echo $user['color']; ?>" <?php }
                                                                                                                            ?>>(<?php echo $user['fname'] . " " . $user['lname']; ?> )</strong> <strong><?php echo $row['name']; ?> : <?php echo $row['details']; ?></strong>
                  <?php if (empty($row['comments'])) { } else { ?>
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
                } catch (PDOException $error) {
                  echo $sql . "<br>" . $error->getMessage();
                }
                if ($files && $fstatement->rowCount() > 0) { ?>
                    <br> <span class="pl-3 text-dark">Files:
                      <?php foreach ($files as $file) {
                        if (empty($file['fileurl'])) { } else { ?>
                          <strong> - <a href="<?php echo $file['fileurl']; ?>"> <?php echo $file['name']; ?> </a></strong></span>
                      <?php

                    }
                  }
                } ?>
                </div>
                <div class="col col-3 text-right">
                  <?php echo $row['points']; ?> |
                  <a href="grabbeditem.php?id=<?php echo $row['id']; ?>&scrumid=<?php echo $_GET['id']; ?>" class="text-primary"><i class="fa fa-upload" aria-hidden="true"></i> Comment <i class="fa fa-comment-o" aria-hidden="true"></i> |</a>
                  <?php
                  if ($_SESSION['pid'] == $row['grabid']) { ?>
                    <a href="viewscrum.php?id=<?php echo $_GET['id']; ?>&back=<?php echo $row['id']; ?>&eid=<?php echo $row['epicid']; ?>" class="text-danger"> Backlog &#x2191;&#x2191;</a>
                    <a href="viewscrum.php?id=<?php echo $_GET['id']; ?>&center=<?php echo $row['id']; ?>&eid=<?php echo $row['epicid']; ?>" class="text-warning">Working &#x2191;</a>
                  <?php } ?>
                </div>
              </div>

            <?php
          }
        } ?>
        </details>
      </div>
    </div>

    <!-- Show all of the backlog items and allow them to be added to the scrum. -->
    <div class="row mt-3">
      <div class="col-md rounded pt-2 ml-3 mr-3 bg-light">
        <!-- LIST and be able to add each backlog item to the scrum. -->
        <details class="p-2">
          <summary class="m-1">
            <h3 style="display:inline">Add Backlog Items to Scrum (<?php echo $bstatement->rowCount() ?>)</h3>
          </summary>

          <div style="column-count: 2" class="columns">
            <?php
            if ($backlog && $bstatement->rowCount() > 0) { ?>
              <ol>
                <?php
                foreach ($backlog as $row) { ?>
                  <li style="break-inside:avoid;"><a href="viewscrum.php?id=<?php echo $_GET['id']; ?>&eid=<?php echo $_GET['eid']; ?>&blid=<?php echo $row['id']; ?>&number=<?php echo $row['number']; ?>"><?php echo $row['name']; ?> - Add to Scrum</a>
                    <form class="form-inline theForm" method="post">
                      <div class="form-group mx-sm-3 mb-2">
                        <label for="points" class="sr-only">Points:</label>
                        <input type="number" class="form-control" name="points" id="points" value="<?php echo $row['points']; ?>">
                      </div>
                      <input type="hidden" name="blid" id="blid" value="<?php echo $row['id']; ?>">
                      <input type="hidden" name="userid" id="userid" value="<?php echo $_SESSION['pid']; ?>">
                      <button type="submit" name="submitpoints" value="submitpoints" class="btn btn-primary mb-2">Update Points</button>
                    </form>

                  </li>

                <?php } ?>
              </ol>
            <?php } ?>
          </div>

          <hr>
        </details>
      </div>
    </div>



    <!-- Allow user to add a backlog item to the epic. -->
    <div class="row mt-3">
      <div class="col-md rounded pt-2 ml-3 mr-3 bg-light">
        <h4>Create New Backlog Item</h4>
        <form method="post">
          <div class="form-group">
            <label for="name">Backlog Item Name:</label>
            <input type="text" class="form-control" name="name" id="name">
          </div>
          <div class="form-group">
            <label for="details">Details:</label>
            <input type="text" class="form-control" name="details" id="details">
          </div>
          <input type="hidden" name="epicid" id="epicid" value="<?php echo $_GET['eid']; ?>">
          <button type="submit" name="submitbacklog" value="submitbacklog" class="btn btn-primary mb-2">Submit Backlog Item</button>

        </form>
        <hr>
      </div>
    </div>




    <!-- CHECK FOR MEETING -->
    <?php if (isset($meeting)) {
    if ($meeting == "true") { ?>


      <div class="row mt-3">
        <div class="col-md rounded pt-2 ml-3 mr-3 bg-light">
          <h5>
            <strong>> Scrum Meeting</strong></h5>
          <h6>Meeting for: <?php echo $current; ?></h6>
          <form method="post">
            <div class="form-group">
              <label for="notes">Notes that need to be remembered:</label>
              <textarea class="form-control" name="notes" id="notes" cols="30" rows="10"></textarea>
              <input type="hidden" name="avgpoints" value="<?php echo round($avgpoints); ?>">
              <input type="hidden" name="completedpoints" value="<?php echo $completenum; ?>">
              <input type="hidden" name="grabpoints" value="<?php echo $grabnum; ?>">
              <button type="submit" name="dailyscrum" value="dailyscrum" class="btn btn-primary">Submit</button>
            </div>
          </form>
        </div>
      </div>
    <?php } } ?>

    <!-- Show MEETING DATA -->

    <div class="row">
      <div class="col-md rounded pt-2 mt-2 ml-3 mr-3">
        <h5>
          <strong>> Meeting Notes</strong></h5>
        <?php
        if ($dailyscrum && $dsstatement->rowCount() > 0) {
          foreach ($dailyscrum as $row) {
            ?>

            <div class="row bg-white shadow-sm border-0 border-white mb-1">
              <div class="col-3">
                <strong>Scrum Meeting Notes: <?php echo nl2br($row['date']); ?></strong>
              </div>
              <div class="col col-9 text-right">
                Points Completed:<?php echo $row['completedpoints']; ?> | Points Average: <?php echo $row['avgpoints']; ?> | Points Workings: <?php echo $row['grabpoints']; ?>
              </div>
              <div class="col-12">
                Notes: <?php echo $row['notes']; ?>
              </div>
            </div>

          <?php }
      } ?>
      </div>
    </div>

    <!-- SHOW SCRUM DATA HERE -->
    <div class="row">
      <div class="col-md rounded pt-2 mt-2 ml-3 mr-3">
        <h5>
          <strong>> Scrum Data</strong></h5>
        <?php
        if ($scrumplayers && $sstatement->rowCount() > 0) {
          foreach ($scrumplayers as $row) {
            $pid = $row['userid'];
            $sql = "SELECT * FROM user WHERE id = $pid";
            $pstatement = $connection->prepare($sql);
            $pstatement->execute();
            $user = $pstatement->fetch();
            $scrumid = $_GET['id'];
            $grabpoints = $connection->query("SELECT SUM(points) FROM backlog WHERE scrumid = $scrumid AND `status` = 'grabbed' AND grabid = $pid")->fetchColumn();
            //GET COMPLETED
            $completepoints = $connection->query("SELECT SUM(points) FROM backlog WHERE scrumid = $scrumid AND `status` = 'completed' AND grabid = $pid")->fetchColumn();
            ?>

            <div class="row bg-white shadow-sm border-0 border-white mb-1">
              <div class="col-3">
                <strong><?php echo $user['fname'] . " " . $user['lname']; ?></strong>
              </div>
              <div class="col col-9 text-right">
                Stats: Working: <?php echo $grabpoints; ?> | Completed: <?php echo $completepoints; ?> | Total Average Per Day: <?php echo $completepoints / $workingdays; ?>

              </div>
            </div>
          <?php }
      } ?>

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


  <!-- include jQuery, there are bunches of other libraries with just AJAX stuff, but jQuery is a just-in-case-you-need-everything -->
  <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
  <!-- script to catch form submit and handle form data -->
  <script>
    let forms = Array.from(document.querySelectorAll('.theForm')) // CSS-style class selector
    // 'forms' is sort of like an array, but we need to convert to array to use 'forEach'
    let formsArray = Array.from(forms)
    // form will be a jQuery collection, form[0] will be the actual form element
    formsArray.forEach(form => {
      form.addEventListener('submit', (event) => {
        // don't actually submit, that'll navigate to a new page
        event.preventDefault()
        // get all the fields from the form
        let data = new FormData(form)
        // create an object to store the form fields and values (to convert to JSON later)
        let dataObject = {}
        // loop through the fields in the form and assign their data to the object
        data.forEach((value, key) => {
          dataObject[key] = value
        })
        // convert to JSON
        let dataJSON = JSON.stringify(dataObject)
        // send the JSON to handle.php
        // 'jQuery' is the same as '$'
        $.post("updateajax.php", dataJSON, (result) => {
          // result should be an object created from the JSON returned from handle.php
          console.log(result.message)
          // at this point you would usually do something like create a new element
          // and populate it with the returned data
          // for example:
          let newMessage = $(`<div>${result.message}</div>`)
          newMessage.insertAfter(form)
        })
      })
    })
  </script>
</body>

</html>

<?php

try {
  $id = $_GET['id'];
  $avgpoints = round($avgpoints);
  if (empty($completenum)) {
    $completenum = 0;
  }
  $sql = "UPDATE scrum SET completedpoints = $completenum, totalpoints = $totalpoints, weekdays = $workingdays, pointsperperson = $avgpoints WHERE id = $id";
  $connection->exec($sql);
} catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
}

?>
