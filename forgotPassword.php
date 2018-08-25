
<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>Website</title>
        <link rel="stylesheet" href="css/style.css">  
</head>

<?php

session_start();

//---------------Forgot Password----------------//
if (isset($_POST['forgot']))
{
	require_once 'config.php';

    //Taking input from POST form 
    $email=$_POST['email'];
    //$result = mysqli_query($link,"SELECT * FROM users WHERE email='$email' ");
    //$row = mysqli_fetch_array($result);

    $result = mysqli_query($link, "SELECT * FROM users");
    if ($result->num_rows > 0) 
    {
        while($row = mysqli_fetch_assoc($result)) 
        {
            $hashed_email = $row['email'];
            if(password_verify($email, $hashed_email))
            {
                if($result && mysqli_num_rows($result)>0)
                { //Checking to see if email is in the database
                    $length = 16;
                    $token = bin2hex(random_bytes($length));
                    $date = date('Y-m-d H:i:s', strtotime("+5 minute"));
                    $sql = "UPDATE users SET token='$token', tokenTime = '$date', tokenLock = '1' WHERE email='$hashed_email'"; //Update sql statement
                    $result = $link->query($sql);
                    $_SESSION['token']=$token;
                    
                    date_default_timezone_set("Europe/London"); //getting current timezone 
                    $usernameLog = $email; //Assigning username if needed
                    $date = date('Y-m-d H:i:s', time()); //getting current timestamp
                    $where = "Forgot Password"; //location of event 
                    $action = "Requested Token for login"; //action occurred
                    $sqlQuery = "UPDATE users SET token='$token', tokenTime = '$date', tokenLock = '1' WHERE email='$email'"; //sql query used in log
            
                    $file = fopen("Logs.txt", "a+") or die("Unable to open file!"); //Opening txt. file
                    $txt = "\r\n" .$strForSession. ", ". htmlspecialchars($username). ", ".$date . ", ".$where .", ". $action .", ". $sqlQuery . "\r\n"; //Sending variables to .txt file
                    fwrite($file, $txt); //writing to txt file
                    echo "<script type='text/javascript'>alert('Successful: Please use token on next page to change your password')</script>";
                    echo "<script language='javascript' type='text/javascript'> location.href='token.php' </script>";	
                }
            }
        }
    }


            

else
{
    echo "<script type='text/javascript'>alert('An account does not exist for that email address')</script>";
    date_default_timezone_set("Europe/London"); //getting current timezone 
							$usernameLog = $email; //Assigning username if needed
							$date = date('Y-m-d H:i:s', time()); //getting current timestamp
							$where = "Forgot Password"; //location of event 
							$action = "No account for email entered."; //action occurred
							$sqlQuery = ""; //sql query used in log
					
							$file = fopen("Logs.txt", "a+") or die("Unable to open file!"); //Opening txt. file
							$txt = "\r\n" .$strForSession. ", ". htmlspecialchars($username). ", ".$date . ", ".$where .", ". $action .", ". $sqlQuery . "\r\n"; //Sending variables to .txt file
							fwrite($file, $txt); //writing to txt file
}
 
}

?>

<!-- Page BODY -->
<body>
  <div class="login-page">
  <div class="form">		
	
			
	<!-- Forgot Password -->
    <form name = "forgot" id = "forgot" method ="post">
      <input type="email" name ="email" placeholder="Please enter your email" required />
      <button name = "forgot" type = "submit">Submit</button>
	  <!-- Register message -->
      <p class="message">Remembered? <a href="login.php">Sign in</a></p>
    </form>

	
  </div>
</div>
	
	<!--Facilitates the scroll for Registration form -->	  
	<script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
	<!--Facilitates the JavaScript file -->
	<script type="text/javascript" src="js/index.js"></script>

</body>
</html>