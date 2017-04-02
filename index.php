<?php
require '../vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
require '../classes/config.php';
$mail = new PHPMailer();
echo get_class($mail);
echo "<p>Hello World</p></br>";

echo "<script src='https://code.jquery.com/jquery-2.2.3.min.js'></script>";
echo "<script>
	$(document).ready(function() {
		$('#emailForm').on('submit', function(){
			$('#sendButton').prop('disabled', true);
			$('#progressImage').show();
	})

});
</script>
";



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



echo isset($_GET['time']) ? $_GET['time'] : '';
echo "<form action='send.php' method='post' id='emailForm'> 
	<button type='submit' id='sendButton'>Send</button>
	<img src='loading.gif' width='16' height='16' id='progressImage' class='hidden' />
      </form> 

 ";

/*

if($mail->send()){
	echo "<br>Mesage sent!<br>";
}
else {
}
*/
?>
