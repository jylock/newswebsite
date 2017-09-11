<?php
	include_once 'user_class.php';
	include_once '../../secure_files/database_access.php';
	$failInfo = "0";

	if(!isset($_SESSION)){
		session_start();
	}

	if( isset($_POST['login'])) {
		if( isset($_POST['user']) && preg_match('/^[\w_\.\-]+$/', $_POST['user'])) {
			if( isset($_POST['password']) && preg_match('/^[\w_\.\-]+$/', $_POST['password']) ) {
				$user = $_POST['user'];
				$password = $_POST['password'];

				/* log in function */
				if(!User::isLoginSuccessful($user, $password, $mysqli)) {
					$failInfo = "Login fail.";
				}
				else {
					header("Location: newsboard.php");
					exit;
				}

			} else {
				$failInfo = "Invaild password.";

			}
			
		} else {
			$failInfo = "Invalid username.";
		}
		unset($_POST['login']);
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
	<title>User Login</title>
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
		        <h2 class="form-login-heading">Please Log In</h2>
		        <!-- <label for="inputEmail" class="sr-only">Email address</label> -->

		        <input type="text" id="username" class="form-control" name="user" placeholder="Username" required autofocus />
		        <p></p>
		        <!-- <label for="inputPassword" class="sr-only">Password</label> -->
		        <input type="password" id="inputPassword" class="form-control" name="password" placeholder="Password" required />

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
			    <button class="btn-lg" type="submit" name="login" value="login">Log in</button> 
		    </form>
		    	<a href='newsboard.php'><button class="btn-lg">Return to main page</button></a>
	      </div>
	    </div> 
	</div>


	</body>
</html>