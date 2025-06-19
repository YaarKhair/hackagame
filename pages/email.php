<?php
require_once('PHPMailer/class.phpmailer.php');
//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

$mail             = new PHPMailer();

$body             = 'This is a test\nDoes it work?';
$body             = eregi_replace("[\]",'',$body);

$mail->IsSMTP(); // telling the class to use SMTP
$mail->Host       = $smtpserver; // SMTP server
$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
                                           // 1 = errors and messages
                                           // 2 = messages only
$mail->SMTPAuth   = true;                  // enable SMTP authentication
$mail->Host       = $smtpserver; // sets the SMTP server
$mail->Port       = $smtpport;                    // set the SMTP port for the GMAIL server
$mail->Username   = $smtpuser; // SMTP account username
$mail->Password   = $smtppass;        // SMTP account password

$mail->SetFrom($gameemail, 'HF Accounts');

$mail->AddReplyTo($gameemail,"HF Accounts");

$mail->Subject    = "Verify your HF account.";

$mail->AltBody    = $body; // optional, comment out and test

$mail->MsgHTML($body);

$address = "chaozznl@chaozz.nl";
$mail->AddAddress($address, "HF user");

$mail->AddAttachment("images/phpmailer.gif");      // attachment
$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}
?>