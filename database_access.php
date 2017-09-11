<?php
// Content of database.php
 
$mysqli = new mysqli('localhost:8889', 'root', 'root', 'NEWS_WEBSITE');
if($mysqli->connect_errno) {
	printf("Connection Failed: %s\n", $mysqli->connect_error);
	exit;
}
?>