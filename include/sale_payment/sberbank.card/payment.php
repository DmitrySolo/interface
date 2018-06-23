<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/money_mail.php"));
/*function PrepareParams(&$item)
{
	$item = rawurlencode($item);
}*/

$message = "";
$invoice_number="";
$formUrl="";
$error="";
$arParams = Array();
$ORDER_ID =(strlen(CSalePaySystemAction::GetParamValue("ORDER_ID")) > 0) ? CSalePaySystemAction::GetParamValue("ORDER_ID") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"];
$AMOUNT = (strlen(CSalePaySystemAction::GetParamValue("SHOULD_PAY")) > 0) ? CSalePaySystemAction::GetParamValue("SHOULD_PAY") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"];
$AMOUNT = round($AMOUNT,2)*100;
$ORDER = CSaleOrder::GetByID($ORDER_ID);

$db_vals = CSaleOrderPropsValue::GetList(array(), array("ORDER_ID" => $ORDER_ID,"CODE"=>'orderId'));
if ($arVals = $db_vals->Fetch()) $invoice_number=$arVals['VALUE'];
$db_vals = CSaleOrderPropsValue::GetList(array(), array("ORDER_ID" => $ORDER_ID,"CODE"=>'formUrl'));
if ($arVals = $db_vals->Fetch()) $formUrl=$arVals['VALUE'];  

//if (empty($invoice_number)){
	/*$SITE_NAME = COption::GetOptionString("main", "server_name", "");
	$dateInsert = (strlen(CSalePaySystemAction::GetParamValue("DATE_INSERT")) > 0) ? CSalePaySystemAction::GetParamValue("DATE_INSERT") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"];
	$arParams['issuer_id'] = base64_encode($ORDER_ID);
	$arParams['access_key'] = (strlen(CSalePaySystemAction::GetParamValue("KEY")) > 0) ? CSalePaySystemAction::GetParamValue("KEY") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["KEY"];
	$arParams['shouldPay'] = (strlen(CSalePaySystemAction::GetParamValue("SHOULD_PAY")) > 0) ? CSalePaySystemAction::GetParamValue("SHOULD_PAY") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"];
	$arParams['buyer_email'] = (strlen(CSalePaySystemAction::GetParamValue("BUYER_EMAIL")) > 0) ? CSalePaySystemAction::GetParamValue("BUYER_EMAIL") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"];
	//$arParams['currency'] = (strlen(CSalePaySystemAction::GetParamValue("CURRENCY")) > 0) ? CSalePaySystemAction::GetParamValue("CURRENCY") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"];
	if (strlen(CSalePaySystemAction::GetParamValue("TEST_MODE")))
		$arParams['currency'] = CSalePaySystemAction::GetParamValue("TEST_MODE");
	else
		$arParams['currency'] = "RUR";	

	$arParams['buyer_ip'] = $_SERVER['REMOTE_ADDR'];
    $arParams['description'] = base64_encode((ToUpper(SITE_CHARSET) != ToUpper('windows-1251')) ? $APPLICATION->ConvertCharset(GetMessage("MM_DESC",Array('#ORDER_ID#' => $ORDER_ID, '#DATE#' => $dateInsert, '#SITE_NAME#' => $SITE_NAME)), SITE_CHARSET, 'windows-1251') : GetMessage("MM_DESC", Array('#ORDER_ID#' => $ORDER_ID, '#DATE#' => $dateInsert, '#SITE_NAME#' => $SITE_NAME)));
	array_walk($arParams, 'PrepareParams');*/
	
	$exp_time=time()+60*60*24*2; //2 days
	$exp_date=date("Y-m-d",$exp_time).'T'.date("H:i:s",$exp_time);
	
	require $_SERVER['DOCUMENT_ROOT'].'/bitrix/sbrb_credentials.php';
	
	$arParams['amount']=$AMOUNT;
	$arParams['currency']='643';
	$arParams['language']='ru';
	$arParams['orderNumber']=$ORDER_ID;
	$arParams['password']=$credentials['pass'];
	$arParams['userName']=$credentials['user'];
	$arParams['returnUrl']='http://santehsmart.ru/store/finish.php';
	$arParams['pageView']='DESKTOP';
	$arParams['expirationDate']=$exp_date;

	$sHost ="securepayments.sberbank.ru";
	$sUrl  ="/payment/rest/register.do";
	$sVars ="amount=".$arParams['amount']."&currency=".$arParams['currency']."&language=".$arParams['language']."&orderNumber=".$arParams['orderNumber'];
	$sVars.="&password=".$arParams['password']."&userName=".$arParams['userName']."&returnUrl=".$arParams['returnUrl']."&pageView=".$arParams['pageView']."&expirationDate=".$arParams['expirationDate'];
	
	$ch=curl_init("https://$sHost$sUrl?$sVars");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	$get_data = curl_exec($ch);
	//var_dump($get_data);
	
	$props=json_decode($get_data,true);
	//var_dump($props);
	
	if(isset($props['orderId'])){
	    $invoice_number=$props['orderId'];
	    $formUrl=$props['formUrl'];
	    if(!isset($props['errorCode'])){
	        $props['errorCode']='';
	        $props['errorMessage']='';
	    }
	}
	if(isset($props['errorCode']))$error="Ошибка {$props['errorCode']}: {$props['errorMessage']}";
	
    foreach($props as $prop_code=>$prop_value) {
        $db_vals = CSaleOrderPropsValue::GetList(array(), array("ORDER_ID" => $ORDER_ID,"CODE"=>$prop_code));
        if ($arVals = $db_vals->Fetch()) {
            CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$prop_value));
        }else{
            $prop = CSaleOrderProps::GetList(array(), array("CODE" => $prop_code));
            $prop = $prop->Fetch();
            if($prop){
                $arFields = array(
                    "ORDER_ID" => $ORDER_ID,
                    "ORDER_PROPS_ID" => $prop["ID"],
                    "NAME" => $prop["NAME"],
                    "CODE" => $prop["CODE"],
                    "VALUE" => $prop_value
                );
                CSaleOrderPropsValue::Add($arFields);
            }
        }
    }
//}

if(empty($formUrl)&&!empty($invoice_number))$formUrl="";//for feauture url


if(empty($invoice_number)){?>
    <span>Номер заказа платежной системы не получен.</span><br>
    <?if(!empty($error))echo "$error<br>";?>
<?}else{?>
    <!--<span>Номер заказа платежной системы: <b><?=$invoice_number?></b></span><br>-->
    <a href="<?=$formUrl?>" target="_blank" style="font-size:22px">Оплата банковской картой</a><br>
<?}?>
