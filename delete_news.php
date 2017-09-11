<?php 
include_once 'user_class.php';
include_once 'news_class.php';
include_once 'comment_class.php';
include_once '../../secure_files/database_access.php';


session_start();
if(isset($_POST['delete_news'])) {
	if(isset($_POST['newsID']))
	{
		/* test for cross-site forgery */
		if($_SESSION['token'] !== $_POST['token']){
			print("<p>Request forgery detected</p>\n");
			print("<p><a href='newsboard.php'><button>Return to main page</button></a></p>\n");
			exit;
		}
		$news = News::retrieveNewsByNewsId($_POST['newsID'], $mysqli);
		$news->delete($mysqli);

		if(isset($_SESSION['previous_page']))
			header('Location:' . $_SESSION['previous_page']);
		else
			header('Location: newsboard.php');
		exit;
	}
	else{
		print("<h3>Can not delete</h3>\n
			   <p><a href='newsboard.php'><button>Return to main page</button></a></p>\n");
		exit;
	}
}

 ?>