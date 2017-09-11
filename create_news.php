<?php
include_once 'user_class.php';
include_once 'news_class.php';
include_once '../../secure_files/database_access.php';
session_start();


if( isset($_POST['create'])) {
	if( isset($_POST['link']) ) {
		if( isset($_POST['description']) ) {
			$link = $_POST['link'];
			$description = $_POST['description'];
			/* create a new news object */
			$new_news = News::createNews($link, $description);
			/* check if user is logged in */
			if( isset($_SESSION['user'])) {
				/* test for cross-site forgery */
				if($_SESSION['token'] !== $_POST['token']){
					print("<p>Request forgery detected</p>\n");
					print("<p><a href='newsboard.php'><button>Return to main page</button></a></p>\n");
					exit;
				}
				$user = $_SESSION['user'];
				$new_news->setUserId($user->getUserId());
				$new_news->insert($mysqli);

				if(isset($_SESSION['previous_page']))
					header('Location:' . $_SESSION['previous_page']);
				else
					header('Location: newsboard.php');
				exit;
			} else {
				print("<p><b>Please login first</b></p>\n
					   <p><a href='newsboard.php'><button>Return to main page</button></a></p>\n");
				exit;
			}
		} else {
			print("<h3>Invalid description</h3>\n
				   <p><a href='newsboard.php'><button>Return to main page</button></a></p>\n");
			exit;
		}
		
	} else {
		print("<h3>Invalid link</h3>\n
			   <p><a href='newsboard.php'><button>Return to main page</button></a></p>\n");
		exit;
	}
}
?>