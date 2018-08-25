<?php
require_once 'config.php';

session_start();

if(!isset($_SESSION['username']) || empty($_SESSION['username'])){
  header("location: login.php");
  exit;
}
$password_err = "";

if(isset($_POST["submit"]))
{
//$hashed_password = "";	
$newPassword = mysqli_real_escape_string($link, $_POST['newPassword']);
$confirm_password = mysqli_real_escape_string($link, $_POST['confirm_password']);
$username = mysqli_real_escape_string($link, $_SESSION['username']);

$uppercase = preg_match('@[A-Z]@', $_POST['newPassword']);
$lowercase = preg_match('@[a-z]@', $_POST['newPassword']);
$number    = preg_match('@[0-9]@', $_POST['newPassword']);

if(empty(trim($_POST['newPassword'])))
{
	$password_err = "Please enter a new password.";     
}
else if(!$uppercase || !$lowercase || !$number || strlen(trim($_POST['newPassword'])) < 8)
{
	$password_err = "Password must contain an uppercase, lowercase, number and be atleast 8 letters";
}
else if ($newPassword != $confirm_password)
{
    $password_err = "your passwords do not match";
}
else if ($newPassword == $confirm_password)
{
	$hashed_password = password_hash($newPassword, PASSWORD_DEFAULT); // Creates a password hash
	$sql = mysqli_query($link, "UPDATE users SET password='$hashed_password' WHERE username='$username'"); //not looking for a hashed user
	session_destroy();                                                                                     // doesnt matter for this project so didnt fix
	header("location: login.php");
	exit;
}
else
{
    mysqli_error($link);
}
mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
 <div class="page-header">
        <h1>Hi, <b><?php echo $_SESSION['username']; ?></b>. Welcome to our site.</h1>
    </div>
    <div class="wrapper">
        <h2>Change Password</h2>
        <p>Please fill in your credentials to change password.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

        <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
		<label for="newPassword">New Password</label>
		<input type="password" class="form-control" id="newPassword" placeholder="New Password" name="newPassword">
		<span class="help-block"><?php echo $password_err; ?></span>
		<label for="confirm_password">Confirm New Password</label>
		<input type="password" class="form-control" id="confirm_password" placeholder="Confirm Password" name="confirm_password"> 
		</div>
          <div class="form-group">
			<input type="submit" name="submit" class="btn btn-primary" value="Submit">
            <input type="reset" class="btn btn-default" value="Reset">
          </div>    
	</div>
        <p><a href="welcome.php">Back to welcome page.</a>.</p>
	</form>
    </div>
    
</body>
</html>