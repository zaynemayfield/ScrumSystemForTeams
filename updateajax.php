<?php
include "config.php";
include "common.php";
// UPDate Points
header("Content-Type: application/json");

// decode the JSON input into a PHP object
$data = json_decode(file_get_contents("php://input"));

// must be an associative array or object
$returnData = [
  "message" => "You entered $data->data"
];

// output the array, encoded as JSON
echo json_encode($returnData);

    //Check to see if there is already an entry with count then do an if else
    $blid = $data->blid;
    $userid = $data->userid;
    $points = $data->points;
    $check = $connection->query("SELECT COUNT(id) FROM points WHERE backlogid = $blid AND userid = $userid")->fetchColumn();
    if ($check == 0) {
      try {
        $newpoint = array(
          "backlogid" => $blid,
          "userid"  => $userid,
          "points"    => $points
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
        $sql = "UPDATE points SET points = $points WHERE backlogid = $blid AND userid = $userid";
    $connection->exec($sql);
      }
      //Get number of votes
  $votes = $connection->query("SELECT COUNT(id) FROM points WHERE backlogid = $blid")->fetchColumn();
  //Get Total points
  $total = $connection->query("SELECT SUM(points) FROM points WHERE backlogid = $blid")->fetchColumn();
  //Divide for the average
  $average = round($total/$votes);
  //update backlog points
    $sql = "UPDATE backlog SET points = $average WHERE id = $blid";
    $connection->exec($sql);
