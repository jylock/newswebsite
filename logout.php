<?php
	session_start();
	/* test for cross-site forgery */
	if($_SESSION['token'] !== $_POST['token']){
		print("<p>Request forgery detected</p>\n");
		print("<p><a href='newsboard.php'><button>Return to main page</button></a></p>\n");
		exit;
	}
	session_destroy();
	header("Location:newsboard.php");
	exit;
?>
