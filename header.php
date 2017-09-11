<?php
include_once 'user_class.php';
/* if user is logged in, create logout button */
if(isset($_SESSION['user'])){
	printf("<h3>Logged in as %s</h3>\n", $_SESSION['user']->getUserName());
	printf("<p><a href='summary.php'><button>My Summary</button></a></p>\n");
	printf("<p><a href='logout.php'><button>logout</button></a></p>\n");
} 
/* if not logged in, create login button */
else {
	printf("<p><a href='login.php'><button>login</button></a></p>\n");
	printf("<p><a href='signup.php'><button>signup</button></a></p>\n");
}
?>

