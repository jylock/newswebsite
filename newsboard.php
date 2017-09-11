<?php
include_once '../../secure_files/database_access.php';
include_once 'news_class.php';
include_once 'comment_class.php';

if(!isset($_SESSION))
{
	session_start();
}
if(isset($_SESSION["user"]))
{
	$login = 1;
	$username = $_SESSION['user']->getUserName();
	$userID   = $_SESSION['user']->getUserId();

}
else
{
	$login = 0;
	// just to test
	$username = "Anonymous";
	$userID   = -1;

}

$_SESSION['previous_page']='newsboard.php';
?>

<!DOCTYPE html>
<html>
<head>
	<title>News Today</title>
	<meta charset="utf-8">
  	<meta name="viewport" content="width=device-width, initial-scale=1">
  	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
  	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <link href="news.css" rel="stylesheet">


</head>

<body>
	<div class="banner">
		
		<div class="container-fluid">
			<br><div class="center"><h1 id="header">NEWS DISTRIBUTION</h1></div>    
			<br>
		</div> 
		<div class="rightpad1">
			<?php
			if($login)
			{
				printf( "<div>Welcome, %s.</div>
					<form action=\"logout.php\" method=\"POST\">\n	
						<input type=\"hidden\" name=\"token\" value=%s>	
						<input class=\"btn btn-primary\" type=\"submit\" value=\"Log Out\" />
					</form>", $username, $_SESSION['token']);
			}
			else
			{
				echo "<form action=\"userlogin.php\" method=\"POST\">\n
						<input class=\"btn btn-primary\" type=\"submit\" value=\"Log In\"/>
						</form>
						<form action=\"usersignup.php\" method=\"POST\">\n
						<input class=\"btn btn-primary\" type=\"submit\" value=\"Sign Up\" /> 
						</form>";
			}
			?>
		</div>
	</div>
	
	<br><br><br><br>
	
	
		<?php
		if($login) {

			printf("
	<div class=\"newStory\">
		<div class=\"formtext\"><br>Post a new story</div>
		<form action=\"create_news.php\" method=\"POST\" class=\"form-horizontal\" id=\"newform\">	
			<input type=\"hidden\" name=\"token\" value=%s>		
			<div class=\"form-group form-group-lg\">
				<label class=\"col-lg-2 control-label\" >New title</label>
				<div class=\"col-lg-8\">
					<input type=\"text\" class=\"form-control\" name=\"description\" >
				</div>
			</div>
			<div class=\"form-group form-group-lg\">
				<label class=\"col-lg-2 control-label\" >New link:</label>
				<div class=\"col-lg-8\">
					<input class=\"form-control\" name=\"link\" type=\"text\">
				</div>
			</div>

			<div class=\"form-group\">        
	      		<div class=\"col-sm-offset-2 col-sm-10\">
	        		<button type=\"submit\" name=\"create\" class=\"btn btn-primary\">Post</button>
	      		</div>
	    	</div>
		</form>
	</div>", $_SESSION['token']);

		}
		else {

			printf("
					<div class=\"formtext\"><br>
					Post your own? Please
						<form action=\"userlogin.php\" method=\"POST\">\n
							
							<input class=\"btn btn-primary\" type=\"submit\" value=\"Log In\"/>
							
						</form>
					</div>
					<br><br>");
		}
		?>



	<div class="rightpad10">
		
			<?php
			if($login)
			{
				printf("<form action=\"manage.php\" method=\"POST\">\n
				<input class=\"btn-lg\" type=\"submit\" value=\"Manage Your Post\" />
				</form><br>");			
			}
			else
			{
				printf("<br><br>");
			}
			?>
		
	</div>


	<div class="canvas">
		<table id="storyTable" class="col-md-10">
			<?php
			// insert sql queries here: select all stroies in order of time

			//-------for test-------

			$newsList = News::returnAllNews($mysqli);

			while($row = $newsList->fetch_assoc()){

				$news = News::retrieveNews($row['NEWS_ID'],
								   $row['LINK'],
								   $row['USER_ID'],
								   $row['DESCRIPTION'],
								   $row['TIME_STAMP']);
				$thisNewsId = $news->getNewsId();
				$thisNewsLink = $news->getlink();				
				$thisNewsDes = $news->getDescription();
				$thisNewsTime = $news->getTimeStamp();
				$thisNewsUserID = $news->getUserId();
				$thisNewsUser = User::retrieveUserByUserId($thisNewsUserID, $mysqli)->getUserName();

				
				/*Make the stroy table*/
				printf("<tr class=\"storyTable\">
					<td class=\"col-md-1\">
						<p class=\"toppad\">%s</p>
						<p class=\"toppad\"><img src=\"http://getbootstrap.com/assets/img/sass-less.png\" alt=\"Smiley face\" height=\"42\" width=\"42\"></p>
					</td >
					<td class=\"col-md-9\">

						<br><p class=\"storyTitle\"><a href=\"%s\">%s</a></p><br>
								
							",
							htmlspecialchars($thisNewsUser),
							htmlspecialchars($thisNewsLink),
							htmlspecialchars($thisNewsDes)
					);
				/*Make the comment table*/
				printf("
							<a href=\"%s\" class=\"normal\" data-toggle=\"collapse\">Comment</a>", "#comment" . $thisNewsId);
						
							
				
				if($thisNewsUserID == $userID) {
					printf("
					<a class=\"normal\" href=\"%s\" data-toggle=\"collapse\">Edit</a>
					<a class=\"normal\" href=\"%s\" data-toggle=\"collapse\">Delete</a>
					<br>
					<div id=\"%s\" class=\"collapse\">
						<div class=\"formtextNormal\">
							Are you sure to delete this story?
							<form action=\"delete_news.php\" method=\"POST\" id=\"deleteform\">
								<input type=\"hidden\" name=\"token\" value=%s>
								<input type=\"hidden\" name=\"newsID\" value=\"%s\" />
								
								<br><br>
								
								<input class=\"btn btn-primary\" type=\"submit\" name=\"delete_news\" value=\"Sure\" />
								<br>
							</form>
						</div>
					</div>
					<div id=\"%s\" class=\"collapse\">

			    		<form action=\"edit_news.php\" method=\"POST\" class=\"form-horizontal\" role=\"form\" id=\"eidtform\">
							<input type=\"hidden\" name=\"newsID\" value=\"%s\" />
							<input type=\"hidden\" name=\"token\" value=%s>
							<div class=\"form-group form-group-lg\">
								<label class=\"col-lg-2 control-label\" >New title</label>
								<div class=\"col-lg-10\">
									<input class=\"form-control\" name=\"new_description\" type=\"text\" >
								</div>
							</div>
							<div class=\"form-group form-group-lg\">
								<label class=\"col-lg-2 control-label\" >New link:</label>
								<div class=\"col-lg-10\">
									<input class=\"form-control\"name=\"new_link\" type=\"text\" >
								</div>
							</div>
							<input class=\"btn btn-primary\" type=\"submit\" name=\"edit\" value=\" Upadate \" /><br>
						</form>
						
					</div>",
					"#edit" . $thisNewsId,
					"#delete" . $thisNewsId,
					"delete" . $thisNewsId,
					$_SESSION['token'],
					$thisNewsId,
					"edit" . $thisNewsId,
					$thisNewsId,
					$_SESSION['token']);
				}
				printf(
				"<br><br>
				<div id=\"%s\" class=\"collapse\">
			    	<table class=\"cmtTable\">
				    	<tr><td>", "comment" . $thisNewsId);
				if($login) {
					printf(
				    		"<form action=\"create_comment.php\" method=\"POST\" id=\"%s\">
								<input type=\"hidden\" name=\"token\" value=%s>
								<input type=\"hidden\" name=\"newsID\" value=\"%s\" />
								<textarea rows=\"4\" cols=\"80\" style=\"border:solid 1px #CCCCCC;\" name=\"content\" form=\"%s\" placeholder=\"Write your comment here\"></textarea><br>
								<br>
								<input class=\"btn btn-primary\" type=\"submit\" name=\"create_comment\" value=\" Post \" />
							</form>
							<br>
						</td></tr>", "commentform" . $thisNewsId, $_SESSION['token'], $thisNewsId, "commentform" . $thisNewsId);
				}

				$commentList = Comment::returnAllComments($mysqli, $thisNewsId);
				while($row = $commentList->fetch_assoc()){
					$comment = Comment::retrieveComment($row['COMMENT_ID'],
									   $row['NEWS_ID'],
									   $row['USER_ID'],
									   $row['CONTENT'],
									   $row['TIME_STAMP']);
					$thisComUId = $comment->getUserId();
					$thisComId  = $comment->getCommentId();
					$thisComNId = $comment->getNewsId();
					$thisComCont = $comment->getContent();
					$thisComUser = User::retrieveUserByUserId($thisComUId, $mysqli)->getUserName();

					printf("
		    			<tr>
		    				<td>
		    					<div>%s: %s"
			    					 ,$thisComUser, htmlentities($thisComCont));

				    if($thisComUser == $username) {		
				   	 	printf("	<div class=\"rightpad1\">	
				   	 					<form action=\"delete_comment.php\" method=\"POST\">\n
				   	 						<input type=\"hidden\" name=\"token\" value=%s>
				   	 						<input type=\"hidden\" name=\"comID\" value=\"%s\" />
											<input class=\"btn btn-primary\" type=\"submit\" name = \"delete_comment\" value=\"Delete\" />
										</form>
										<a href=\"%s\" class=\"btn btn-primary\" data-toggle=\"collapse\">Edit</a>
									</div>
									<div id=\"%s\" class=\"collapse\">
										<form action=\"edit_comment.php\" method=\"POST\" id=\"%s\">
											<input type=\"hidden\" name=\"token\" value=%s>
											<input type=\"hidden\" name=\"comID\" value=\"%s\" />
											<textarea rows=\"2\" cols=\"80\" style=\"border:solid 1px #CCCCCC;\" type=\"text\" name=\"new_content\" form=\"%s\" placeholder=\"Write your new comment here\"></textarea><br>
											<br>
											<input class=\"btn btn-primary\" type=\"submit\" name=\"edit\" value=\" Post \" />
										</form>
									</div>
									", 	$_SESSION['token'],
										$thisComId,
										"#editCom" . $thisComId,
										"editCom" . $thisComId,
										"editComForm" . $thisComId,
										$_SESSION['token'],
										$thisComId,
										"editComForm" . $thisComId
									);
					}	

					printf("
									
								</div>
							</td>		
						</tr>");
					

				}
				printf("
					    	</table>
					    	<br>
			  		</div>
			  	</td></tr>"
				);
			}
				
			?>
				
		</table>
	</div>
	<div class="banner">
		<div class="container-fluid">
			<div class="center"><br>
			<p class="bannerWhite">
				<a href="newsboard.php">
				Last Story Posted At: 
				<?php 
				/* Show date and time */

				$stmt = $mysqli->prepare(
					"SELECT NEWS_ID, LINK, USER_ID, DESCRIPTION, TIME_STAMP 
			 		FROM NEWS
			 		WHERE NEWS_ID=(SELECT MAX(NEWS_ID) FROM NEWS)");
				if(!$stmt){
					printf("Query Prep Failed: %s\n", $mysqli->error);
					exit;
				}
				$value = $stmt->execute();
				if(!$value){
					printf("Select Failed: %s\n", $mysqli->error);
					exit;
				}
				$value = $stmt->bind_result($news_id, $link, $user_id, $description, $time_stamp);
				$stmt->fetch();
				$stmt->close();
				if(!$value) {
					printf("Select Failed: %s\n", $mysqli->error);
					exit;
				}
				
				$user = User::retrieveUserByUserId($user_id,$mysqli);

				printf("%s\n", $time_stamp);
				printf("by %s\n", $user->getUserName());

				?>
				</a>
			</p></div>      
			<br>
		</div> 
	</div>
</body>

</html>