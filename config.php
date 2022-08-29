<?php
/**
 * Configuration for database connection
 *
 */
$host       = "localhost";
$user   = "root";
$pass   = "password";
$dbname     = "scrum";
$dsn        = "mysql:host=$host;dbname=$dbname";
$options    = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
              );

$connection = new PDO($dsn, $user, $pass, $options);