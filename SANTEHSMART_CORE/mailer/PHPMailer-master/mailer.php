<?
require "/var/www/west/data/www/partner.santehsmart.ru/bitrix/php_interface/SANTEHSMART_CORE/mailer/PHPMailer-master/src/PHPMailer.php";
require "/var/www/west/data/www/partner.santehsmart.ru/bitrix/php_interface/SANTEHSMART_CORE/mailer/PHPMailer-master/src/OAuth.php";
require "/var/www/west/data/www/partner.santehsmart.ru/bitrix/php_interface/SANTEHSMART_CORE/mailer/PHPMailer-master/src/Exception.php";
require "/var/www/west/data/www/partner.santehsmart.ru/bitrix/php_interface/SANTEHSMART_CORE/mailer/PHPMailer-master/src/POP3.php";
require "/var/www/west/data/www/partner.santehsmart.ru/bitrix/php_interface/SANTEHSMART_CORE/mailer/PHPMailer-master/src/SMTP.php";


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
//
//$mail->DKIM_domain = 'example.com';
//$mail->DKIM_private = '/path/to/my/private.key';
//$mail->DKIM_selector = 'phpmailer';
//$mail->DKIM_passphrase = '';
//$mail->DKIM_identity = $mail->From;

$email = new PHPMailer();
$email -> CharSet = 'UTF-8';
//$email->IsSMTP();
$email->From      = 'dropshipping@santehsmart.ru';
$email->FromName  = 'Santehsmart Dropshipping';
$email->Subject   = '';

$email->Body      =  ' ';
$email->AddAddress( 'solo-webworks@mail.ru' );
$email->IsHTML(true);
$file_to_attach = '/var/www/west/data/INOUT/resources/DS_ORDERS/'.$doc.'.pdf';

$email->AddAttachment( $file_to_attach,$doc.'.pdf');


//$email->Send();
class StsMailer {

}