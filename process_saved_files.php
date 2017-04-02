<?php

if(php_sapi_name()!="cli"){
	echo '<html><body bgcolor="#000000" text="white"><pre>';
}

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
if(PHP_SAPI!='cli'){echo "<br>";}else{echo "\n";}

// Check items that need processing 
$sql = "SELECT * from emails.processed_emails WHERE parsed = 0 and attachments>0";
if($result = $conn->query($sql))
{
	while($row = $result->fetch_assoc()) {
		$filepattern= "/store/".sprintf('%06d',$row['id']);
		// Retrieve filenames that need processing;
		$filenames=glob("$filepattern*.*");
		foreach ($filenames as $fn){
			echo "processing ".$fn;
			if(PHP_SAPI!='cli'){echo "<br>";}else{echo "\n";}

			//retrieve config:
			$inv_amt=0;
			//echo $row['from_address'];
			//	insert_break();
			$client_config=retrieve_config($row['from_address'],$conn);	
			echo $client_config['partner'];
		 insert_break();
			if ($client_config['partner']=='initial'){echo "config not found";continue;}

			//parse file


			$fh = fopen($fn,'r');
			while ($line = fgets($fh)) {
				// <... Do your work with the line ...>
				//				echo($line);
				//				echo $client_config['inv_no_str']. $client_config['partner'];
				$pos=strpos($line, $client_config['inv_no_str']);
				if($pos===false) 
				{continue;}	
				else {
					$inv_no_str= substr($line,$pos+strlen($client_config['inv_no_str']),strlen($line)-strlen($client_config['inv_no_str'])-1) ;
					//		$inv_no_str= preg_replace("/\r\n|\r|\n/", ' ', $inv_no_str);
					//					insert_break();
					//		echo strlen($line);	

					continue;							
				}
			}
			fclose($fh);
		}
		//Update retrieved data
		if($inv_no_str){

			$sql = "UPDATE `emails`.`processed_emails`
				SET parsed =CURRENT_TIMESTAMP,
					invoice_number='$inv_no_str' 
						WHERE id =".$row['id'];
			//	echo $sql;
			$res=$conn->query($sql) or die($conn->error);

			echo "updated";
			insert_break();	
		}
		else {
			insert_break();
			echo "parse fail";
			insert_break();
		}
	}

	$conn->close();
}

function retrieve_config($from_address,$connection){
	$array = array(
			"from_address"=> "",
			"partner"=> "initial",
			"inv_no_str" => ""

			);

	$sql = "SELECT * from emails.match_config WHERE email ='$from_address'";
	//	echo $sql;
	insert_break();
	if($result = $connection->query($sql))
	{
		echo "found something in config: ";
		$row =$result->fetch_assoc();

		$array['partner']=$row['partner'];
		$array['inv_no_str']=$row['inv_no_str'];
	}
	else {
		echo $connection->error; 
	}

	return $array;
}

function insert_break(){	
	if(PHP_SAPI!='cli'){$brstr= "<br>";}else{$brstr= "\n";}
	echo $brstr;
}


//$conn->close();
?>
