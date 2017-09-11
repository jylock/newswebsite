<?php 
include_once '../../secure_files/database_access.php';
include_once 'news_class.php';
include_once 'comment_class.php';

session_start();

if( isset($_POST['edit'])) {
	if( isset($_POST['new_content']) ) {
			/* test for cross-site forgery */
			if($_SESSION['token'] !== $_POST['token']){
				print("<p>Request forgery detected</p>\n");
				exit;
			}
			$new_content = $_POST['new_content'];

			$comment = Comment::retrieveCommentByCommentId($_POST["comID"], $mysqli);
			$user = $_SESSION['user'];
			$comment->edit($new_content, $mysqli);

			if(isset($_SESSION['previous_page']))
				header('Location:' . $_SESSION['previous_page']);
			else
				header('Location: newsboard.php');
			exit;
		
	} else {
		$editcomFail = "Invalid Content.";
		exit;
	}
} else {
	print("<p>error</p>\n");
}
?>