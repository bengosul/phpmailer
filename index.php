<?php
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
$mail->SMTPDebug=2;

//Send an email
$mail->setFrom ('osboxes@gmail.com');
$mail->addAddress (config::MY_ADDRESS);
$mail->addCC(config::MY_ADDRESS);
$mail->addReplyTo('replyto@example.com');
$mail->Subject = 'An email sent from PHP2';
$mail->Body = '<h1>External image</h1>'
	.'<img src="https://daveh.io/apple.png"</h2>'
	."\n"
	.'<h2>Embedded Image</h2>'
	.'<img src="cid:banana">';

$mail->addAttachment(dirname(__FILE__) . '/example.txt', 'awesometextfile.txt');
$mail->AddEmbeddedImage('banana.jpg','banana');

if($mail->send()){
	echo "<br>Mesage sent!";
}
else {
}
?>
