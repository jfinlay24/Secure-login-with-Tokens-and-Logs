<?php
/*$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($servername, $username, $password);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

// Create database
$sql = "CREATE DATABASE sadproject
";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully";
} else {
    echo "Error creating database: " . $conn->error;
}*/

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'sadproject');
 
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

$sql = "CREATE DATABASE sadproject";

// Check connection
if($link->query($sql) === TRUE)
{
	echo "Database created successfully";
}
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
 
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// sql to create table
$sql = "CREATE TABLE users (
id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
username VARCHAR(100) NOT NULL COLLATE 'latin1_swedish_ci',
password VARCHAR (200),
email VARCHAR (200),
dob VARCHAR (255),
lastTime datetime NOT NULL,
attempts int(11) NOT NULL,
token VARCHAR (200),
tokenTime datetime NOT NULL,
tokenLock int(11)

)";
$sql2 = "CREATE TABLE activesession (
id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
SessionID VARCHAR(33) NOT NULL COLLATE 'latin1_swedish_ci',
Counter INT(11) NOT NULL,
Tstamp DATETIME NOT NULL
)";

if ($link->query($sql) && $link->query($sql2) === TRUE) {
    echo "<script type='text/javascript'>alert('Table successfully created')</script>";
	echo "<script language='javascript' type='text/javascript'> location.href='register.php' </script>";	
} else {
    echo "Error creating table: " . $link->error;
}


$link->close();
?>