<?php

require dirname(__DIR__).'/vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
require dirname(__DIR__).'/configs/config.php';
require __DIR__.'/classes/queue.php';

$dir = __DIR__.'/queue/';
$queue = new Queue($dir);
$mail = $queue->getNextItem();

/*
$mail = new PHPMailer();
echo get_class($mail);
echo "<p>Hello World";
 */

while ($mail !== null) {

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
	$mail->Subject = 'An email sent from PHP3 queued';
/*
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
 */

	if($mail->send()){
		echo "\nMesage sent from queue!";
	}
	else  	{
		echo 'Mailer error: '. $mail->ErrorInfo;
	#	exit();
	}

	$mail = $queue->getNextItem();

}

//return to index.php
//header("Location: index.php?time=$time");
//exit();


?>
