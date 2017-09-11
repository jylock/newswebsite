<?php

class Comment {
	private $comment_id;
	private $news_id;
	private $user_id;
	private $content;
	private $time_stamp;

	/* private constructor, should not be used directly */
	private function __construct($comment_id=NULL,
								   $news_id,
								   $user_id,
								   $content,
								   $time_stamp=NULL) {
		$this->comment_id = $comment_id;
		$this->news_id = $news_id;
		$this->user_id = $user_id;
		$this->content = $content;
		$this->time_stamp = $time_stamp;
	}

	/* create a new comment */
	public static function createComment($news_id, $user_id, $content) {
		$instance =  new self(NULL, $news_id, $user_id, $content,NULL);
		return $instance;
	}

	/* retrieve a comment */
	public static function retrieveComment($comment_id=NULL,
										   $news_id,
										   $user_id,
										   $content,
										   $time_stamp=NULL) {
		$instance = new self($comment_id, 
							 $news_id,
							 $user_id,
							 $content,
							 $time_stamp);
		return $instance;
	}

	public function getCommentId() {
		return $this->comment_id;
	}
	public function getNewsId() {
		return $this->news_id;
	}

	public function getUserId() {
		return $this->user_id;
	}

	public function getContent() {
		return $this->content;
	}

	public function getTimeStamp() {
		return $this->time_stamp;
	}

	/* insert current news into database */
	public function insert($mysqli) {
		$stmt = $mysqli->prepare(
				"INSERT INTO COMMENTS 
				 VALUES(NULL, ?, ?, ?, NULL)");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('sss', $this->news_id, $this->user_id, $this->content);
		$value = $stmt->execute();
		if(!$value){
			printf("Insert Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->close();
	}

	/* edit comment */
	public function edit($new_content, $mysqli) {
		$stmt = $mysqli->prepare(
			"UPDATE COMMENTS 
			 SET CONTENT=?
			 WHERE COMMENT_ID=?");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('ss', $new_content, $this->comment_id);
		$value = $stmt->execute();
		if(!$value){
			printf("Edit Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->close();
	}

	/* delete comment */
	public function delete($mysqli) {
		$stmt = $mysqli->prepare(
			"DELETE FROM COMMENTS 
			 WHERE COMMENT_ID=?");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('s', $this->comment_id);
		$value = $stmt->execute();
		if(!$value){
			printf("Delete Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->close();
	}

	/* retrieve a comment by its comment_id, this is different from 
	retrieveComment().  retrieveComment() is more basic*/
	public static function retrieveCommentByCommentId($comment_id, $mysqli) {
		$stmt = $mysqli->prepare(
			"SELECT COMMENT_ID, NEWS_ID, USER_ID, CONTENT, TIME_STAMP 
			 FROM COMMENTS
			 WHERE COMMENT_ID=?");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('s', $comment_id);
		$value = $stmt->execute();
		if(!$value){
			printf("Select Failed: %s\n", $mysqli->error);
			exit;
		}
		$value = $stmt->bind_result($comment_id, $news_id, $user_id, $content, $time_stamp);
		$stmt->fetch();
		$stmt->close();
		if(!$value) {
			printf("Select Failed: %s\n", $mysqli->error);
			exit;
		}
		$instance = Comment::retrieveComment($comment_id, $news_id, $user_id, $content, $time_stamp);
		return $instance;
	}

	/* display a single comment */
	public static function displayComment($row) {
	/* retrieve news and put it into an instance */
		$comment = Comment::retrieveComment($row['COMMENT_ID'],
									   $row['NEWS_ID'],
									   $row['USER_ID'],
									   $row['CONTENT'],
									   $row['TIME_STAMP']);
		/* All users get to view comments, no matter logged in 
		or not */
		printf("\t
				<li>
					<p>%s %s %s</p>",
				htmlspecialchars( $row['CONTENT'] ),
				htmlspecialchars( $row['USER_ID'] ),
				htmlspecialchars( $row['TIME_STAMP']));
		if(isset($_SESSION['user'])) {
			/* if this comment is posted by the user, add edit and delete
			buttons */
			if($row['USER_ID'] == $_SESSION['user']->getUserId()){
				printf("<form action=\"comment_page.php\" method=\"POST\">
							<input type=\"hidden\" name=\"comment_id\" value=%s >
							<input type=\"hidden\" name=\"token\" value=%s>
							<input type=\"submit\" name=\"edit_comment\" value=\"edit comment\">
							<input type=\"submit\" name=\"delete_comment\" value=\"delete comment\">
						</form>",
				htmlspecialchars( $row['COMMENT_ID']),
				$_SESSION['token']);
			}
		}
		printf("</li>\n");
	}

	/* display all comments from the database */
	public static function displayAllComments($mysqli, $newsid) {
		$stmt = $mysqli->prepare(
			"SELECT COMMENT_ID, NEWS_ID, USER_ID, CONTENT, TIME_STAMP 
			 FROM COMMENTS
			 WHERE NEWS_ID=?");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('s', $newsid);
		$value = $stmt->execute();
		if(!$value){
			printf("Select Failed: %s\n", $mysqli->error);
			exit;
		}
		$result = $stmt->get_result();

		echo "<ul>\n";
		while($row = $result->fetch_assoc()){
			Comment::displayComment($row);
		}
		echo "</ul>\n";
		$stmt->close();
	}
	
	public static function returnAllComments($mysqli, $newsid) {
		$stmt = $mysqli->prepare(
			"SELECT COMMENT_ID, NEWS_ID, USER_ID, CONTENT, TIME_STAMP 
			 FROM COMMENTS
			 WHERE NEWS_ID=?");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('s', $newsid);
		$value = $stmt->execute();
		if(!$value){
			printf("Select Failed: %s\n", $mysqli->error);
			exit;
		}
		$result = $stmt->get_result();
		$stmt->close();
		return $result;
	}
}


?>