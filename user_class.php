<?php
include_once '../../secure_files/database_access.php';
include_once 'user_class.php';
include_once 'news_class.php';
include_once 'comment_class.php';

class User {

	protected $news;
	protected $comments;

	private $user_id;
	private $user_name;
	private $password_hash;
	private $first_name;
	private $last_name;
	private $email;
	private $profile_picture;

	/* constructor is private, you should never call it 
	directly.  Instead use createUser() and retrieveUser() */
	private function __construct($user_id=NULL,
						 $user_name,
						 $password_hash,
						 $first_name=NULL,
						 $last_name=NULL,
						 $email=NULL,
						 $profile_picture=NULL) {

		$this->user_id = $user_id;
		$this->user_name = $user_name;
		$this->password_hash = $password_hash;
		$this->first_name = $first_name;
		$this->last_name = $last_name;
		$this->email = $email;
		$this->profile_picture = $profile_picture;
	
		$this->news = array();
		$this->comments = array();
	}

	/* create a brand new user */
	public static function createUser($user_name, 
									  $password_hash,
									  $first_name,
									  $last_name,
									  $email,
									  $profile_picture) {
		$instance = new self(NULL,
							 $user_name,
							 $password_hash,
							 $first_name,
							 $last_name,
							 $email,
							 $profile_picture);
		return $instance;
	}

	/* create a user with stored info */
	public static function retrieveUser($user_id=NULL,
						 $user_name,
						 $password_hash,
						 $first_name=NULL,
						 $last_name=NULL,
						 $email=NULL,
						 $profile_picture=NULL) {
		$instance = new self($user_id,
							 $user_name,
							 $password_hash,
							 $first_name,
							 $last_name,
							 $email,
							 $profile_picture);
		return $instance;
	}

	/* only called when new user tries to register, 
	checks if the user name already exists
	returns true if user name is used */
	public static function isUsernameUsed($new_user_name, $mysqli) {
		$stmt = $mysqli->prepare(
			"SELECT 
				COUNT(*) 
			 FROM USERS 
			 WHERE USER_NAME=(?)");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('s', $new_user_name);
		$value = $stmt->execute();
		if(!$value){
			printf("Select Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_result($count);
		$stmt->fetch();
		$stmt->close();
		/* If the user name already exits */
		if($count != 0) {
			return true;
		}
		return false;
	}

	/* only called when new user registers, insert 
	new user data into database */
	public function register($mysqli) {
		$stmt = $mysqli->prepare(
				"INSERT INTO USERS 
				 VALUES(NULL, ?, ?, ?, ?, ?, ?)");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$hashed_password = crypt($this->password_hash,'cse503s');
		$stmt->bind_param('sssssb', $this->user_name, $hashed_password, 
						   $this->first_name, $this->last_name, $this->email, $this->profile_picture);
		$value = $stmt->execute();
		if(!$value){
			printf("Insert Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->close();
	}
	

	/* only called when user tries to log in, checks if user name and password 
	are valid.
	return true if login is successful, upon which user is loggin in
	return false if login failed, upon which the error message can be retrieved */
	public static function isLoginSuccessful($user_name, $password_hash, $mysqli) {
		$stmt = $mysqli->prepare(
			"SELECT USER_ID, USER_NAME, PASSWORD_HASH, FIRST_NAME, LAST_NAME, EMAIL, PROFILE_PICTURE
			 FROM USERS 
			 WHERE USER_NAME=(?)");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('s', $user_name);
		$value = $stmt->execute();
		if(!$value){
			printf("Select Failed: %s\n", $mysqli->error);
			exit;
		}
		/* get a mysqli_result object from your mysqli_stmt object 
		by calling its get_result() method*/
		$row = $stmt->get_result()->fetch_assoc();
		$stmt->close();
		/* If Login failed */
		if($row == NULL || 
			crypt($password_hash, $row['PASSWORD_HASH']) != $row['PASSWORD_HASH']) {
			return false;
		}
		/* retrieve user's info from stored data */
		$user = User::retrieveUser($row['USER_ID'],
						 $row['USER_NAME'],
						 $row['PASSWORD_HASH'],
						 $row['FIRST_NAME'],
						 $row['LAST_NAME'],
						 $row['EMAIL'],
						 $row['PROFILE_PICTURE']);
		/* store user in session */
		$_SESSION['user'] = $user;
		/* security measure against cross-site forgery */
		$_SESSION['token'] = substr(md5(rand()), 0, 10);
		return true;
	}

	/* Access functions */
	public function getUserId() {
		return $this->user_id;
	}

	public function getUserName() {
		return $this->user_name;
	}

	public function getFirstName() {
		return $this->first_name;
	}

	public function getLastName() {
		return $this->last_name;
	}

	public function getEmail() {
		return $this->email;
	}

	public function getProfilePicture() {
		return $this->profile_picture;
	}

	/* return an user object by it's id */
	public static function retrieveUserByUserId($user_id, $mysqli) {
		$stmt = $mysqli->prepare(
			"SELECT USER_ID, USER_NAME, PASSWORD_HASH, FIRST_NAME, LAST_NAME, EMAIL, PROFILE_PICTURE
			 FROM USERS 
			 WHERE USER_ID=(?)");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('s', $user_id);
		$value = $stmt->execute();
		if(!$value){
			printf("Select Failed: %s\n", $mysqli->error);
			exit;
		}
		$row = $stmt->get_result()->fetch_assoc();
		$stmt->close();

		if($row == NULL ) {
			printf("User Id does not exist: %s\n", $mysqli->error);
			exit;
		}
		/* retrieve user's info from stored data */
		$user = User::retrieveUser($row['USER_ID'],
						 $row['USER_NAME'],
						 $row['PASSWORD_HASH'],
						 $row['FIRST_NAME'],
						 $row['LAST_NAME'],
						 $row['EMAIL'],
						 $row['PROFILE_PICTURE']);
		return $user;
	}

	/* display all news posted by this user */
	public function displayMyNews($mysqli) {
		$stmt = $mysqli->prepare(
			"SELECT NEWS_ID, LINK, USER_ID, DESCRIPTION, TIME_STAMP 
			 FROM NEWS
			 WHERE USER_ID=?");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('s', $this->user_id);
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
	}

	/* display all comments posted by this user */
	public function displayMyComments($mysqli) {
		$stmt = $mysqli->prepare(
			"SELECT COMMENT_ID, NEWS_ID, USER_ID, CONTENT, TIME_STAMP 
			 FROM COMMENTS
			 WHERE USER_ID=?");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			exit;
		}
		$stmt->bind_param('s', $this->user_id);
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

	/* logout current user */
	public function logout() {
		session_start();
		session_destroy();
		header("Location:interface.php");
		exit;
	}

	public function printInfo() {
		printf("
			user_name = %s\n
			password_hash = %s\n", $this->user_name, $this->password_hash);
	}
}
?>