<?php
require "sess.php";
include "config.php";

$redi = $_GET['id'];

//HANDLE ADDING FINANCE ITEM
if (isset($_POST['submitfinance'])) {
  try {
$newfinance = array(
  "epicid"    => $_POST['epicid'],
  "userid"  => $_SESSION['pid'],
  "item"      => $_POST['item'],
  "qnty"   => $_POST['qnty'],
  "cost"   => $_POST['cost'],
  "location"   => $_POST['location'],
  "date"   => $_POST['date'],
  "notes"   => $_POST['notes']
);

$sql = sprintf(
    "INSERT INTO %s (%s) values (%s)",
    "finance",
    implode(", ", array_keys($newfinance)),
    ":" . implode(", :", array_keys($newfinance))
);

$statement = $connection->prepare($sql);
$statement->execute($newfinance);

  } catch (PDOException $error) {
    echo $sql . "<br />" . $error->getMessage();
    }
}

//Handle shifting positions
if (isset($_GET['direction'])) {
  $id = $_GET['blid'];
  $direction = $_GET['direction'];
  $number = $_GET['number'];
  $shiftdown = $number +1;
  $shiftup = $number -1;
  if ($direction == 'down') {
    $sql = "UPDATE backlog SET `number` = $number Where `number` = $shiftdown AND scrumid IS NULL";
    $connection->exec($sql);
    $sql = "UPDATE backlog SET `number` = $number +1 WHERE id = $id";
    $connection->exec($sql);
} else if ($direction == 'up') {
  $sql = "UPDATE backlog SET `number` = $number Where `number` = $shiftup AND scrumid IS NULL";
    $connection->exec($sql);
    $sql = "UPDATE backlog SET `number` = $number -1 WHERE id = $id";
    $connection->exec($sql);
}
  header("Location: viewepic.php?id=$redi");
  die();
}
//GET EPIC INFORMATION TO DISPLAY
try {
$epicid = $_GET['id'];
$sql = "SELECT * FROM epic WHERE id = $epicid";
$statement = $connection->prepare($sql);
$statement->execute();
$epic = $statement->fetch();
} catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
  }
//Current Scrum for this epic
try {
  $date = date("Y-m-d");
  $sql = "SELECT * FROM scrum WHERE epicid = $epicid AND datebegin <= '$date' AND dateend >= '$date'";
  $statement = $connection->prepare($sql);
  $statement->execute();
  $scrum = $statement->fetch();
  } catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
  }
//Get All previous Scrums for this epic
try {
  $date = date("Y-m-d");
  $sql = "SELECT * FROM scrum WHERE epicid = $epicid AND dateend < '$date'";
  $statement = $connection->prepare($sql);
  $statement->execute();
  $prevscrum = $statement->fetchALL();
  } catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
  }

  //HANDLE ADDING BACKLOG ITEM
if (isset($_POST['submitbacklog'])) {
  $epicid = $_GET['id'];
  try {
    $number = $connection->query("SELECT COUNT(id) FROM backlog WHERE epicid = $epicid AND `status` = 'new'")->fetchColumn();
    $number = $number +1;
  } catch(PDOException $error) {
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
    } catch(PDOException $error) {
      echo $sql . "<br>" . $error->getMessage();
    }
    }

//HANDLE DELETING FINANCE ITEM
if (isset($_GET['deletefinance'])) {
  $id = $_GET['deletefinance'];
  $sql = "DELETE FROM finance WHERE id = $id";
  $connection->exec($sql);
  header("Location: viewepic.php?id=$redi");
  die();
}

//HANDLE DELETING BACKLOG ITEM
if (isset($_GET['deleteblid'])) {
  $id = $_GET['deleteblid'];
  $sql = "DELETE FROM backlog WHERE id = $id";
  $connection->exec($sql);
  header("Location: viewepic.php?id=$redi");
  die();
}

//HANDLE APPROVING BACKLOG ITEM
if (isset($_GET['blstatid'])) {
  $id = $_GET['blstatid'];
  $sql = "UPDATE backlog SET `status` = 'new' WHERE id = $id";
  $connection->exec($sql);
  header("Location: viewepic.php?id=$redi");
  die();
}

//HANDLE ARRANGING THE ORDER OF THE ITEMS SHIFTING EVERYTHING - Drag and drop sorting

//HANDLE SETTING AMOUNT OF POINTS FOR EACH BACKLOG ITEM the first time
if (isset($_POST['submitpoints'])) {
  //Check to see if there is already an entry with count then do an if else
  $blid = $_POST['blid'];
  $userid = $_SESSION['pid'];
  $check = $connection->query("SELECT COUNT(id) FROM points WHERE backlogid = $blid AND userid = $userid")->fetchColumn();
  if ($check == 0) {
    try {
      $newpoint = array(
        "backlogid" => $_POST['blid'],
        "userid"  => $_SESSION['pid'],
        "points"    => $_POST['points']
      );

      $sql = sprintf(
          "INSERT INTO %s (%s) values (%s)",
          "points",
          implode(", ", array_keys($newpoint)),
          ":" . implode(", :", array_keys($newpoint))
      );

      $statement = $connection->prepare($sql);
      $statement->execute($newpoint);
    } catch(PDOException $error) {
      echo $sql . "<br>" . $error->getMessage();
    }

    } else {
      $points = $_POST['points'];
      $blid = $_POST['blid'];
      $userid = $_SESSION['pid'];
      $sql = "UPDATE points SET points = $points WHERE backlogid = $blid AND userid = $userid";
  $connection->exec($sql);
    }
    //Get number of votes
$blid = $_POST['blid'];
$votes = $connection->query("SELECT COUNT(id) FROM points WHERE backlogid = $blid")->fetchColumn();
//Get Total points
$total = $connection->query("SELECT SUM(points) FROM points WHERE backlogid = $blid")->fetchColumn();
//Divide for the average
$average = round($total/$votes);
//update backlog points
  $sql = "UPDATE backlog SET points = $average WHERE id = $blid";
  $connection->exec($sql);
  }


//GET BACKLOG ITEMS TO DISPLAY
try {
  $userid = $_SESSION['pid'];
  $sql = "SELECT backlog.*, points.points FROM backlog LEFT OUTER JOIN points ON(points.backlogid = backlog.id AND points.userid = $userid) WHERE epicid = $epicid AND `status` = 'new' ORDER BY `number` ASC";
  $statement = $connection->prepare($sql);
  $statement->execute();
  $backlog = $statement->fetchALL();
  } catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
  }

//GET FINANCE ITEMS TO DISPLAY
try{
  $epicidtid = $_GET['id'];
  $sql = "SELECT * FROM finance WHERE epicid = $epicid ORDER BY `date` ASC";
  $fstatement = $connection->prepare($sql);
  $fstatement->execute();
  $finance = $fstatement->fetchALL();
} catch (PDOException $error) {
  echo $sql . "<br />" . $error->getMessage();
  }

//CHECK IF USER IS OWNER AND SET VARIABLE TO CHECK FOR APPROVING, and Arranging

if ($epic['userid'] == $_SESSION['pid']){
  $owner = "true";
} else {
  $owner = "false";
}


?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>View Epic</title>
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
          <li class="nav-item"> <a class="nav-link" href="#">You Are At View Epic</a> </li>
          <li class="nav-item"> <a class="nav-link" href="index.php">SCRUM HOME</a> </li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="py-2" style="background-color: #eeeeee;" >
        <div class="col-md-12 rounded pt-2" style="background-color:white;">
<!-- SHOW epic information -->

<div class="card">
  <div class="card-body">
    <h4 class="card-title"><?php echo $epic['name']; ?> : <?php echo $epic['details']; ?> | Start: <?php echo $epic['datebegin']; ?> End: <?php echo $epic['dateend']; ?>
    <?php
    if ($owner == "true") { ?>
<a href="editepic.php?id=<?php echo $_GET['id']; ?>" class="card-link text-danger">edit</a>
  <?php
} ?></h4>

  </div>
  <!-- SHOW Current Scrum -->
  <div class="card-footer bg-transparent border-info">
  <h4>Current Scrum: <?php if (!empty($scrum['id'])){ ?><a href="viewscrum.php?id=<?php echo $scrum['id']; ?>&eid=<?php echo $scrum['epicid']; ?>"><?php echo $scrum['name']; ?></a> <?php }?>
  <?php
  if ($owner == "true"){
        if (!empty($scrum['id'])){ ?>
          <a class="text-danger" href="editscrum.php?id=<?php echo $scrum['id']; ?>&eid=<?php echo $epic['id']; ?>"> - Edit</a> -
        <?php } ?>
        <a href="createscrum.php?id=<?php echo $epic['id']; ?>" class="float-right">Create New Scrum</a>
        <?php
        }?>
        </h4>
  </div>
</div>
<br>

<div class="row">
  <div class="col">
<!-- Create a New backlog Item -->
<h4>Create New Backlog Item</h4>
<form method="post">
<div class="form-group">
    <label for="name">Backlog Item Name:</label>
    <input type="text" class="form-control" name="name" id="name" >
  </div>
  <div class="form-group">
  <label for="details">Details:</label>
    <input type="text" class="form-control" name="details" id="details" >
  </div>
  <input type="hidden" name="epicid" id="epicid" value="<?php echo $epic['id']; ?>">
  <button type="submit" name="submitbacklog" value="submitbacklog" class="btn btn-primary mb-2">Submit Backlog Item</button>

</form>
      </div>
<!-- CHECK FOR FINANCE AND HOW THE FINANCE FORM -->
      <?php if ($epic['finance'] == 'yes'){      ?>
      <div class="col">
      <h4>Input Finance Item</h4>
<form method="post">
<div class="form-inline">
    <label for="item">Item Name:</label>
    <input type="text" class="form-control ml-2" name="item" id="item" >
  </div>
  <div class="form-inline">
    <label for="qnty">Quantity:</label>
    <input type="number" class="form-control ml-2" name="qnty" id="qnty" >
  </div>
  <div class="form-inline">
  <label for="cost">Cost:</label>
    <input type="text" class="form-control ml-2" name="cost" id="cost" >
  </div>
  <div class="form-inline">
    <label for="location">Location:</label>
    <input type="text" class="form-control ml-2" name="location" id="location" >
  </div>
  <div class="form-inline">
    <label for="date">Purchase Date:</label>
    <input type="date" class="form-control ml-2" name="date" id="date" >
  </div>
  <div class="form-group">
    <label for="notes">Notes:</label>
    <input type="text" class="form-control" name="notes" id="notes" >
  </div>
  <input type="hidden" name="epicid" id="epicid" value="<?php echo $epic['id']; ?>">
  <button type="submit" name="submitfinance" value="submitfinance" class="btn btn-warning mb-2">Submit Finance Item</button>

</form>
      </div>
      <?php } ?>
      </div>
<!-- SHOW all backlog items associated with this epic NEED ORDER FUNCTION drag and drop-->
</div>
<div class="row">
  <div class="col">
<?php
if ($backlog && $statement->rowCount() > 0) {
foreach ($backlog as $row) {

?>

<div class="row bg-white shadow-sm border-0 border-white pt-2 pb-1 mt-1 mb-1 mr-2 ml-2">
<div class="col">
<?php echo $row['number']; ?>.&nbsp;<a href="editbacklog.php?id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?> </a>
<br>
      <?php echo $row['details'];
      if ($owner == "true") { ?> - <a class="text-danger" href="viewepic.php?id=<?php echo $_GET['id']; ?>&deleteblid=<?php echo $row['id']; ?>">DELETE</a> <?php
                                                                                                                                                    } ?>

     </div>
     <div class="col-4">
<form class="form-inline theForm" method="post">
<div class="form-group mb-2">
<h4><a href="viewepic.php?id=<?php echo $_GET['id']; ?>&blid=<?php echo $row['id']; ?>&direction=up&number=<?php echo $row['number']; ?>">&#x2191;</a> | <a href="viewepic.php?id=<?php echo $_GET['id']; ?>&blid=<?php echo $row['id']; ?>&direction=down&number=<?php echo $row['number']; ?>">&#x2193;</a></h4>
     &nbsp;Points: <?php echo $row['points']; ?>
  </div>
  <div class="form-group mx-sm-3 mb-2">
  <label for="points" class="sr-only">Points:</label>
  <input type="number" class="form-control" name="points" id="points" value="<?php echo $row['points']; ?>">
  </div>
  <input type="hidden" name="blid" id="blid" value="<?php echo $row['id']; ?>">
  <input type="hidden" name="userid" id="userid" value="<?php echo $_SESSION['pid']; ?>">
  <button type="submit" name="submitpoints" value="submitpoints" class="btn btn-primary mb-2">Submit Points</button>
</form>
   </div>
</div>
<?php
}}
?>
</div>
<!--SHOW ALL THE FINANCE ITEMS -->
<div class="col">
<?php
if ($finance && $fstatement->rowCount() > 0) {
foreach ($finance as $row) {

?>

<div class="row bg-white shadow-sm border-0 border-white pt-2 pb-1 mt-1 mb-1 mr-2 ml-2">
<div class="col">
<a href="editfinance.php?id=<?php echo $row['id']; ?>"><?php echo $row['item']; ?> </a>
<br>
      COST: <?php echo $row['cost']; ?> | QNTY: <?php echo $row['qnty']; ?> | LOCATION: <?php echo $row['location']; ?>
     <br>
     Notes: <?php echo $row['notes']; ?>
     </div>
     <div class="col-4">
     <?php echo $row['date']; ?> <?php if ($_SESSION['pid'] == $row['userid']) { ?> - <a class="text-danger" href="viewepic.php?id=<?php echo $_GET['id']; ?>&deletefinance=<?php echo $row['id']; ?>">DELETE</a> <?php } ?>

  </div>
  

</div>
<?php
}}
?>
</div>
</div>

<!-- SHOW PREVIOUS SCRUMS FOR THIS EPIC -->
<h5>
<ul>
<?php
if ($prevscrum && $statement->rowCount() > 0) {
foreach ($prevscrum as $row) {?>
<li>
<a href="viewscrum.php?id=<?php echo $row['id']; ?>&eid=<?php echo $row['epicid']; ?>"><?php echo $row['datebegin']; ?> - <?php echo $row['dateend']; ?>: <?php echo $row['name']; ?></a>
</li>
<?php
}?>
</ul>
</h5>
<?php
}
?>







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
<!-- include jQuery, there are bunches of other libraries with just AJAX stuff, but jQuery is a just-in-case-you-need-everything -->
<script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>
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

</html>
