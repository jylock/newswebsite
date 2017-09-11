<?php
include_once 'user_class.php';
include_once '../../secure_files/database_access.php';
session_start();

$failInfo = "0";

if( isset($_POST['signup'])) {
	if( isset($_POST['user']) && preg_match('/^[\w_\.\-]+$/', $_POST['user'])) {
		if( isset($_POST['password']) && preg_match('/^[\w_\.\-]+$/', $_POST['password']) ) {
			$user = $_POST['user'];
			$password = $_POST['password'];
			$firstname = $_POST['first_name'];
			$lastname  = $_POST['last_name'];
			$email     = $_POST['email'];
			// $filename = basename($_FILES['uploadedfile']['name']);
			
			// if (!preg_match('/^[\w_\.\-\s]+$/', $filename)) {
			// 	$failInfo = "Invalid photo.";
			// }
			if(!preg_match('/^[\w_\-]+$/', $firstname)) {
				$failInfo = "Invalid name.";
			}
			else if(!preg_match('/^[\w_\-]+$/', $lastname)) {
				$failInfo = "Invalid name.";
			}
			

			if(User::isUserNameUsed($user,$mysqli)){
				$failInfo = "Username: ". htmlentities($user) . " already exists</h3>\n";
			}
			else {


				/* create new user */
				// $new_user = User::createUser($user, 
				// 							 $password, 
				// 							 $firstname, 
				// 							 $lastname, 
				// 							 $email, 
				// 							 $_FILES['uploadedfile']);
				$new_user = User::createUser($user, 
											 $password, 
											 $firstname, 
											 $lastname, 
											 $email, 
											 NULL);
				/* push data into database */
				$new_user->register($mysqli);
				/* log in*/

				if(User::isLoginSuccessful($user, $password, $mysqli)) {
					header("Location: newsboard.php");
					exit;
				}
				else {
					header("Location: newsboard.php");
					exit;
				}
			}
			
		} else {
			$failInfo = "Invalid password.";
		}
		
	} else {
			$failInfo = "Invalid username.";
	}
}

?>

<!DOCTYPE html>
<html>
	<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <meta name="description" content="">
    <meta name="author" content="">
	<title>User Signup</title>
	<!-- Bootstrap core CSS -->
    <!-- <link href="bootstrap.min.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link href="news.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="banner">
			<div class="container-fluid">
				<br><h1 id="header">NEWS DISTRIBUTION</h1>      
				<div id="bwu">Be With Us</div>      
			</div>
		</div>
	<!-- Login Box -->
	<br><br>


	<div id="login">
		<p></p>
		<div class = "center"> 
			<div class="container">

		     <form class="form-login" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
		        <h2 class="form-login-heading">Thanks for being <br>with us.</h2>
		        <!-- <label for="inputEmail" class="sr-only">Email address</label> -->
		        <input type="text" id="inputusername" class="form-control" name="user" placeholder="Username" required autofocus>
		        <p></p>
		        <!-- <label for="inputPassword" class="sr-only">Password</label> -->
		        <input type="password" id="passwd" class="form-control" name="password" placeholder="Password" required>
		        <p></p>
		        <input type="text" id="firstname" class="form-control" name="first_name" placeholder="First Name" >
		        <p></p>
		        <input type="text" id="lastname" class="form-control" name="last_name" placeholder="Last Name" >
		        <p></p>
		        <input type="email" id="inputEmail" class="form-control" name="email" placeholder="Email Address" >
		        <p></p>
		        <p class = "text-danger">
			        <?php
			        if($failInfo != "0") {
			        	echo $failInfo;
			        	$failInfo = "0";
			        }
			        ?>
		    	</p>
		        <p></p>

		        <br>
			    <button class="btn-lg" type="submit" name="signup" value="signup">Log in</button> 
		    </form>
		    	<a href='newsboard.php'><button class="btn-lg">Return to main page</button></a>
	      </div>
	    </div> 
	</div>
	</body>
</html>