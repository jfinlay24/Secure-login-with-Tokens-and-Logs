<?php

	
//-----------------------------------------------------------------------------------------------------------------//

$ser="localhost";
$user="root";
$pass="";
$db ="sadproject";
	error_reporting(0);

if(isset($_POST['login']))
{
// Obtain the IP address
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

// Obtain the port Number
$rport =  $_SERVER['REMOTE_PORT'];    
// Obtain the Useragent
$browser = $_SERVER['HTTP_USER_AGENT'];



// Concatenate all values to setup the session for pre-login
$strForSession = (string)$browser. (string)$ip;
print_r($strForSession);
echo "<br>";
session_start();




// MD5 Hash for the session
$strForSession = md5($strForSession);
$_SESSION["PreAuthSess"]=$strForSession;
print_r($strForSession . "<br/><br>");

$con = mysqli_connect($ser, $user, $pass, $db)or die("Connection Failed");
$sql = "SELECT `Counter`,`Tstamp` FROM `activesession` WHERE `SessionID` = '$strForSession'";

$objDateTime = new DateTime('NOW');


$query = mysqli_query($con,$sql);

if ($query->num_rows == 0)
{
	$sql = "INSERT INTO `activesession` (`SessionID`, `Counter`, `Tstamp`) VALUES ('$strForSession', '0', NOW())";
	
	print_r($sql);	

	if (!mysqli_query($con,$sql))  
	{
		die('Error: ' . mysqli_error($con));
	}
	else
	{
		// debug output
		//echo "active session inserted into database<br/>";
	}
	
}
else
{
	$sql = "SELECT `Counter` FROM `activesession` WHERE `SessionID` = '$strForSession'";
	
	$result = mysqli_query($con,$sql);
	
	if (!$result) 
	{
    die('Could not query:' . mysql_error());
	}
	else
	{//Query ran OK
	$counter = ($result->fetch_row()[0]);  // get the counter
	echo ("<br/>Counter in active session: ".$counter);  // debug msg
	
		If ($counter >=3)
		{
			$sql = "SELECT `Tstamp` FROM `activesession` WHERE `SessionID` = '$strForSession'";
			$result = mysqli_query($con,$sql);
		
			if (!$result) 
			{
			die('Could not query:' . mysql_error());
			}
			else
			{
				// get the last login attempt time to determine if a 5 min lockout should be enforced
				$lastLoginAttemptTime = ($result->fetch_row()[0]);		
				echo ("<br/>Last Login Attempt Time: ".$lastLoginAttemptTime);
			}
			
			date_default_timezone_set('Europe/London');
			$currentTime = date('Y-m-d H:i:s');
			echo ("<br/>Current Time: ".$currentTime);
			
			$differenceInSeconds = strtotime($currentTime) - strtotime($lastLoginAttemptTime);
			
			echo ("<br/>Time difference in sec: ".$differenceInSeconds);
			
			if((int)$differenceInSeconds <= 300) // set to 30s for testing should be 5 min (300)
			{
				$remainingDelay = 300 - $differenceInSeconds;
				echo ("<br/>Remaining Delay: " . $remainingDelay . " seconds");
			}
			else
			{ // Display Login					
				//reset the counter as 5 min has passed.
				$sql = "UPDATE `activesession` SET Counter = 0, `Tstamp` = NOW() WHERE `SessionID` = '$strForSession'";
				$result = mysqli_query($con,$sql);
				if (!$result) 
				{
					die('Could not query:' . mysql_error());
				}
				else
				{
					echo "<script>alert('Counter Reset')</script>";
				}
			}
		}
	}
}
				
//-----------------------------------------------------------------------------------------------------------------//

function encrypt_decrypt($action, $string) 
{
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

//----------------------------------------------------------------------------------------------------------------//	

	// Include config file
	require_once 'config.php';
	// Define variables and initialize with empty values
	$username = $password = "";
	$username_err = $password_err = "";
	 
	// Processing form data when form is submitted
	if($_SERVER["REQUEST_METHOD"] == "POST"){
	 
		// Check if username is empty
		if(empty(trim($_POST["username"]))){
			$username_err = 'Please enter username.';
		} else{
			$username = trim($_POST["username"]);
			$username = strip_tags($username);

		}
		
		// Check if password is empty
		if(empty(trim($_POST['password']))){
			$password_err = 'Please enter your password.';
		} else{
			$password = trim($_POST['password']);
		}

		// Validate credentials
		if(empty($username_err) && empty($password_err))
		{
			// Prepare a select statement
			$sql = mysqli_query($link, "SELECT * FROM users");
			if ($sql->num_rows > 0) {
				while($row = mysqli_fetch_assoc($sql)) 
				{
					$hashed_user = $row['username'];
					$hashed_password = $row['password'];
					$hashed_dob = $row['dob'];
					if (password_verify($username, $hashed_user)) 
					{
						echo "User Verified";
						if($counter > 4)
						{
							echo "<script>alert('User locked out')</script>";
						}
						else if(password_verify($password, $hashed_password))
						{
							echo "Password Verifed, logging in";
							/* Password is correct, so start a new session and
							save the username to the session */
							session_start();
							$_SESSION['username'] = $username;
							$sql2 = "UPDATE `activesession` SET Counter = 0, `Tstamp` = NOW() WHERE `SessionID` = '$strForSession'";
							$result = mysqli_query($con,$sql2);	
							
							date_default_timezone_set("Europe/London"); //getting current timezone 
							$usernameLog = $hashed_user; //Assigning username if needed
							$date = date('Y-m-d H:i:s', time()); //getting current timestamp
							$where = "Login"; //location of event 
							$action = "Login Succesful"; //action occurred
							$sqlQuery = "UPDATE activesession SET Counter='0', Tstamp = '$hashed_dob', WHERE SessionID='$strForSession'"; //sql query used in log
					
							$file = fopen("Logs.txt", "a+") or die("Unable to open file!"); //Opening txt. file
							$txt = "\r\n" .$strForSession. ", ". htmlspecialchars($hashed_user). ", ".$date . ", ".$where .", ". $action .", ". $sqlQuery . "\r\n"; //Sending variables to .txt file
							fwrite($file, $txt); //writing to txt file

							header("location: welcome.php");
						} 
						else
						{
							// Display an error message if password is not valid
							$password_err = 'The password you entered was not valid.';
							$sql3 = "UPDATE `activesession` SET counter = Counter + 1, `Tstamp` = NOW() WHERE `SessionID` = '$strForSession'";
							$result = mysqli_query($con, $sql3);
							
							date_default_timezone_set("Europe/London"); //getting current timezone 
							$usernameLog = $hashed_user; //Assigning username if needed
							$date = date('Y-m-d H:i:s', time()); //getting current timestamp
							$where = "Login"; //location of event 
							$action = "Login Failed, Password incorrect"; //action occurred
							$sqlQuery = "UPDATE activesession SET Counter='+1', Tstamp = '$date', WHERE SessionID='$strForSession'"; //sql query used in log
					
							$file = fopen("Logs.txt", "a+") or die("Unable to open file!"); //Opening txt. file
							$txt = "\r\n" .$strForSession. ", ". htmlspecialchars($username). ", ".$date . ", ".$where .", ". $action .", ". $sqlQuery . "\r\n"; //Sending variables to .txt file
							fwrite($file, $txt); //writing to txt file
						}
					}
					else
					{
						// Display an error message if username doesn't exist
						$user = $_POST["username"];
					    //  $username_err = "Account '$user' was not found.";
						$username_err = htmlspecialchars("Account '$user' was not found.");
						date_default_timezone_set("Europe/London"); //getting current timezone 
							$usernameLog = $email; //Assigning username if needed
							$date = date('Y-m-d H:i:s', time()); //getting current timestamp
							$where = "Login"; //location of event 
							$action = "Login Failed, User does not exist."; //action occurred
							$sqlQuery = "UPDATE activesession SET Counter='+1', Tstamp = '$date', WHERE SessionID='$strForSession'"; //sql query used in log
					
							$file = fopen("Logs.txt", "a+") or die("Unable to open file!"); //Opening txt. file
							$txt = "\r\n" .$strForSession. ", ". htmlspecialchars($username). ", ".$date . ", ".$where .", ". $action .", ". $sqlQuery . "\r\n"; //Sending variables to .txt file
							fwrite($file, $txt); //writing to txt file
					}
				}
			}
			else
			{
				echo "FAILED";
				date_default_timezone_set("Europe/London"); //getting current timezone 
							$usernameLog = $hashed_user; //Assigning username if needed
							$date = date('Y-m-d H:i:s', time()); //getting current timestamp
							$where = "Login"; //location of event 
							$action = "Login Failed, error searching for user."; //action occurred
							$sqlQuery = "UPDATE activesession SET Counter='+1', Tstamp = '$date', WHERE SessionID='$strForSession'"; //sql query used in log
					
							$file = fopen("Logs.txt", "a+") or die("Unable to open file!"); //Opening txt. file
							$txt = "\r\n" .$strForSession. ", ". htmlspecialchars($username). ", ".$date . ", ".$where .", ". $action .", ". $sqlQuery . "\r\n"; //Sending variables to .txt file
							fwrite($file, $txt); //writing to txt file
			}
		}
		
		// Close connection
		mysqli_close($link);
	}
}


?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>
        <form name="login" id="login" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Username:<sup>*</sup></label>
                <input type="text" name="username"class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Password:<sup>*</sup></label>
                <input type="password" name="password" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" name="login" class="btn btn-primary" value="Submit">
            </div>
            <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
			<p>Forget Password? <a href="forgotPassword.php">Reset Here</a>.</p>

        </form>
    </div>    
</body>
</html>