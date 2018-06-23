<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/money_mail.php"));

$psTitle = GetMessage("MM_TITLE");
$psDescription = GetMessage("MM_DESCRIPTION");

$arPSCorrespondence = array(
		"ORDER_ID" => array(
				"NAME" => GetMessage("MM_ORDER_ID"),
				"DESCR" => "",
				"VALUE" => "ID",
				"TYPE" => "ORDER"
			),
		"SHOULD_PAY" => array(
				"NAME" => GetMessage("MM_SHOULD_PAY"),
				"DESCR" => GetMessage("MM_SHOULD_PAY_DESC"),
				"VALUE" => "SHOULD_PAY",
				"TYPE" => "ORDER"
			),
);
?>
