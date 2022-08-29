<?php
 require "config.php";

session_start();
if (isset($_GET['student'])) {
    trackEvent("Logout", "Student Logs out of Portal");
}
if (isset($_COOKIE['token']) && isset($_COOKIE['id'])) {
    try {
        $token = $_COOKIE['token'];
        $id = $_COOKIE['id'];
        $sql = "SELECT * FROM user WHERE id = $id AND token = '$token'";
        $statement = $connection->prepare($sql);
        $statement->execute();

        $data = $statement->fetch();
    } catch (PDOException $error) {
        echo $sql . "<br />" . $error->getMessage();
    }
    if (!empty($data)) {
        if ($data['token'] == $_COOKIE['token'] && $data['id'] == $_COOKIE['id']) {
$id = $data['id'];
$token = bin2hex(openssl_random_pseudo_bytes(128));
$sql = "UPDATE user SET token = '$token' WHERE id = $id";
$connection->exec($sql);
$expire = time() - 60 * 60 * 24 * 30;
setcookie("token", $token, $expire);
setcookie("id", $id, $expire);
        }
    }
}


session_unset();   // remove all session variables
session_destroy();  // destroy the session
header('Location: index.php');   //Redirect to login page
