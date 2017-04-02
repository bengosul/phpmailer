<?php
require_once 'inbox_class.php';

$emails = New Email_reader();
//$emails->inbox();
//$total = count($emails->inbox);


$mails=$emails->output();

//echo count($mails);


foreach ($mails as $value){
	$subj= $value["index"].$value["header"]->subject;
	$from=  $value["index"].$value["header"]->fromaddress;
	$attachments= $value["index"].$value["header"]->subject;
	$received= $value["index"].$value["header"]->date;

	echo iconv_mime_decode($subj,0,"UTF-8");


	print_r($value["structure"]);

	echo "<br>\r\n";
		}

//for($i=$total-1;$i>=0;$i--) {
//	$email = $emails->inbox[$i];
//}

// echo $total

?>
