<?php

/**
*start the timer
*/

$start_time = microtime(true);

require '../vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
require '../classes/config.php';
$mail = new PHPMailer();
echo get_class($mail);
echo "<p>Hello World";

//configure PHPMailer with the SMTP Server
$mail->isSMTP();
$mail->Host = config::SMTP_HOST;
$mail->Port = config::SMTP_PORT;
$mail->SMTPAuth = true;
$mail->Username = config::SMTP_USER;
$mail->Password = config::SMTP_PASSWORD;
$mail->SMTPSecure = 'ssl';
$mail->CharSet = 'UTF-8';
$mail->isHTML(true);

//Enable debug
// $mail->SMTPDebug=2;

//Send an email
$mail->setFrom ('osboxes@gmail.com');
$mail->addAddress (config::MY_ADDRESS);
$mail->addCC(config::MY_ADDRESS);
$mail->addReplyTo('replyto@example.com');
$mail->Subject = 'An email sent from PHP3';
$mail->Body = '<h1>External image</h1>
	<img src="https://daveh.io/apple.png"</h2>
	
	<h2>Embedded Image</h2>
	<img src="cid:banana">  

	
	<h1 style="font-style: italic;">Hello italic</h1>

	<p style="color: #f00;">This is an email with some <span style="color: #0f0">CSS styles</span></p>;
';

$mail->AltBody = "Hello.\nThis is the alternative text body";

$mail->addAttachment(dirname(__FILE__) . '/example.txt', 'awesometextfile.txt');
$mail->AddEmbeddedImage('banana.jpg','banana');





if($mail->send()){
	echo "<br>Mesage sent!";
}
else  	{
	echo 'Mailer error: '. $mail->ErrorInfo;
	exit();
}

//calculate time taken to execute the script
$end_time = microtime(true);
$time = number_format($end_time - $start_time, 5);

//return to index.php
header("Location: index.php?time=$time");
exit();


?>
