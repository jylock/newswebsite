<?php 
include_once 'user_class.php';
include_once 'news_class.php';
include_once '../../secure_files/database_access.php';


session_start();

if( isset($_POST['edit'])) {
	if( isset($_POST['new_link']) ) {
		if( isset($_POST['new_description']) ) {
			/* test for cross-site forgery */
			if($_SESSION['token'] !== $_POST['token']){
				print("<p>Request forgery detected</p>\n");
				print("<p><a href='newsboard.php'><button>Return to main page</button></a></p>\n");
				exit;
			}

			$new_link = $_POST['new_link'];
			$new_description = $_POST['new_description'];

			$news = News::retrieveNewsByNewsId($_POST['newsID'], $mysqli);
			$user = $_SESSION['user'];
			$news->edit($new_link, $new_description, $mysqli);

			$news->setLink($new_link);
			$news->setDescription($new_description);
			$_SESSION['news'] = $news;

			if(isset($_SESSION['previous_page']))
				header('Location:' . $_SESSION['previous_page']);
			else
				header('Location: newsboard.php');
			exit;
		}
		else {
			print("<h3>Invalid Title</h3>\n
				   <p><a href='newsboard.php'><button>Return to main page</button></a></p>\n");
			exit;
		}
		
	} else {
		print("<h3>Invalid Link</h3>\n
			   <p><a href='newsboard.php'><button>Return to main page</button></a></p>\n");
		exit;
	}
}

 ?>