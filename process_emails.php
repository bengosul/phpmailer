<?php

if(php_sapi_name()!="cli"){
echo '<html><body bgcolor="#000000" text="white"><pre>';
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
insert_break();
while($row = $result->fetch_assoc()) {
	echo var_dump($row['subject'])."<hr />";
	//		        echo "id: " . $row["id"]. " Subject: " . $row["subject"]. "<br>";
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

// Begin reading emails	
require_once 'inbox_class.php';

$email_object = New Email_reader();
$inbox_array=$email_object->output();
echo "Found ".count($inbox_array). " new emails"; insert_break();
foreach ($inbox_array as $email){
	echo "adding"; insert_break();
	$subj= $email["index"].$email["header"]->subject;
	$from_name=  $email["index"].$email["header"]->fromaddress;
	$from_address = $email['header']->from[0]->mailbox."@".$email['header']->from[0]->host;
	$received= $email["index"].$email["header"]->date;
	// print_r($value["structure"]);
	$subject= iconv_mime_decode($subj,0,"UTF-8");
	$subject=mysqli_real_escape_string($conn, $subject);
	$count=0;

	//Insert email data to retrieve index
	$sql = "INSERT into `emails`.`processed_emails`(subject, received, attachments, partner, from_address, invoice_date, invoice_amount, invoice_number,parsed)
		VALUES ('$subject','$received','$attachments','$partner','$from_address','$invoice_date','$invoice_amount','$invoice_number','$invoice_date')";
	$result=$conn->query($sql) or die($conn->error);
	// echo 'insertid:'.$conn->insert_id;
	if(!$result) echo "</br> insert Failed </br>";
	if($result) echo "</br> insert Success </br>";

	if (gettype($result)=="object"){
		$result->close();
	}

	// check for attachments
	$attachments = array();
	if (isset($email['structure']->parts) && count($email['structure']->parts)) {
		// loop through all attachments
		for ($i = 0; $i < count($email['structure']->parts); $i++) {
			// set up an empty attachment
			$attachments[$i] = array(
					'is_attachment' => FALSE,
					'filename'      => '',
					'name'          => '',
					'attachment'    => ''
					);

			// if this attachment has idfparameters, then proceed
			if ($email['structure']->parts[$i]->ifdparameters) {
				foreach ($email['structure']->parts[$i]->dparameters as $object) {
					// if this attachment is a file, mark the attachment and filename
					if (strtolower($object->attribute) == 'filename') {
						$attachments[$i]['is_attachment'] = TRUE;
						$attachments[$i]['filename']      = $object->value;
					}
				}
			}

			// if this attachment has ifparameters, then proceed as above
			if ($email['structure']->parts[$i]->ifparameters) {
				foreach ($email['structure']->parts[$i]->parameters as $object) {
					if (strtolower($object->attribute) == 'name') {
						$attachments[$i]['is_attachment'] = TRUE;
						$attachments[$i]['name']          = $object->value;
					}
				}
			}

			// if we found a valid attachment for this 'part' of the email, process the attachment
			if ($attachments[$i]['is_attachment']) {
				$count++;


				// get the content of the attachment
				$attachments[$i]['attachment'] = imap_fetchbody($email_object->conn, $email['index'], $i+1);

				// check if this is base64 encoding
				if ($email['structure']->parts[$i]->encoding == 3) { // 3 = BASE64
					$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
				}
				// otherwise, check if this is "quoted-printable" format
				elseif ($email['structure']->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
					$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
				}

				echo	"</br>insertid: ".$conn->insert_id;
//				print_r($attachments[$i]);
				file_put_contents("/store/".sprintf('%06d',$conn->insert_id)."_".sprintf('%02d',$count)."_".$attachments[$i]['filename'], $attachments[$i]['attachment']);
				// echo	mkdir("/store/xxx2");	
			}
		}
	}

	//print_r($attachments);
	$attachments=$count;
	//Insert email data to retrieve index
	$sql = "UPDATE `emails`.`processed_emails`
		SET attachments = $attachments
		WHERE id = $conn->insert_id";
	$result=$conn->query($sql) or die($conn->error);


	// move the email to Processed folder on the server
	$r=@imap_createmailbox($email_object->conn, imap_utf7_encode("{".config::IMAP_HOST."}Processed"));
	//	echo "</br>".$r."</br>"	 ; 
	//	print_r(imap_list($email_object->conn,"{".config::IMAP_HOST."}","*"));
	//	  $email_object->move($email['index'],"Processed");
	$email_object->move(1,"Processed");
}
$conn->close();

?>
