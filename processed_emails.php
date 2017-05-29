<?php

if(php_sapi_name()!="cli"){
echo '<html><body bgcolor="#000000" text="white" style="color:cyan;font-family: Calibri;font-size: 100%;"><pre>';
}

require_once 'functions/general_functions.php';
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
echo "Connected successfully";
insert_break();

// Check existing rows
$sql = "SELECT * from emails.processed_emails LIMIT 10";
$result = $conn->query($sql);

echo "--- printing top 10 existing in db---";
insert_break();insert_break();
while($row = $result->fetch_assoc()) {
	echo "<hr/>"; insert_break();echo var_dump($row['subject']);
	//		        echo "id: " . $row["id"]. " Subject: " . $row["subject"]. "<br>";

	$sql = "SELECT * from emails.processed_attachments 
			WHERE id_email=".$row['id'];
	$result_att = $conn->query($sql);
	while($row_att=$result_att->fetch_assoc()){
		echo "<li>".$row_att['id_attachment']." ".$row_att['invoice_number']."</li>";
		}


}
insert_break();

/*
   $sql = "TRUNCATE TABLE emails.processed_emails";
   $result = $conn->query($sql);
 */


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

?>
