<?php 
include_once 'user_class.php';
include_once 'news_class.php';
include_once 'comment_class.php';
include_once '../../secure_files/database_access.php';

if(!isset($_SESSION)){
	session_start();	
}

if( isset($_POST['create_comment'])) {
	if( isset($_POST['content'])) {
		/* test for cross-site forgery */
		if($_SESSION['token'] !== $_POST['token']){
			print("<p>Request forgery detected</p>\n");
			print("<p><a href='newsboard.php'><button>Return to main page</button></a></p>\n");
			exit;
		}
		$content = $_POST['content'];
		$news = News::retrieveNewsByNewsId($_POST['newsID'], $mysqli);
		$user = $_SESSION['user'];

		$comment = Comment::createComment($news->getNewsId(), $user->getUserId(), $content);
		$comment->insert($mysqli);

		if(isset($_SESSION['previous_page']))
			header('Location:' . $_SESSION['previous_page']);
		else
			header('Location: newsboard.php');
		exit;
	} else {
		print("<h3>Invalid New Comment</h3>\n
			   <p><a href='newsboard.php'><button>Return to main page</button></a></p>\n");
		exit;
	}
}
 ?>