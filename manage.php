<?php
include_once '../../secure_files/database_access.php';
include_once 'news_class.php';
include_once 'comment_class.php';
include_once 'user_class.php';
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
	header("Location: newsboard.php");
	exit;
}
$_SESSION['previous_page']='manage.php';
?>

<!DOCTYPE html>
<html>
<head>
	<title>Post Management</title>
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
			<br><h1 id="header">NEWS DISTRIBUTION</h1>      
			<br>
		</div> 
		<div class="rightpad1">
			<?php
				printf( "
						<div>Welcome, %s.</div>
						<a href='newsboard.php'><button class='btn btn-primary'>Return to main page</button></a>
						<form action=\"logout.php\" method=\"POST\">\n
						<input type=\"hidden\" name=\"token\" value=%s>
						<input class=\"btn btn-primary\" type=\"submit\" value=\"Log Out\" />
						</form>", $username, $_SESSION['token']);
			?>
		</div>
	</div>



	<br><br><br><br>
	<div class="newStory">
		<div class="formtext"><br>Post a new story</div>
		<form action="create_news.php" method="POST" class="form-horizontal" id="newform">	
			<input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />	
			<div class="form-group form-group-lg">
				<label class="col-lg-2 control-label" >New title</label>
				<div class="col-lg-8">
					<input type="text" class="form-control" name="description" >
				</div>
			</div>
			<div class="form-group form-group-lg">
				<label class="col-lg-2 control-label" >New link:</label>
				<div class="col-lg-8">
					<input class="form-control" name="link" type="text">
				</div>
			</div>

			<div class="form-group">        
	      		<div class="col-sm-offset-2 col-sm-10">
	        		<button type="submit" name="create" class="btn btn-primary">Post</button>
	      		</div>
	    	</div>
		</form>
	</div>
	<br>
	
	<div class="canvas">
		<table id="storyTable" class="col-md-10">
			<?php
			// insert sql queries here: select all stroies in order of time


			$newsList = News::returnAllNews($mysqli);

			printf("<h3>News Posted By Me</p>\n");
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
				if($thisNewsUserID != $userID) {
					continue;
				}
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
							htmlspecialchars($thisNewsDes));
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

			    		<form action=\"edit_news.php\" method=\"POST\" class=\"form-horizontal\" id=\"eidtform\">
			    			<input type=\"hidden\" name=\"token\" value=%s>
							<input type=\"hidden\" name=\"newsID\" value=%s>
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
					$_SESSION['token'],
					$thisNewsId);
				}


				/* comment table */
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
								<textarea rows=\"4\" cols=\"80\" style=\"border:solid 1px #CCCCCC;\" type=\"text\" name=\"content\" form=\"%s\" placeholder=\"Write your comment here\"></textarea><br>
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
											<textarea rows=\"2\" cols=\"80\" style=\"border:solid 1px #CCCCCC;\" \"type=\"text\" name=\"new_content\" form=\"%s\" placeholder=\"Write your new comment here\"></textarea><br>
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


    	<?php 
    	$user = $_SESSION['user'];
		printf("<h3>Comments Posted By Me</h3>\n"); 

		printf("
				<table class=\"storyTable\" class=\"col-md-10\">
					<tbody class=\"col-md-10\">");
		$stmt = $mysqli->prepare(
			"SELECT COMMENT_ID, NEWS_ID, USER_ID, CONTENT, TIME_STAMP 
			 FROM COMMENTS
			 WHERE USER_ID=?");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('s', $userID);
		$value = $stmt->execute();
		if(!$value){
			printf("Select Failed: %s\n", $mysqli->error);
			exit;
		}
		$commentList = $stmt->get_result();
		while($row = $commentList->fetch_assoc()){
			$comment = Comment::retrieveComment($row['COMMENT_ID'],
							   $row['NEWS_ID'],
							   $row['USER_ID'],
							   $row['CONTENT'],
							   $row['TIME_STAMP']);
			$user_id = $comment->getUserId();
			$comment_id  = $comment->getCommentId();
			$news_id = $comment->getNewsId();
			$content = $comment->getContent();
			$time_stamp = $comment->getTimeStamp();

			$news = News::retrieveNewsByNewsId($news_id, $mysqli);

			printf("
				<tr class=\"storyTable\">	
					<td class=\"col-md-1\">
						<p class=\"toppad\">%s</p>
						<p class=\"toppad\"><img src=\"http://getbootstrap.com/assets/img/sass-less.png\" alt=\"Smiley face\" height=\"42\" width=\"42\"></p>
					</td >
					<td class=\"col-md-9\">
						
						<p>%s</p>
						<p>%s</p>

						<form action=\"delete_comment.php\" method=\"POST\">
							<input type=\"hidden\" name=\"comID\" value=%s >
							<input type=\"hidden\" name=\"token\" value=%s>
							<button type=\"submit\" name=\"delete_comment\" >delete comment</button>
						</form>

						<a href=\"%s\" class=\"btn btn-info\" data-toggle=\"collapse\">Edit Comment</a>
						
						<div id=\"%s\" class=\"collapse\">
							 <form action='edit_comment.php' method='POST' id=\"%s\">
								<input type=\"hidden\" name=\"token\" value=\"%s\">
								<input type=\"hidden\" name=\"comID\" value=\"%s\">
									<label>Edit Comment:</label>
									<textarea rows=\"2\" cols=\"80\" style=\"border:solid 1px #CCCCCC;\" type=\"text\" name=\"new_content\" form=\"%s\" placeholder=\"Edit your new comment here\">
									</textarea><br>
									<br>
									<input type='submit' name='edit' value='edit'>
							</form>
						</div>

						<br>%s

					</td>
				</tr>\n
				", $username, $news->getDescription(), $content, 
				   $comment_id, $_SESSION['token'],
				   '#'.$comment_id, 
				   $comment_id, 
				   'form'.$comment_id,
				   $_SESSION['token'],
				   $comment_id,
				   'form'.$comment_id,
				   $time_stamp);
		}
		printf("</tbody>
			</table>\n");
    	 ?>
	</div>

	<div class="banner">
		<div class="container-fluid">
			<div class="center"><br>
			</div>      
			<br>
		</div> 
	</div>
</body>

</html>