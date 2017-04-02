<?php

require_once '../classes/config.php';

$servername = config::MYSQL_SERVER;
$username = config::MYSQL_USER;
$password = config::MYSQL_PASS;

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
} 
echo "Connected successfully<br>";

// Check existing rows
$sql = "SELECT * from emails.processed_emails LIMIT 10";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
	echo var_dump($row['subject'])."<hr />";
	//		        echo "id: " . $row["id"]. " Subject: " . $row["subject"]. "<br>";
}

$sql = "TRUNCATE TABLE emails.processed_emails";
$result = $conn->query($sql);

// --------------------------------------------------------------
// Setup default values
$subject = "test";
$received = "2017-03-25";
$attachments = "";
$partner = "";
$from_address="";
$invoice_date = "";
$invoice_amount = "";
$invoice_number ="";

// Begin reading emails	
require_once 'inbox_class.php';

$emails = New Email_reader();
$mails=$emails->output();

foreach ($mails as $value){
	$subj= $value["index"].$value["header"]->subject;
	$from_address=  $value["index"].$value["header"]->fromaddress;
	$attachments= $value["index"].$value["header"]->subject;
	$received= $value["index"].$value["header"]->date;

echo "<html><body><pre>";
print_r($value["structure"]);



	$subject= iconv_mime_decode($subj,0,"UTF-8");
	$subject=mysqli_real_escape_string($conn, $subject);

	$sql = "INSERT into `emails`.`processed_emails`(subject, received, attachments, partner, from_address, invoice_date, invoice_amount, invoice_number)
		VALUES ('$subject','$received','$attachments','$partner','$from_address','$invoice_date','$invoice_amount','$invoice_number')";

//	echo $sql;
	$result=$conn->query($sql) or die($conn->error);
	if(!$result) echo "</br> insert Failed </br>";
	if($result) echo "</br> insert Success </br>";


	if (gettype($result)=="object"){
		$result->close();
	}

	echo mysqli_insert_id($conn);

}
$conn->close();

?>
