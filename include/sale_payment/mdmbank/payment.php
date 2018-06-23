<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?

$arQuery['AMOUNT']     = CSalePaySystemAction::GetParamValue("AMOUNT");
$arQuery['CURRENCY']   = CSalePaySystemAction::GetParamValue("CURRENCY");
$arQuery['ORDER']      = CSalePaySystemAction::GetParamValue("ORDER");
$arQuery['DESC']       = CSalePaySystemAction::GetParamValue("DESC");
$arQuery['MERCH_NAME'] = CSalePaySystemAction::GetParamValue("MERCH_NAME");
$arQuery['MERCH_URL']  = CSalePaySystemAction::GetParamValue("MERCH_URL");
$arQuery['MERCHANT']   = CSalePaySystemAction::GetParamValue("MERCHANT");
$arQuery['TERMINAL']   = CSalePaySystemAction::GetParamValue("TERMINAL");
$arQuery['EMAIL']      = CSalePaySystemAction::GetParamValue("EMAIL");
$arQuery['TRTYPE']     = CSalePaySystemAction::GetParamValue("TRTYPE");
$arQuery['COUNTRY']    = CSalePaySystemAction::GetParamValue("COUNTRY");
$arQuery['MERC_GMT']   = CSalePaySystemAction::GetParamValue("MERC_GMT");
//$arQuery['TIMESTAMP']  = CSalePaySystemAction::GetParamValue("TIMESTAMP");
$arQuery['TIMESTAMP']  = gmdate('YmdHis');
//$arQuery['BACKREF']    = CSalePaySystemAction::GetParamValue("BACKREF");
$arQuery['BACKREF']    = "https://santehsmart.ru/personal/order/{$arQuery['ORDER']}";

?>
<form method="POST" action="https://3ds.mdmbank.ru:443/cgi-bin/cgi_link">
<?
foreach($arQuery as $key=>$value){?>
    <input type="hidden" name="<?=$key?>" value="<?=$value?>">
    <?}
?>
    <input style="font-size:25px;font-weight:900" type="submit" value="Перейти к оплате">
</form>
