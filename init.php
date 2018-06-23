<?
use Bitrix\Main,
    Bitrix\Main\Context,
    Bitrix\Sale\Order,
    Bitrix\Sale\Basket,
    Bitrix\Sale\Delivery,
    Bitrix\Sale\PaySystem;

/**
 * регистрация ивента двойного заказа
 */
Main\EventManager::getInstance()->addEventHandler(
    'sale',
    'OnSaleOrderSaved',
    'onSaleOrderSaved'
);
	//AddEventHandler("sale",'OnBeforeOrderAdd','gogo');
//	AddEventHandler("sale",'OnOrderNewSendEmail','customOnOrderSend1C');
    AddEventHandler("sale",'OnOrderNewSendEmail','OnOrderNewSendEmailHandler');
    AddEventHandler("sale",'OnOrderStatusSendEmail','OnOrderStatusSendEmailHandler');
    AddEventHandler("sale",'OnOrderDeliverSendEmail','OnOrderDeliverSendEmailHandler');
    AddEventHandler("catalog",'OnGetOptimalPrice','customOnGetOptimalPrice');
    AddEventHandler("sale", "OnSaleComponentOrderOneStepPersonType", "selectSavedPersonType");
    AddEventHandler('sale', 'OnBuildAccountNumberTemplateList', 'OnBuildAccountNumberTemplateListHandler');
    AddEventHandler("sale", "OnBeforeOrderAccountNumberSet", "OnBeforeOrderAccountNumberSetHandler");
    AddEventHandler("catalog",'OnBeforePriceUpdate','customOnBeforePriceUpdate');
    AddEventHandler("main", "OnAfterUserLogin", "OnAfterUserLoginHandler");
	
	/*function gogo(){
		$message=serialize($arFields);
		mail('ove-shop@mail.ru', 'the subject', 'tyu', null);
	}*/
	//КЛАСС ДЛЯ ПЕРЕДАЧИ ДАННЫХ МЕЖДУ КОМПОНЕНТАМИ
	Class GarbageStorage{
		private static $storage = array();
		public static function set($name, $value){ self::$storage[$name] = $value;}
		public static function get($name){ return self::$storage[$name];}
	}

    function isPartnerClient(){
        static $partner_ID = false;
        static $second = false;

        if(!$second){
            $second = true;
            global $USER;
            $arGroups = $USER->GetUserGroupArray();
            $userGroupsString = implode(" | ", $arGroups);
            $filter = Array("ID" => $userGroupsString, "ACTIVE" => "Y");
            $rsGroups = CGroup::GetList(($by = "c_sort"), ($order = "desc"), $filter);
            //if($rsGroups->is_filtered){
                while ($get = $rsGroups->GetNext()) {
                    if (substr($get['STRING_ID'], 0, 8) == 'partner_') {
                        $partner_ID = str_replace('partner_', '', $get['STRING_ID']);
                        break;
                    }
                }
            //}
        }

        return $partner_ID;
    } //MOVE TO PARTNER SINGLETON AS STATIC METHOD

    function getUnredNotices(){
        static $count = '';

        if($partner_ID = isPartnerClient()) {
            include_once $_SERVER["DOCUMENT_ROOT"] . '/testzone/util/platform.php';
            include_once getPlatformPath();

            $pl = new Platform();
            $count = $pl->getUnredNoticesByPartnerID($partner_ID);
            if($count) $count = '+'.$count;
            else $count = '';
        }

        return $count;
    }//MOVE TO PARTNER SINGLETON AS STATIC METHOD

    function OnOrderNewSendEmailHandler($orderID, &$eventName, &$arFields){

        $order = Order::load($orderID);
        $personType = $order->getPersonTypeId();
        $propertyCollection = $order->getPropertyCollection();
        $somePropValue = $propertyCollection->getItemByOrderPropertyId($personType == 1 ? 70 : 73); //$bill1CPropValue
        $bill1C = $somePropValue->getValue();
        if($bill1C) $arFields["ORDER_ID"] = $bill1C;

	    $somePropValue = $propertyCollection->getItemByOrderPropertyId(85);
        $pvzArr = explode('|',$somePropValue->getValue());
        if(isset($pvzArr[1])) {
            $arFields["PVZ"] = 'Пункт выдачи:' . $pvzArr[1] . ', </br>'
                          . $pvzArr[2];
        }

    }
    const CARD_PAY_SYSTEM_ID = 23;
    function OnOrderStatusSendEmailHandler($orderID, &$eventName, &$arFields, $val){

        $order = Order::load($orderID);
	    $propertyCollection = $order->getPropertyCollection();
        $personType = $order->getPersonTypeId();
	    $somePropValue = $propertyCollection->getItemByOrderPropertyId(76); //$bill1CPropValue
	    $franchiseID = $somePropValue->getValue();
	    $partnerEmail = Logistic::getEmailByPartnerId($franchiseID);
        $sender = new stsInformer('ap132625','879444');
	    $somePropValue = $propertyCollection->getItemByOrderPropertyId(3); //$bill1CPropValue
	    $phone = $somePropValue->getValue();
	    $somePropValue = $propertyCollection->getItemByOrderPropertyId($personType == 1 ? 70 : 73); //$bill1CPropValue
	    $bill1C = $somePropValue->getValue();
	    $isCard = false;
	    $paymentIds = $order->getPaymentSystemId();
	    foreach ($paymentIds as $paymentId){
		    if($paymentId == CARD_PAY_SYSTEM_ID) $isCard = true;
	    }
	    $somePropValue = $propertyCollection->getItemByOrderPropertyId(85);
	    $pvzArr = explode('|', $somePropValue->getValue());
	    $arFields["PVZ"] = $pvzArr[0] . ', ' . $pvzArr[1] . ', </br> Телефон пункта выдачи:' . $pvzArr[2];

	    if($val == 'X'){ // ON ORDER CONFIRM

            $sender->sendDropshippingConfirmed($bill1C,$arFields['ORDER_DATE'],$partnerEmail);

	        $toPay = ($isCard)? "" : "К оплате:".$order->getPrice()."р.";

            $message = array(
            array(
                "id" => "1",
                "phone"=> $phone,
                "text"=> 'Ваш заказ N '.$bill1C.' подтвержден и будет отправлен в пвз: '
                    . str_replace("<br>"," ", $pvzArr[1]) .'. '. $pvzArr[2].' '.$toPay,
                "sender"=> "OKNOVEVROPU"

            ));
            $sender->sendSms($message);

        }

        if($val == 'FD') { // ON SHIPMENT READY

            $sender->sendDropshippingDocumentMail($bill1C,$isCard ? 'ТЧ' : 'ТОРГ12', $arFields['ORDER_DATE'],$partnerEmail);

        }
    }


    function OnOrderDeliverSendEmailHandler($orderID, &$eventName, &$arFields){
        $order = Order::load($orderID);
        $arFields["DATE_ALLOW_DELIVERY"] = $order->getField("DATE_ALLOW_DELIVERY");
    }

/**
 * ивент двойного заказа
 */
function onSaleOrderSaved(Main\Event $event){
    /** @var \Bitrix\Sale\Order $order */
    global $USER;
    $order = $event->getParameter("ENTITY");
    //$oldValues = $event->getParameter("VALUES");
    $isNew = $event->getParameter("IS_NEW");
try {
    if ($isNew) {
        if(isPartnerClient()) {
            $siteId = Context::getCurrent()->getSite();
            $props = $order->getPropertyCollection();
            $account_number1 = $props->getItemByOrderPropertyId(73)->getValue();
            $account_number2 = $props->getItemByOrderPropertyId(74)->getValue();
            $bill_cut = $props->getItemByOrderPropertyId(75)->getValue();

            if ($account_number2 != '' && $bill_cut != '') {//проверка по наличию второго номера счета
                $newOrder = Order::create($siteId, $USER->isAuthorized() ? $USER->GetID() : 1);
                $newOrder->setPersonTypeId(2);
                $newOrder->setField('CURRENCY', 'RUB');
                $comment = $order->getField('USER_DESCRIPTION');
                if ($comment) {
                    $newOrder->setField('USER_DESCRIPTION', $comment);
                }
                $order->setField('ACCOUNT_NUMBER', $account_number1);
                $order->save();
                $newOrder->setField('ACCOUNT_NUMBER', $account_number2);

                $bill_cut = explode(';', $bill_cut);
                $arBillCutter = array();
                foreach ($bill_cut as $b_data) {
                    $b_data = explode(':', $b_data);
                    $arBillCutter[$b_data[0]] = abs((intval($b_data[1]) < 0) ? intval($b_data[1]) : 0);
                }

                $basket = Basket::create($siteId);
                $basketOld = $order->getBasket();
                $basketItems = $basketOld->getBasketItems();
                foreach ($basketItems as $basketItem) {
                    $item_xml_id = $basketItem->getField('PRODUCT_XML_ID');
                    $item_quantity = isset($arBillCutter[$item_xml_id]) ? $arBillCutter[$item_xml_id] : 0;
                    if ($item_quantity) {
                        $item = $basket->createItem('catalog', $basketItem->getProductId());
                        $item->setFields(array(
                            'QUANTITY' => $item_quantity,
                            'PRICE' => $basketItem->getPrice(),
                            'NAME' => $basketItem->getField('NAME'),
                            'CATALOG_XML_ID' => $basketItem->getField('CATALOG_XML_ID'),
                            'PRODUCT_XML_ID' => $item_xml_id,
                            'CURRENCY' => 'RUB',
                            'LID' => $siteId,
                            'PRODUCT_PROVIDER_CLASS' => '\CCatalogProductProvider',
                        ));
                    }
                }
                $newOrder->setBasket($basket);

                foreach ($basketItems as $basketItem) {
                    $item_xml_id = $basketItem->getField('PRODUCT_XML_ID');
                    $item_quantity = $basketItem->getQuantity();
                    $cut_quantity = isset($arBillCutter[$item_xml_id]) ? $arBillCutter[$item_xml_id] : 0;
                    if ($item_quantity - $cut_quantity) {
                        $item = $basketOld->createItem('catalog', $basketItem->getProductId());
                        $item->setFields(array(
                            'QUANTITY' => $item_quantity - $cut_quantity,
                            'PRICE' => $basketItem->getPrice(),
                            'NAME' => $basketItem->getField('NAME'),
                            'CATALOG_XML_ID' => $basketItem->getField('CATALOG_XML_ID'),
                            'PRODUCT_XML_ID' => $item_xml_id,
                            'CURRENCY' => 'RUB',
                            'LID' => $siteId,
                            'PRODUCT_PROVIDER_CLASS' => '\CCatalogProductProvider',
                        ));
                    }
                    $basketItem->delete();
                }
                $basketOld->save();
                $order->save();

                $shipmentCollection = $newOrder->getShipmentCollection();
                $shipment = $shipmentCollection->createItem();
                $service = Delivery\Services\Manager::getById(Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId());
                $shipment->setFields(array(
                    'DELIVERY_ID' => $service['ID'],
                    'DELIVERY_NAME' => $service['NAME'],
                ));
                $shipmentItemCollection = $shipment->getShipmentItemCollection();
                $basketItems = $basket->getBasketItems();
                foreach ($basketItems as $basketItem) {
                    $shipmentItem = $shipmentItemCollection->createItem($basketItem);
                    $shipmentItem->setQuantity($basketItem->getQuantity());
                }

                $paymentCollection = $newOrder->getPaymentCollection();
                $payment = $paymentCollection->createItem();
                $paySystemService = PaySystem\Manager::getObjectById(1);
                $payment->setFields(array(
                    'PAY_SYSTEM_ID' => $paySystemService->getField("PAY_SYSTEM_ID"),
                    'PAY_SYSTEM_NAME' => $paySystemService->getField("NAME"),
                ));

                /*$propertyCollection = $newOrder->getPropertyCollection();
                $phoneProp = $propertyCollection->getItemByOrderPropertyId(3);//phone
                $phoneProp->setValue('79507581367');
                $nameProp = $propertyCollection->getItemByOrderPropertyId(1);//payer name
                $nameProp->setValue('testovich');
                $order_copy = $propertyCollection->getItemByOrderPropertyId(74);//order copy
                $order_copy->setValue('Y');*/

                /*$shipmentCollection = $newOrder->getShipmentCollection();
                $shipmentCollection->load($order);

                $paymentCollection = $newOrder->getPaymentCollection();
                $paymentCollection->load($order);*/

                $propertyCollection = $newOrder->getPropertyCollection();
                $oldProps = $order->getPropertyCollection()->getArray();
                foreach ($oldProps["properties"] as $p) {
                    $p_id = intval($p["ID"]);
                    if (isset($p["VALUE"][0]) && $p_id != 73) { //без первого номера
                        $property = $propertyCollection->getItemByOrderPropertyId(($p_id == 74) ? 73 : $p_id); //замена второго номера первым
                        $property->setValue($p["VALUE"][0]);
                    }
                }

                /*********/
                /*$newOrder = $order->createClone();

                $basket = $newOrder->getBasket();
                $item = $basket->createItem('catalog', 126253);
                $item->setFields(array(
                    'QUANTITY' => 1,
                    'CURRENCY' => 'RUB',
                    'LID' => $siteId,
                    'PRODUCT_PROVIDER_CLASS' => '\CCatalogProductProvider',
                ));
                $newOrder->setId(intval($newOrder->getId())+1);*/

                $newOrder->doFinalAction(true);
                $result = $newOrder->save();
                if ($result) $order = $newOrder;
                //$_SESSION['ONSALEDATA'] = $basketList;
            }
        }else{ // franchise orders and normal orders!!!
            Partner::setPartnerOrderProps($order);
            Partner::customOnOrderSend1C($order);
        }
    }
}catch (\Bitrix\Main\NotSupportedException $e){
    $_SESSION['ONSALEDATA'] = 'NotSupportedException: '.$e->getTraceAsString();
}catch (\Bitrix\Main\ObjectNotFoundException $e){
    $_SESSION['ONSALEDATA'] = 'ObjectNotFoundException: '.$e->getTraceAsString();
}catch (Exception $e){
    $_SESSION['ONSALEDATA'] = 'Exception: '.$e->getTraceAsString();
}

    $result = new Main\EventResult( Main\EventResult::SUCCESS, array("ENTITY", $order) );
    $event->addResult($result);
    return $result;
}
function OnBuildAccountNumberTemplateListHandler(){
    return array('CODE'=>'an_partner','NAME'=>'Номер заказа для партнера');
}
function OnBeforeOrderAccountNumberSetHandler($ID,$type){
    if($type=='an_partner'){
        if(CModule::IncludeModule('sale') && $ID>0) {
            $order_num = "11$ID";
            if(isPartnerClient()){ // сделать для всех, у кого есть номер счета в свойстве заказа
                if(isset($_SESSION['EXTRA_BILL_NUMBER_1C']))
                    $order_num = $_SESSION['EXTRA_BILL_NUMBER_1C'];
            }
            return $order_num;
        }
    }
    return false;
}
function OnAfterUserLoginHandler(&$fields)
{
    global $USER;
    if ($partner_ID = isPartnerClient()) {
        if(!isset($_SESSION["PARTNER_BALANCE"]) || !isset($_SESSION["PARTNER_BALANCE"][$partner_ID])) {
            if ($fields['USER_ID'] > 0 && $fields['USER_ID'] == $USER->GetID()) {
                include_once $_SERVER["DOCUMENT_ROOT"] . '/testzone/util/platform.php';
                include_once getPlatformPath();

                $pl = new Platform();
                $_SESSION["PARTNER_BALANCE"][$partner_ID] = $pl->getPartnerBalans($partner_ID);
            }
        }
    }
}

function customOnBeforePriceUpdate($ID, &$arFields){
    if($arFields["CATALOG_GROUP_ID"] == 1) { // обновляем только BASE - это для Маркета!
        include_once $_SERVER["DOCUMENT_ROOT"] . '/testzone/util/platform.php';
        include_once getPlatformPath();

        $pl = new Platform();
        $pl->editPriceInUML($arFields["PRODUCT_ID"], $arFields["PRICE"]);
    }

    return true;
}

function selectSavedPersonType(&$arResult, &$arUserResult, $arParams)
{
    global $USER;
    if($USER->IsAuthorized())
    {
        $rsUser = $USER->GetByID($USER->GetID());
        $arUser = $rsUser->Fetch();
        $entity = $arUser['UF_PARTNER_XML_ID']; //поле принадлежности к юр. лицу

        $personType = 0;
        if ($entity) {
            $personType = 2;
        } else {
            $personType = 1;
        }
        //очищаем текущее значение типа плательщика
        foreach($arResult['PERSON_TYPE'] as $key => $type){
            if($type['CHECKED'] == 'Y'){
                unset($arResult['PERSON_TYPE'][$key]['CHECKED']);
            }
        }
        //устанавливаем новое значение типа плательщика
        $arResult['PERSON_TYPE'][$personType]['CHECKED'] = 'Y';
        $arUserResult['PERSON_TYPE_ID'] = $personType;
    }
}
	
function customOnGetOptimalPrice($intProductID,$quantity,$arUserGroups,$renewal,$arPrices){
    $iblock_id=10;
    if(!empty($intProductID)&&$_SESSION['TF_LOCATION_SELECTED_CITY_NAME']=='Краснодар') {
        //определение и установка нужной цены
        $arSelect = array(
            'IBLOCK_ID',
            'ID',
            'NAME',
            'CATALOG_GROUP_1'
        );
        $arFilter = array(
            'IBLOCK_ID' => $iblock_id,
            'ID' => $intProductID
        );
        $res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
        $element = $res->GetNext();
        $price = $element['CATALOG_PRICE_1'];
        
        $db_props = CIBlockElement::GetProperty($iblock_id, $intProductID, array("sort" => "asc"), Array("CODE"=>"vendor"));
        if($ar_props = $db_props->Fetch())
            $vendor = $ar_props["VALUE"];
        else $vendor = '';
        if($vendor=='Акватон')$price = $element['CATALOG_PRICE_1']*1.07;
        //если задан купон скидки
        if(!empty($arDiscountCoupons)) {
            $arSelectCoup = array('DISCOUNT_ID');
            $arFilterCoup = array(
                'COUPON' => $arDiscountCoupons[0]
            );
            $recCoup = CCatalogDiscountCoupon::GetList(
                array(),
                $arFilterCoup,
                false,
                false,
                $arSelectCoup
            );
            $coupon = $recCoup->GetNext();
            $discount = CCatalogDiscount::GetByID($coupon['DISCOUNT_ID']);
        }
        return array(
            'PRICE' => array(
                'CATALOG_GROUP_ID' => 1,
                'PRICE' => $price,
                'CURRENCY' => "RUB",
            ),
            'DISCOUNT_LIST' => array(
                array(
                    'VALUE_TYPE' => $discount['VALUE_TYPE'],
                    'VALUE' => $discount['VALUE'],
                    'CURRENCY' => $discount['CURRENCY']
                )
            )

        );

    }
    return true;
}

//function customOnOrderSend1C($ordId){
//    include_once $_SERVER["DOCUMENT_ROOT"] . '/testzone/util/platform.php';
//    include_once getPlatformPath();
//
//    $pl = new Platform();
//    $bill_request = $pl->makeRetailBillRequest($ordId);
//    $pl->makeTestBill($bill_request);
//}
//////YANDEX_ADV//////////
if(isset($_GET['yd_cid'])&&isset($_GET['yd_gid'])&&isset($_GET['yd_aid'])){
    $cookie_param=implode(';',array($_GET['yd_cid'],$_GET['yd_gid'],$_GET['yd_aid']));
    global $APPLICATION;
    $APPLICATION->set_cookie("STS_ADC", $cookie_param, time()+60*60*24*365);
}
//////ARNIKA_ADV//////////
if(isset($_GET['utm_source'])&&isset($_GET['utm_medium'])&&isset($_GET['utm_campaign'])&&isset($_GET['utm_content'])&&isset($_GET['utm_term'])){
    $cookie_param=implode(';',array($_GET['utm_source'],$_GET['utm_medium'],$_GET['utm_campaign'],$_GET['utm_content'],$_GET['utm_term']));
    global $APPLICATION;
    $APPLICATION->set_cookie("STS_ARN", $cookie_param, time()+60*60*24*365);
}
function pre($a){
    echo "<pre>";
        print_r($a);
    echo "</pre>";
}
//////GEO_PRICE/////////////
require_once 'init_geo.php';

require_once('/var/www/west/data/www/santehsmart.ru/punkty-vydachi/pvzwidget/scripts/service.php');
require_once('include/Logistic/logisticClass.php');
////////////////////////////
//////SANTEHSMART_CORE//////
include_once('SANTEHSMART_CORE/MOD.php');
include_once('SANTEHSMART_CORE/Partner.php');
require_once 'SANTEHSMART_CORE/StsInformer/StsInformer.php';
?>
