<?php

if(php_sapi_name()!="cli"){
	echo '<html><body bgcolor="#000000" text="white"><pre>';
}

require_once '../classes/config.php';
require_once 'functions/general_functions.php';

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
		
		//only while testing
		$sql = "UPDATE `emails`.`processed_emails`
				SET parsed=0";
		$res=$conn->query($sql) or die($conn->error);


// Check items that need processing, this query can be manipulated to only process some selection 
$sql = "SELECT * from emails.processed_emails WHERE parsed = 0 and attachments>0";

if($result = $conn->query($sql))
{
	if($result->num_rows==0){echo "No new emails"; insert_break();}
	$processedrow=1;
	while($row = $result->fetch_assoc()) {
	
		//mark email as processed - ! not good when one attachment is parsed and the other throws an error
		$sql = "UPDATE `emails`.`processed_emails`
				SET parsed =CURRENT_TIMESTAMP 
				WHERE id =".$row['id'];
		//echo "line 79 sql: ". $sql;
		$res=$conn->query($sql) or die($conn->error);

		//retrieve config:
		$client_config=retrieve_config($row['from_address'],$conn);
		if(!isset($client_config)){echo "Processed row ".$processedrow." of ".$result->num_rows ; insert_break(); $processedrow++; continue;}
			
		echo "For ".$row['from_address']." found Partner: ".$client_config['partner'];
		insert_break();
		//if ($client_config['partner']=='initial'){echo "config not found";continue;}

		// Retrieve filenames that need processing;
		$filepattern= "/store/".sprintf('%06d',$row['id']);
		$filenames=glob("$filepattern*.*");
		$processedfilename=1;
		foreach ($filenames as $fn){

			//reset values
			$invoice_number ="";
			
			//check if it's PDF
			if (substr($fn,-3)=="pdf") {
				prepare_pdf($fn);
				$fn=str_replace(".pdf",".txt",$fn);
				$fn=str_replace("/store/","/store/temp/",$fn);
				}
			
			echo "Processing row ".$processedrow." of ".$result->num_rows."; file# ".$processedfilename." of ".count($filenames).": ".$fn;

			//parse file
			$fh = fopen("$fn",'r');
			while ($line = fgets($fh)) {
				// <... Do your work with the line ...>
			// 		echo($line);
			//					echo $client_config['inv_no_str']. $client_config['partner'];
				$pos=strpos($line, $client_config['inv_no_str']);
				if($pos===false) 
				{continue;}	
				else {
					$invoice_number= substr($line,$pos+strlen($client_config['inv_no_str']),strlen($line)-strlen($client_config['inv_no_str'])-1) ;
					//		$inv_no_str= preg_replace("/\r\n|\r|\n/", ' ', $inv_no_str);
					//					insert_break();
					//		echo "futere:". strlen($line);	

					break;							
				}
			}
			fclose($fh);
		
			//Update retrieved data
			if(strlen($invoice_number)<100){
				//echo "inv no length: ".strlen($inv_no_str);
/*				$sql = "UPDATE `emails`.`processed_emails`
						SET parsed =CURRENT_TIMESTAMP,
						invoice_number='$inv_no_str' 
							WHERE id =".$row['id'];
				//echo "line 79 sql: ". $sql;
*/
				$sql = "DELETE FROM `emails`.`processed_attachments`
						WHERE id_email =".$row['id']."
						AND id_attachment =".$processedfilename;
			
				$res=$conn->query($sql) or die($conn->error);

				$sql = "INSERT `emails`.`processed_attachments` (id_email, id_attachment, invoice_number)
						VALUES (".$row['id'].",".$processedfilename.",'$invoice_number')";
			
				$res=$conn->query($sql) or die($conn->error);
				insert_break();
				echo "updated";
				insert_break(); insert_break();
			}
			else {
				insert_break();
				echo "inv_no_str parse fail";
				//add this message to the table too
				insert_break();
			}
			$processedfilename++;
		}
	
		$processedrow++;
	}

	$conn->close();
}
else {echo "nothing new LE: or rather some db resultset error";}

//
//FUNCTIONS
//

function retrieve_config($from_address,$connection){
	$array = array(
			"from_address"=> "",
			"partner"=> "initial",
			"inv_no_str" => ""
			);

	$sql = "SELECT * from emails.match_config WHERE email ='$from_address'";
	//	echo $sql;
	insert_break();
	if($result_config = $connection->query($sql))
	{
		if($result_config->num_rows==0){echo "Partner not found for: ".$from_address ;insert_break();return;}
		
		echo "found something in config: ";
		$row =$result_config->fetch_assoc();

		$array['partner']=$row['partner'];
		$array['inv_no_str']=$row['inv_no_str'];
	}
	else {
		echo $connection->error; 
	}

	return $array;
}


function prepare_pdf($fn){
	echo "Preparing PDF ".$fn;
	insert_break();
	echo exec("qpdf --decrypt \"".$fn."\"  \"" .str_replace(".pdf",".pdf", str_replace("/store/","/store/temp/",$fn)) . "\""  );
	echo exec("pdftotext \"".str_replace("/store/","/store/temp/",$fn)."\"  \"" .str_replace(".pdf",".txt", str_replace("/store/","/store/temp/",$fn)) . "\""  );
	}

//$conn->close();
?>
