<?

require('SMSProstorAPI.php');
require "/var/www/west/data/www/partner.santehsmart.ru/bitrix/php_interface/SANTEHSMART_CORE/mailer/PHPMailer-master/src/PHPMailer.php";
require "/var/www/west/data/www/partner.santehsmart.ru/bitrix/php_interface/SANTEHSMART_CORE/mailer/PHPMailer-master/src/OAuth.php";
require "/var/www/west/data/www/partner.santehsmart.ru/bitrix/php_interface/SANTEHSMART_CORE/mailer/PHPMailer-master/src/Exception.php";
require "/var/www/west/data/www/partner.santehsmart.ru/bitrix/php_interface/SANTEHSMART_CORE/mailer/PHPMailer-master/src/POP3.php";
require "/var/www/west/data/www/partner.santehsmart.ru/bitrix/php_interface/SANTEHSMART_CORE/mailer/PHPMailer-master/src/SMTP.php";


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class   stsInformer {
    public $email;
    public $sms;
    function __construct($login,$pass){
       $this->sms = new prostorApi($login,$pass);
       $this->email = new PHPMailer();
       $this->email -> CharSet = 'UTF-8';
    }

    public function sendSms($messages){
        var_dump($this->sms->credits()); // узнаем текущий баланс
        var_dump($this->sms->senders()); // получаем список доступных подписей
		var_dump($this->sms->send($messages, 'testQueue'));
	}
    public function sendDropshippingConfirmed($doc,$date,$partnerEmail){

        $body= '<table class="outerTable" style="background-color: rgb(243, 243, 243); border-collapse: collapse; border-spacing: 0px; box-sizing: border-box; margin: 0px 0px 2.23125em; padding: 0px; width: 100%;"> 
  <tbody> 
    <tr style="box-sizing: border-box; margin: 0px; padding: 0px;"> <td style="box-sizing: border-box; margin: 0px; padding: 0.2125em 0.85em; border-image: initial;"> 
        <table class="email__content" data-qcontent="module__email" style="border: 1px solid rgb(218, 218, 218); background-color: rgb(255, 255, 255); border-collapse: collapse; border-spacing: 0px; box-sizing: border-box; margin: 0px auto 2.23125em; max-width: 800px; padding: 0px; width: 100%;"> 
          <tbody> 
            <tr><th class="email" style="background-color: rgb(255, 255, 255); border-bottom: 4px solid rgb(61, 37, 91); box-sizing: border-box; color: rgb(37, 22, 55); margin: 0px 0px 15px; padding: 20px; text-align: left;"> <img src="https://partner.santehsmart.ru/bitrix/templates/STS2/images/logo-ds.jpg" width="250" style="border-style: none; box-sizing: border-box; margin: 0; padding: 0; vertical-align: middle;"  /><span style="box-sizing: border-box; color: rgb(61, 37, 91); float: right; font-size: 25px; vertical-align: middle;">Новый заказ '.$doc.' </span></th> </tr>
        </tbody>
         
          <tbody style="border-bottom: 4px solid rgb(218, 218, 218); box-sizing: border-box; margin: 0px; padding: 0px;"> 
            <tr style="box-sizing: border-box; margin: 0px; padding: 0px;"> <td style="box-sizing: border-box; margin: 0px; padding: 0.2125em 0.85em; border-image: initial;"> 
                <h4 style="box-sizing: border-box; clear: both; color: rgb(37, 22, 55); font-size: 19.6071px; margin: 0px; padding: 50px 30px 10px;">Ваш пункт выдачи был выбран покупателем.</h4>
               </td> </tr>
           
            <tr style="box-sizing: border-box; margin: 0px; padding: 0px;"> <td style="box-sizing: border-box; margin: 0px; padding: 0.2125em 0.85em; border-image: initial;"> 
                <p style="box-sizing: border-box; font-size: 17px; margin: 0px; padding: 0px 35px 35px;">Заказ номер -'.$doc.' от '.$date.' был подтвержден  менеджером и ожидает отгрузку. </p>
               </td> </tr>
           
            <tr style="box-sizing: border-box; margin: 0px; padding: 0px;"> <td style="box-sizing: border-box; margin: 0px; padding: 0.2125em 0.85em; border-image: initial;"> 
                
               </td> </tr>
           
            <tr style="border-top: 3px solid rgb(61, 37, 91); box-sizing: border-box; margin: 0px; padding: 0px;"> <td style="box-sizing: border-box; margin: 0px; padding: 0.2125em 0.85em; border-image: initial;"> 
                <p style="box-sizing: border-box; font-size: 17px; margin: 0px; padding: 0px 35px 35px;">С уважением, 
                  <br style="box-sizing: border-box;" />
                 администрация Интернет-магазина
        <br style="box-sizing: border-box;" />
                 E-mail:dropshipping@santehsmart.ru</p>
               </td> </tr>
           </tbody>
         </table>
       </td> </tr>
   </tbody>
 </table>';

        $this->email->From      = 'dropshipping@santehsmart.ru';
        $this->email->FromName  = 'Santehsmart Dropshipping';
        $this->email->Subject   = 'Santehsmart Dropshipping: Новый заказ на ваш ПВЗ';

        $this->email->Body = $body;
        $this->email->AddAddress($partnerEmail);
        $this->email->IsHTML(true);
	    $file_to_attach = '/var/www/west/data/INOUT/resources/DS_ORDERS/'.$doc.'.pdf';
	    $this->email->AddAttachment( $file_to_attach,$doc.'.pdf');
        $this->email->Send();

    }
    public function sendDropshippingDocumentMail($doc,$postfix,$date,$partnerEmail){

        $dropshippingMailBody = '<table class="outerTable" style="background-color: rgb(243, 243, 243); border-collapse: collapse; border-spacing: 0px; box-sizing: border-box; margin: 0px 0px 2.23125em; padding: 0px; width: 100%;"> 
  <tbody> 
    <tr style="box-sizing: border-box; margin: 0px; padding: 0px;"> <td style="box-sizing: border-box; margin: 0px; padding: 0.2125em 0.85em; border-image: initial;"> 
        <table class="email__content" data-qcontent="module__email" style="border: 1px solid rgb(218, 218, 218); background-color: rgb(255, 255, 255); border-collapse: collapse; border-spacing: 0px; box-sizing: border-box; margin: 0px auto 2.23125em; max-width: 800px; padding: 0px; width: 100%;"> 
          <tbody> 
            <tr><th class="email" style="background-color: rgb(255, 255, 255); border-bottom: 4px solid rgb(61, 37, 91); box-sizing: border-box; color: rgb(37, 22, 55); margin: 0px 0px 15px; padding: 20px; text-align: left;"> <img src="https://partner.santehsmart.ru/bitrix/templates/STS2/images/logo-ds.jpg" width="250" style="border-style: none; box-sizing: border-box; margin: 0; padding: 0; vertical-align: middle;" height="41"  /><span style="box-sizing: border-box; color: rgb(61, 37, 91); float: right; font-size: 25px; vertical-align: middle;">Заказ  '.$doc.' отгружен</span></th> </tr>
           </tbody>
         
          <tbody style="border-bottom: 4px solid rgb(218, 218, 218); box-sizing: border-box; margin: 0px; padding: 0px;"> 
            <tr style="box-sizing: border-box; margin: 0px; padding: 0px;"> <td style="box-sizing: border-box; margin: 0px; padding: 0.2125em 0.85em; border-image: initial;"> 
                <h4 style="box-sizing: border-box; clear: both; color: rgb(37, 22, 55); font-size: 19.6071px; margin: 0px; padding: 50px 30px 10px;">Заказ номер -
               '.$doc.' от '.$date.'  был отгружен со склада и направлен в ваш пункт выдачи заказов. Документы во вложении.  </h4>
               </td> </tr>
           
            <tr style="box-sizing: border-box; margin: 0px; padding: 0px;"> <td style="box-sizing: border-box; margin: 0px; padding: 0.2125em 0.85em; border-image: initial;"></td> </tr>
           
            <tr style="box-sizing: border-box; margin: 0px; padding: 0px;"> <td style="box-sizing: border-box; margin: 0px; padding: 0.2125em 0.85em; border-image: initial;"></td> </tr>
           
            <tr style="border-top: 3px solid rgb(61, 37, 91); box-sizing: border-box; margin: 0px; padding: 0px;"> <td style="box-sizing: border-box; margin: 0px; padding: 0.2125em 0.85em; border-image: initial;"> 
                <p style="box-sizing: border-box; font-size: 17px; margin: 0px; padding: 0px 35px 35px;">С уважением, 
                  <br style="box-sizing: border-box;" />
                 администрация Интернет-магазина 
                  <br style="box-sizing: border-box;" />
                 E-mail:dropshipping@santehsmart.ru</p>
               </td> </tr>
           </tbody>
         </table>
       </td> </tr>
   </tbody>
 </table>';




        $this->email->From      = 'dropshipping@santehsmart.ru';
        $this->email->FromName  = 'Santehsmart Dropshipping';
        $this->email->Subject   = 'Santehsmart Dropshipping: Отгрузка заказа. Документы.';

        $this->email->Body      =  $dropshippingMailBody;
        $this->email->AddAddress($partnerEmail);
        $this->email->IsHTML(true);

        $file_to_attach = '/var/www/west/data/INOUT/resources/DS_ORDERS/'.$doc.'.pdf';
        $this->email->AddAttachment( $file_to_attach,$doc.'.pdf');

        $file_to_attach = '/var/www/west/data/INOUT/resources/DS_ORDERS/'.$doc.'_'.$postfix.'.pdf';
        $this->email->AddAttachment( $file_to_attach,$doc.'_'.$postfix.'.pdf');
       
        $this->email->Send();

    }

}


























//$snd = new stsInformer('ap132625','879444');

//$messages = array(
//	array(
//		"id" => "1",
//		"phone"=> "79601089972",
//		"text"=> "Заказ N 3456 HA00 отгружен и сегодня прибудет на Космонавтов 39,",
//		"sender"=> "OKNOVEVROPU"
//
//	));
