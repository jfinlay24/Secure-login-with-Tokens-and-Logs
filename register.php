<?php
error_reporting(0);

function encrypt_decrypt($action, $string) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'This is my secret key';
    $secret_iv = 'This is my secret iv';
    // hash
    $key = hash('sha256', $secret_key);
    
    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if( $action == 'decrypt' ) {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}

// Include config file
if(isset($_POST['register'])){
	
	require_once 'config.php';
	 
	// Define variables and initialize with empty values
	$username = $password = $confirm_password = $email = $dob = "";
	$username_err = $password_err = $confirm_password_err = $email_err = $dob_err = "";
	 
	// Processing form data when form is submitted
	//if($_SERVER["REQUEST_METHOD"] == "POST"){
	 
		// Validate username
		if(empty(trim($_POST["username"]))){
			$username_err = "Please enter a username.";
		} else{
			 // Prepare a select statement
			$sql = "SELECT id FROM users WHERE username = ?";
			
			if($stmt = mysqli_prepare($link, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "s", $param_username);
				
				// Set parameters
				$param_username = trim($_POST["username"]);
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt)){
					/* store result */
					mysqli_stmt_store_result($stmt);
									
					if(mysqli_stmt_num_rows($stmt) == 1){
						$username_err = "This username is already taken.";
					} 
					else
					{
						$username = trim($_POST["username"]);
					}
				} 
				else
				{
					echo "Oops! Something went wrong. Please try again later.";
				}
			}
			 
			// Close statement
			mysqli_stmt_close($stmt);
		}
		if (empty(trim($_POST["email"])))
		{
			$email_err = "Please enter an email";
		}
		else
		{
			$email = trim($_POST["email"]);
		}

		if (empty($_POST["dob"]))
		{
			$dob_err = "Please enter a dob";
		}
		else
		{
			$dob = ($_POST["dob"]);
		}
		
		// Validate password
		$uppercase = preg_match('@[A-Z]@', $_POST['password']);
		$lowercase = preg_match('@[a-z]@', $_POST['password']);
		$number    = preg_match('@[0-9]@', $_POST['password']);
		if(empty(trim($_POST['password']))){
			$password_err = "Please enter a password.";     
		} elseif(!$uppercase || !$lowercase || !$number || strlen(trim($_POST['password'])) < 8){
			$password_err = "Password must have atleast 8 characters, a uppercase letter and lowercase letter.";
		} else{
			$password = trim($_POST['password']);
		}
		
		// Validate confirm password
		if(empty(trim($_POST["confirm_password"]))){
			$confirm_password_err = 'Please confirm password.';     
		} else{
			$confirm_password = trim($_POST['confirm_password']);
			if($password != $confirm_password){
				$confirm_password_err = 'Password did not match.';
			}
		}
		
		// Check input errors before inserting in database
		if(empty($username_err) && empty($email_err) && empty($dob_err) && empty($password_err) && empty($confirm_password_err)){
			
			// Prepare an insert statement
			$sql = "INSERT INTO users (username, password, email, dob) VALUES (?, ?, ?, ?)";
			 
			if($stmt = mysqli_prepare($link, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "ssss", $param_username, $param_password, $param_email, $param_dob);
				
				// Set parameters
				$param_username = password_hash($username, PASSWORD_DEFAULT);
				$param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
				$param_email = password_hash($email, PASSWORD_DEFAULT);
				$param_dob = password_hash($dob, PASSWORD_DEFAULT);
				//$param_dob = AES_ENCRYPT('$dob', '"SALT"');

				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt))
				{
					date_default_timezone_set("Europe/London"); //getting current timezone 
                    $usernameLog = $email; //Assigning username if needed
                    $date = date('Y-m-d H:i:s', time()); //getting current timestamp
                    $where = "Register"; //location of event 
                    $action = "User registered"; //action occurred
                    $sqlQuery = "UPDATE users SET username='$param_username', password = '$param_password', email = '$param_email' WHERE email='$param_email'"; //sql query used in log
            
                    $file = fopen("Logs.txt", "a+") or die("Unable to open file!"); //Opening txt. file
                    $txt = "\r\n" .$strForSession. ", ". htmlspecialchars($username). ", ".$date . ", ".$where .", ". $action .", ". $sqlQuery . "\r\n"; //Sending variables to .txt file
                    fwrite($file, $txt); //writing to txt file
					// Redirect to login page
					header("location: login.php");
				} 
				else
				{
					echo "Something went wrong. Please try again later.";
				}
			}
			 
			// Close statement
			mysqli_stmt_close($stmt);
		}
		
		// Close connection
		mysqli_close($link);
	//}
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Sign Up</h2>
        <p>Please fill this form to create an account.</p>
        <form name = "register" id = "register" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Username:<sup>*</sup></label>
                <input type="text" name="username"class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div> 
			<div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
                <label>Email:<sup>*</sup></label>
                <input type="email" name="email"class="form-control" value="<?php echo $email; ?>">
                <span class="help-block"><?php echo $email_err; ?></span>
            </div>
			<div class="form-group <?php echo (!empty($dob_err)) ? 'has-error' : ''; ?>">
                <label>Date Of Birth:<sup>*</sup></label>
                <input type="date" name="dob"class="form-control" value="<?php echo $dob; ?>">
                <span class="help-block"><?php echo $dob_err; ?></span>
            </div>   
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Password:<sup>*</sup></label>
                <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                <label>Confirm Password:<sup>*</sup></label>
                <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
                <span class="help-block"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" name="register" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-default" value="Reset">
            </div>
            <p><a href="login.php">Login here</a>.</p>
        </form>
		<form action="createTable.php" method="post">
			    <input type="submit" class="btn btn-primary" value="Create Table">
				</form>
    </div>    
</body>
</html>