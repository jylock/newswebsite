<?php
include_once 'user_class.php';
include_once '../../secure_files/database_access.php';

class News {
	private $news_id;
	private $link;
	private $description;
	private $user_id;
	private $time_stamp;

	/* default constructor, should not be called directly */
	private function __construct($news_id=NULL, 
								 $link, 
								 $user_id=NULL,
								 $description,
								 $time_stamp=NULL) {
		$this->news_id = $news_id;
		$this->link = $link;
		$this->user_id = $user_id;
		$this->description = $description;
		$this->time_stamp = $time_stamp;
	}

	public static function createNews($link, $description) {
		$instance = new self(NULL, $link, NULL, $description);
		return $instance;
	}

	public static function retrieveNews($news_id=NULL, 
										$link, 
										$user_id=NULL,
										$description,
										$time_stamp=NULL) {
		$instance = new self($news_id, 
							 $link, 
							 $user_id, 
							 $description,
							 $time_stamp);
		return $instance;
	}

	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}

	public function setlink($new_link) {
		$this->link = $new_link;
	}

	public function setDescription($new_description) {
		$this->description = $new_description;
	}

	public function getNewsId() {
		return $this->news_id;
	}

	public function getlink() {
		return $this->link;
	}

	public function getDescription() {
		return $this->description;
	}

	public function getUserId() {
		return $this->user_id;
	}

	public function getTimeStamp() {
		return $this->time_stamp;
	}

	/* insert a new news into database */
	public function insert($mysqli) {
		$stmt = $mysqli->prepare(
				"INSERT INTO NEWS 
				 VALUES(NULL, ?, ?, ?, NULL)");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('sss', $this->link, $this->user_id, $this->description);
		$value = $stmt->execute();
		if(!$value){
			printf("Insert Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->close();
	}

	/* retrieve a news by its news_id, this is different from 
	retrieveNews().  retrieveNews() is more basic*/
	public static function retrieveNewsByNewsId($news_id, $mysqli) {
		$stmt = $mysqli->prepare(
			"SELECT NEWS_ID, LINK, USER_ID, DESCRIPTION, TIME_STAMP 
			 FROM NEWS
			 WHERE NEWS_ID=?");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('s', $news_id);
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
		$instance = News::retrieveNews($news_id, $link, $user_id, $description, $time_stamp);
		return $instance;
	}

	/* retrieve an existing news and edit it, then store back */
	public function edit($new_link, $new_description, $mysqli) {
		$stmt = $mysqli->prepare(
			"UPDATE NEWS 
			 SET LINK=?, DESCRIPTION=? 
			 WHERE NEWS_ID=?");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('sss', $new_link, $new_description, $this->news_id);
		$value = $stmt->execute();
		if(!$value){
			printf("Update Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->close();
	}

	/* delete an existing news from database */
	public function delete($mysqli) {
		$stmt = $mysqli->prepare(
			"DELETE FROM NEWS 
			 WHERE NEWS_ID=?");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('s', $this->news_id);
		$value = $stmt->execute();
		if(!$value){
			printf("Delete Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->close();
	}

	/* format news layout nicely */
	public function prettyPrintNews() {
		printf("\t<li>
					<a href=%s> %s %s %s</a>",
			htmlspecialchars( $this->link ),
			htmlspecialchars( $this->description ),
			htmlspecialchars( $this->user_id ),
			htmlspecialchars( $this->time_stamp));
	}

	/* display a single news entry,
	create different buttons for different permissions,
	input: $row,which is returned from 
	$result->fetch_assoc() */
	public static function displayNews($row) {
		/* retrieve news and put it into an instance */
		$news = News::retrieveNews($row['NEWS_ID'],
								   $row['LINK'],
								   $row['USER_ID'],
								   $row['DESCRIPTION'],
								   $row['TIME_STAMP']);
		/* check if user is logged in */
		/* if user is logged in */
		if(isset($_SESSION['user'])) {
			/* if this news is posted by the user
			edit news, delete news, comment */
			if($row['USER_ID'] == $_SESSION['user']->getUserId()){
			$news->prettyPrintNews($row);
			printf("
					<form action=\"news_page.php\" method=\"POST\">
						<input type=\"hidden\" name=\"news_id\" value=%s >
						<input type=\"hidden\" name=\"token\" value=%s>
						<input type=\"submit\" name=\"edit_news\" value=\"edit news\">
						<input type=\"submit\" name=\"delete_news\" value=\"delete news\">
						<input type=\"submit\" name=\"comment\" value=\"comment\">
					</form>
				 </li>\n",
			htmlspecialchars( $row['NEWS_ID']),
			$_SESSION['token']);
			}
			/* else this news is not posted by the user 
			comment */
			else {
				$news->prettyPrintNews($row);
				printf("
					<form action=\"news_page.php\" method=\"POST\">
						<input type=\"hidden\" name=\"news_id\" value=%s >
						<input type=\"hidden\" name=\"token\" value=%s>
						<input type=\"submit\" name=\"comment\" value=\"comment\">
					</form>
				 </li>\n",
				htmlspecialchars( $row['NEWS_ID']),
				$_SESSION['token']);
			}
		}
		/* if user is not a registered user 
		view comment*/
	 	else {
	 		$news->prettyPrintNews($row);
			printf("
					<form action=\"news_page.php\" method=\"POST\">
						<input type=\"hidden\" name=\"news_id\" value=%s >
						<input type=\"submit\" name=\"view_comment\" value=\"view comment\">
					</form>
				 	</li>\n",
			htmlspecialchars( $row['NEWS_ID']));
		}
	}

	/* display all news from the database, allow logged in users more features */
	public static function displayAllNews($mysqli) {
		$stmt = $mysqli->prepare(
			"SELECT NEWS_ID, LINK, USER_ID, DESCRIPTION, TIME_STAMP 
			 FROM NEWS");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$value = $stmt->execute();
		if(!$value){
			printf("Select Failed: %s\n", $mysqli->error);
			exit;
		}
		$result = $stmt->get_result();

		echo "<ul>\n";
		while($row = $result->fetch_assoc()){
			News::displayNews($row);
		}
		echo "</ul>\n";
		$stmt->close();
		var_dump($result);
	}

	public static function returnAllNews($mysqli) {
		$stmt = $mysqli->prepare(
			"SELECT NEWS_ID, LINK, USER_ID, DESCRIPTION, TIME_STAMP 
			 FROM NEWS");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
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