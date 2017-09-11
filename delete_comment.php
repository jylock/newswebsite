<?php
include_once '../../secure_files/database_access.php';
include_once 'comment_class.php';

if(!isset($_SESSION))
{
	session_start();
}

if( isset($_POST['delete_comment'])) {
	/* test for cross-site forgery */
	if($_SESSION['token'] !== $_POST['token']){
		print("<p>Request forgery detected</p>\n");
		print("<p><a href='newsboard.php'><button>Return to main page</button></a></p>\n");
		exit;
	}
	$comment = Comment::retrieveCommentByCommentId($_POST["comID"], $mysqli);
	$comment->delete($mysqli);
	
	if(isset($_SESSION['previous_page']))
		header('Location:' . $_SESSION['previous_page']);
	else
		header('Location: newsboard.php');
	exit;
}
?>