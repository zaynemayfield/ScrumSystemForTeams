<?php
if(isset($_POST['submit'])){
    include "config.php";
$usernamef = strtolower($_POST['usernamef']);
$password = $_POST['password'];

$sql = "SELECT *
        FROM user
        WHERE username='$usernamef'";
$statement = $connection->prepare($sql);
$statement->bindParam('$username', $usernamef);
$statement->execute();
$data = $statement->fetch(PDO::FETCH_ASSOC);
    if ($data['username'] == $usernamef){
        if ( password_verify($password, $data['password'])){
            session_start();
            // SET LOGIN TO TRUE
            $_SESSION['loggedin'] = "true";
            // SET USER USERNAME
            $_SESSION["username"] = $data['username'];
            // SET USER ID
            $_SESSION['pid'] = $data['id'];
            // SET ADMIN IF USER IS ADMIN
            //$_SESSION['admin'] = $data['admin'];

            header('Location: index.php');
        }
        else { echo " Incorrect Password ";}
    }
    else{ echo " Your username is not found ";}
} else {
    echo "no post set.";
header("refresh:1;url=signin.php");
}
