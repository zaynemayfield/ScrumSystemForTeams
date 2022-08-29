<?php
session_start();

$status = $_SESSION['loggedin'];
$username = $_SESSION['username'];
$pid = $_SESSION['pid'];

if( $username == "" or $status == "" ) {
  echo "Please login";
  header('Location: signin.php');
}
