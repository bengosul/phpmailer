<?php

/**
 *start the timer
 */

$start_time = microtime(true);

// require '../vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

require '../vendor/autoload.php';
require '../configs/config.php';
require 'classes/queue.php';
// require '../vendor/php-amqplib/php-amqplib/PhpAmqpLib/Connection/AbstractConnection.php';
// require '../vendor/php-amqplib/php-amqplib/PhpAmqpLib/Connection/AMQPStreamConnection.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use MessagePack\Packer;





//$connection = new PhpAmqpLib\Connection\AMQPStreamConection('localhost',5672, 'guest', 'guest');
$connection = new AMQPStreamConection('localhost',5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('emails',false,false,false,false);




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


/* actually don't send it but use the queue
 *
 if($mail->send()){
	 echo "<br>Mesage sent!";
}
else  	{
	echo 'Mailer error: '. $mail->ErrorInfo;
	exit();
}
*
 */

$dir = __DIR__.'/queue/';
#$dir = '/store/queue/';
$queue = new Queue($dir);
if ($queue->push($mail) === false){
	echo 'Unalbe to queue email';
	exit();
}




//calculate time taken to execute the script
$end_time = microtime(true);
$time = number_format($end_time - $start_time, 5);

//return to index.php
header("Location: index.php?time=$time");
exit();


?>
