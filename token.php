<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	 <link rel="stylesheet" href="css/style.css">  
<title>Website</title>
</head>

<body>

 <?php
session_start();
echo "<b> The token generated, and to be used in resetting is: </b><br />";
$token = $_SESSION['token'];
echo $token . "<br /><br />";

// ------------------------------------------------------------------------------------------------------------------------//
// Variable declaration

$ser = "localhost";
$user = "root";
$pass = "";
$db = "sadproject";
error_reporting(0);

if (isset($_POST['login']))
	{

	// Obtain the IP address

	if (!empty($_SERVER['HTTP_CLIENT_IP']))
		{
		$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
	  else
		{
		$ip = $_SERVER['REMOTE_ADDR'];
		}

	// Obtain the port Number

	$rport = $_SERVER['REMOTE_PORT'];

	// Obtain the Useragent

	$browser = $_SERVER['HTTP_USER_AGENT'];

	// Concatenate all values to setup the session for pre-login

	$strForSession = (string)$browser . (string)$ip;
	print_r($strForSession);
	echo "<br />";
	session_start();

	// MD5 Hash for the session

	$strForSession = md5($strForSession);
	$_SESSION["PreAuthSess"] = $strForSession;
	print_r($strForSession . "<br /><br />");
	$con = mysqli_connect($ser, $user, $pass, $db) or die("Connection Failed");
	$sql = "SELECT `Counter`,`Tstamp` FROM `activesession` WHERE `SessionID` = '$strForSession'";
	$objDateTime = new DateTime('NOW');
	$query = mysqli_query($con, $sql);
	if ($query->num_rows == 0)
		{
		$sql = "INSERT INTO `activesession` (`SessionID`, `Counter`, `Tstamp`) VALUES ('$strForSession', '0', NOW())";
		print_r($sql);
		if (!mysqli_query($con, $sql))
			{
			die('Error: ' . mysqli_error($con));
			}
		  else
			{

			// debug output
			// echo "active session inserted into database<br/>";

			}
		}
	  else
		{
		$sql = "SELECT `Counter` FROM `activesession` WHERE `SessionID` = '$strForSession'";
		$result = mysqli_query($con, $sql);
		if (!$result)
			{
			die('Could not query:' . mysql_error());
			}
		  else
			{ //Query ran OK
			$counter = ($result->fetch_row() [0]); // get the counter
			echo ("<br />Counter in active session: " . $counter); // debug msg
			If ($counter >= 3)
				{
				$sql = "SELECT `Tstamp` FROM `activesession` WHERE `SessionID` = '$strForSession'";
				$result = mysqli_query($con, $sql);
				if (!$result)
					{
					die('Could not query:' . mysql_error());
					}
				  else
					{

					// get the last login attempt time to determine if a 5 min lockout should be enforced

					$lastLoginAttemptTime = ($result->fetch_row() [0]);
					echo ("<br />Last Login Attempt Time: " . $lastLoginAttemptTime);
					}

				date_default_timezone_set('Europe/London');
				$currentTime = date('Y-m-d H:i:s');
				echo ("<br />Current Time: " . $currentTime);
				$differenceInSeconds = strtotime($currentTime) - strtotime($lastLoginAttemptTime);
				echo ("<br />Time difference in sec: " . $differenceInSeconds);
				if ((int)$differenceInSeconds <= 30) // set to 30s for testing should be 5 min (300)
					{
					$remainingDelay = 300 - $differenceInSeconds;
					echo ("<br />Remaining Delay: " . $remainingDelay . " seconds");
					}
				  else
					{ // Display Login

					// reset the counter as 5 min has passed.

					$sql = "UPDATE `activesession` SET Counter = 0, `Tstamp` = NOW() WHERE `SessionID` = '$strForSession'";
					$result = mysqli_query($con, $sql);
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
	}

	// --------------------------------------------------------------------------------------------------//

	if (isset($_POST['token']))
	{
		require_once 'config.php';

		$ser = "localhost";
		$user = "root";
		$pass = "";
		$db = "sadproject";

		// Grabbing variables from form via POST

		$email = $_POST['email'];
		$dob = $_POST['dob'];
		$passwordToken = $_POST['passwordToken'];
		$password = $_POST['password'];
		$password2 = $_POST['password2'];
		$current = date('Y-m-d H:i:s', time());

		// query for select

        //$result = mysqli_query($link, "SELECT * FROM users WHERE email='$email' ");
		//$row = mysqli_fetch_array($result);
		$result = mysqli_query($link, "SELECT * FROM users");
		if ($result->num_rows > 0) 
		{
			while($row = mysqli_fetch_assoc($result)) 
			{
				$hashed_email = $row['email'];
				$hashed_dob = $row['dob'];
				if(password_verify($email, $hashed_email))
				{
					if(password_verify($dob, $hashed_dob))
					{
						$sessionQuery = mysqli_query($db, "SELECT * FROM `activesession` WHERE `SessionID` = '$strForSession' ");
						$rowSes = mysqli_fetch_array($sessionQuery);
						echo "<br />";
						echo "Current attempts: ";
						echo $rowSes['Counter'];
						echo "This " + $strForSession;
						if ($rowSes['Counter'] >= 3)
						{
							echo "<script type='text/javascript'>alert('You are locked out')</script>";
							date_default_timezone_set("Europe/London"); //getting current timezone 
							$usernameLog = $email; //Assigning username if needed
							$date = date('Y-m-d H:i:s', time()); //getting current timestamp
							$where = "Token"; //location of event 
							$action = "User locked out."; //action occurred
							$sqlQuery = ""; //sql query used in log
					
							$file = fopen("Logs.txt", "a+") or die("Unable to open file!"); //Opening txt. file
							$txt = "\r\n" .$strForSession. ", ". htmlspecialchars($username). ", ".$date . ", ".$where .", ". $action .", ". $sqlQuery . "\r\n"; //Sending variables to .txt file
							fwrite($file, $txt); //writing to txt file
						}
						else
						{
							if ($current < $row['tokenTime'])
							{
								if ($row['token'] != $passwordToken)
								{
									echo "<script type='text/javascript'>alert('Error: Incorrect token')</script>";
									date_default_timezone_set("Europe/London"); //getting current timezone 
									$usernameLog = $email; //Assigning username if needed
									$date = date('Y-m-d H:i:s', time()); //getting current timestamp
									$where = "Token"; //location of event 
									$action = "Incorrect token"; //action occurred
									$sqlQuery = ""; //sql query used in log
							
									$file = fopen("Logs.txt", "a+") or die("Unable to open file!"); //Opening txt. file
									$txt = "\r\n" .$strForSession. ", ". htmlspecialchars($username). ", ".$date . ", ".$where .", ". $action .", ". $sqlQuery . "\r\n"; //Sending variables to .txt file
									fwrite($file, $txt); //writing to txt file
								}
								if ($row['token'] == $passwordToken && $row['dob'] == $hashed_dob)
								{
									$pattern = "/^.*(?=.{7,})(?=.*d)(?=.*[a-z])(?=.*[A-Z]).*$/"; //Password Pattern.
									$passwordresult = preg_match($pattern, $password); //Checking password against pattern
									if ($passwordresult == 0)
									{ //if the password is 0 it does not meet requirements/did not match pattern
										echo "<script type='text/javascript'>alert('Password does not meet requirements')</script>";
										date_default_timezone_set("Europe/London"); //getting current timezone 
										$usernameLog = $email; //Assigning username if needed
										$date = date('Y-m-d H:i:s', time()); //getting current timestamp
										$where = "Token"; //location of event 
										$action = "Password did not meet requirements"; //action occurred
										$sqlQuery = ""; //sql query used in log
								
										$file = fopen("Logs.txt", "a+") or die("Unable to open file!"); //Opening txt. file
										$txt = "\r\n" .$strForSession. ", ". htmlspecialchars($username). ", ".$date . ", ".$where .", ". $action .", ". $sqlQuery . "\r\n"; //Sending variables to .txt file
										fwrite($file, $txt); //writing to txt file
									}
									else if($password == $password2)
									{ //if ok, go ahead and insert to table the new password
										$password = password_hash($password, PASSWORD_DEFAULT);
										//echo "Passworded Changed! Please return to Login page.";
										$sql = "UPDATE users SET password='$password', tokenLock = '0' WHERE email= '$hashed_email'"; //Update sql statement
										
										date_default_timezone_set("Europe/London"); //getting current timezone 
										$usernameLog = $email; //Assigning username if needed
										$date = date('Y-m-d H:i:s', time()); //getting current timestamp
										$where = "Token"; //location of event 
										$action = "Password successfully changed"; //action occurred
										$sqlQuery = "UPDATE users SET password='$password', tokenLock = '0', WHERE email='$hashed_email'"; //sql query used in log
								
										$file = fopen("Logs.txt", "a+") or die("Unable to open file!"); //Opening txt. file
										$txt = "\r\n" .$strForSession. ", ". htmlspecialchars($username). ", ".$date . ", ".$where .", ". $action .", ". $sqlQuery . "\r\n"; //Sending variables to .txt file
										fwrite($file, $txt); //writing to txt file
										
										echo "<script language='javascript' type='text/javascript'> location.href='login.php' </script>";
										if ($link->query($sql) === TRUE)
										{ //checking connection to the table/db
										
											$sql = "UPDATE activesession SET counter = 0 WHERE sessionID ='$strForSession' ";
											$db->query($sql);
											echo "<script type='text/javascript'>alert('Password successfully changed. Please log in using your new credentials.')</script>";
											session_destroy();
											echo "<script language='javascript' type='text/javascript'> location.href='login.php' </script>";
										}
										else
										{
											echo "Error: " . $sql . "<br />" . $db->error;
										}
									}
									else
									{
										echo "Passwords did not match!";
										date_default_timezone_set("Europe/London"); //getting current timezone 
										$usernameLog = $email; //Assigning username if needed
										$date = date('Y-m-d H:i:s', time()); //getting current timestamp
										$where = "Token"; //location of event 
										$action = "Passwords did not match."; //action occurred
										$sqlQuery = ""; //sql query used in log
								
										$file = fopen("Logs.txt", "a+") or die("Unable to open file!"); //Opening txt. file
										$txt = "\r\n" .$strForSession. ", ". htmlspecialchars($username). ", ".$date . ", ".$where .", ". $action .", ". $sqlQuery . "\r\n"; //Sending variables to .txt file
										fwrite($file, $txt); //writing to txt file
										echo "<script language='javascript' type='text/javascript'> location.href='forgotPassword.php' </script>";
									}

								}
							} //End of token input check against token
							else
							{
								if ($rowSes['Counter'] == 4)
								{
									$objDateTime = new DateTime('NOW');
									$sql = "UPDATE `activesession` SET `Counter`= 3, `Tstamp` = NOW() WHERE `SessionID` = '$strForSession'";
									$db->query($sql);
								}
								else
								{
									$sql = "UPDATE activesession SET counter = counter + 1 WHERE SessionID ='$strForSession' ";

									$db->query($sql);
								}
							}
						}
					}
					
				}
			}
		} //End of Token time check
		else
		{
						echo "<script type='text/javascript'>alert('Error: That token has expired, or your email address is incorrect')</script>";
						$sql = "UPDATE activesession SET counter = counter + 1 WHERE SessionID ='$strForSession' ";
						$db->query($sql);
						date_default_timezone_set("Europe/London"); //getting current timezone 
							$usernameLog = $email; //Assigning username if needed
							$date = date('Y-m-d H:i:s', time()); //getting current timestamp
							$where = "Token"; //location of event 
							$action = "Token expired, or email incorrect."; //action occurred
							$sqlQuery = "UPDATE activesession SET Counter='+1', Tstamp = '$date', WHERE SessionID='$strForSession'"; //sql query used in log
					
							$file = fopen("Logs.txt", "a+") or die("Unable to open file!"); //Opening txt. file
							$txt = "\r\n" .$strForSession. ", ". htmlspecialchars($username). ", ".$date . ", ".$where .", ". $action .", ". $sqlQuery . "\r\n"; //Sending variables to .txt file
							fwrite($file, $txt); //writing to txt file
		}
	} //End of POST
?>

 <br /><br /><br />
 <div class="form">		
			
	<!-- Uploading Option -->
	<form method="post">
	  <input name = "email" type="email" placeholder="Email Address"   required /><font size ="2">Please enter Date Of Birth</font>		
	  <input name = "dob" type="date" placeholder="Date of Birth"   required />	
	   <input name = "passwordToken" type="password" placeholder="Password Token"required />		  
	  <input name = "password" type="password" placeholder="New Password" pattern="(?=.*d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title = "Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" required /> <!-- Pattern for password, but aslo validated in PHP -->
	  <input name = "password2" type="password" placeholder="Confirm Password" pattern="(?=.*d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title = "Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" required /> <!-- Pattern for password, but aslo validated in PHP -->	  
	  
	  <button name = "token" type = "submit" >Change Password</button> 
	  <p class="message">Remembered it? <a href="login.php">Go Back</a></p>
	</form>
    	
</div>
</body>
</html>