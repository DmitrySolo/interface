<?php
use Bitrix\Sale;
use Bitrix\Main\Mail\Event;

class Partner{
    const PARTNER_HLBLOCK = 13;
    const FRANCHISE_HLBLOCK = 16;
    const BILL_TYPE_4_RATIO = 1.05;
    const IBLOCK_ID = 10;

    private static $partner_ID = null;
    private static $is_franchise = null;

    public static function getPartnerID(){
        static $second = false;

        if(!$second){
            $second = true;
            global $USER;
            $arGroups = $USER->GetUserGroupArray();
            $userGroupsString = implode(" | ", $arGroups);
            $filter = Array("ID" => $userGroupsString, "ACTIVE" => "Y");
            $rsGroups = CGroup::GetList(($by = "c_sort"), ($order = "desc"), $filter);
            while ($get = $rsGroups->GetNext()) {
                if (substr($get['STRING_ID'], 0, 8) == 'partner_') {
                    self::$partner_ID = str_replace('partner_', '', $get['STRING_ID']);
                    break;
                }
            }
        }

        return self::$partner_ID;
    }

    public static function isPartner(){
        return (self::getPartnerID() == null) ? false : true;
    }

    /**
     * @return null|bool
     */
    public static function isFranchise(){
        if (self::$is_franchise === null){
            if(self::isPartner()) {
                $hlFranchise = self::getHlDataClass(self::FRANCHISE_HLBLOCK);
                if ($hlFranchise != null) {
                    $result = $hlFranchise::getList(array(
                        'filter' => array('UF_PARTNER_ID' => self::$partner_ID),
                        'select' => array('ID')
                    ));
                    if ($get = $result->fetch()) {
                        self::$is_franchise = true;
                        return self::$is_franchise;
                    }
                }
            }
            self::$is_franchise = false;
        }

        return self::$is_franchise;
    }

    public static function getFranchiseOrders(){
        /**
         * выбрать заказы, у которых свойство franchise равно коду партнера
         */
        $arOrders = array();
        if(self::isFranchise() && CModule::IncludeModule("sale")){
            include_once $_SERVER["DOCUMENT_ROOT"].'/testzone/util/platform.php';
            include_once getPlatformPath();

            $pl = new Platform();
            $arOrders = $pl->getFranchiseOrders(self::$partner_ID);
        }
        return $arOrders;
    }

    public static function setPartnerOrderProps(Sale\Order $order){
        $order_id = $order->getId();

        //$order = Sale\Order::load($order_id);
        $personType = $order->getPersonTypeId();
        $propertyCollection = $order->getPropertyCollection();
        $somePropValue = $propertyCollection->getItemByOrderPropertyId($personType == 1 ? 76 : 77); //franchise (must be corrected!!!)
        $franchise = $somePropValue->getValue();

        if($franchise) {
            $arPrice = CCatalogIBlockParameters::getPriceTypesList();
            if (isset($arPrice['purchasePrice_' . $franchise])) {
                $price_id = self::getPriceIdByString($arPrice['purchasePrice_' . $franchise]);

                $partnerPrice = 0;
                $arBasketProducts = array();

                $dbBasket = CSaleBasket::GetList(array("ID" => "ASC"), array("ORDER_ID" => $order_id), false, false,
                    array("PRODUCT_ID", "PRODUCT_XML_ID", "QUANTITY", "NAME"));
                $arFilter=array('IBLOCK_ID' => self::IBLOCK_ID/*,'ACTIVE' => 'Y'*/);
                $arFilterOr=array("LOGIC" => "OR");
                while ($arBasketProduct = $dbBasket->GetNext(false, false)) {
                    //purchasePrice_
                    $arFilterOr[]=array("ID" => intval($arBasketProduct["PRODUCT_ID"]));
                    $arBasketProducts[$arBasketProduct["PRODUCT_ID"]] = $arBasketProduct;
                }
                $arFilter[]=$arFilterOr;

                $arPartnerProducts = array();

                $db_products = CIBlockElement::GetList(array(), $arFilter, false, false, array("ID","XML_ID","CATALOG_GROUP_$price_id"));
                while ($arProduct = $db_products->GetNext(false, false)){
                    $productPartnerPrice = intval($arProduct["CATALOG_PRICE_$price_id"]) * self::BILL_TYPE_4_RATIO;
                    $arPartnerProducts[] = array(
                        "XML_ID" => $arProduct["XML_ID"],
                        "QUANTITY" => intval($arBasketProducts[$arProduct["ID"]]["QUANTITY"]),
                        "PRICE" => $productPartnerPrice
                    );
                    $productPartnerPrice = intval($arProduct["CATALOG_PRICE_$price_id"]) * intval($arBasketProducts[$arProduct["ID"]]["QUANTITY"]);
                    $partnerPrice += $productPartnerPrice;
                }
                $arPartnerProducts = json_encode($arPartnerProducts);

                //D7!!!!!
//                $order = Sale\Order::load($order_id);
//                $personType = $order->getPersonTypeId();
//                $propertyCollection = $order->getPropertyCollection();
                $somePropValue = $propertyCollection->getItemByOrderPropertyId($personType == 1 ? 78 : 79); //partner_price
                $somePropValue->setValue($partnerPrice);
                $somePropValue->save();
                $somePropValue = $propertyCollection->getItemByOrderPropertyId($personType == 1 ? 80 : 81); //partner_order
                $somePropValue->setValue($arPartnerProducts);
                $somePropValue->save();
                $somePropValue = $propertyCollection->getItemByOrderPropertyId($personType == 1 ? 82 : 83); //partner_order_created
                $somePropValue->setValue('N');
                $somePropValue->save();
            }
        }
    }

    public static function customOnOrderSend1C(Sale\Order $order){
        $ordId = $order->getId();

        include_once $_SERVER["DOCUMENT_ROOT"] . '/testzone/util/platform.php';
        include_once getPlatformPath();

        $pl = new Platform();
        $bill_request = $pl->makeRetailBillRequest($ordId);

        //$order = Sale\Order::load($ordId);
        $personType = $order->getPersonTypeId();
        $propertyCollection = $order->getPropertyCollection();
        $somePropValue = $propertyCollection->getItemByOrderPropertyId($personType == 1 ? 76 : 77); //franchise (add to order.ajax!!!)
        $franchise = $somePropValue->getValue();
        $bill1CPropValue = $propertyCollection->getItemByOrderPropertyId($personType == 1 ? 70 : 73);

        if(empty($bill1CPropValue->getValue())) {
            if ($franchise) {
                $bill1CPropValue->setValue('000-XXX');
                $bill1CPropValue->save();

                $psID = 0;
                $paymentCollection = $order->getPaymentCollection();
                foreach ($paymentCollection as $payment) {
                    $psID = $payment->getPaymentSystemId(); // ID платежной системы
                }

                if ($psID == 23) { //card
                    $new_bill_request = $bill_request;
                    unset($new_bill_request['bill_type']);
                    $bill_path = $pl->getBillPathByPartnerID($franchise);
                    $new_bill_request = array_replace(array(
                        'bill_type' => '3',
                        'bill_path' => $bill_path
                    ), $new_bill_request);
                    $result = $pl->makeTestBill($new_bill_request);
                } else {
                    $result = $pl->makeFranchiseBillByOrderID($franchise, $ordId);
                    if($result == false) {
                        $bill1CPropValue->setValue('NOT_AVAILABLE-XXXX');
                        $bill1CPropValue->save();
                    }
                    $somePropValue = $propertyCollection->getItemByOrderPropertyId($personType == 1 ? 82 : 83); //partner_order_created
                    $somePropValue->setValue('Y');
                    $somePropValue->save();
                }
            } else $result = $pl->makeTestBill($bill_request);

            if (isset($result["bill_number"])) {
                $bill1CPropValue->setValue($result["bill_number"]);
                $bill1CPropValue->save();
                //$account_number1 = $bill1CPropValue->getValue();
//                $order->setField('ACCOUNT_NUMBER', $account_number1);
//                $order->save();
            }
        }
    }

    /*public static function getPartnerMail($partner_ID){
        $hlPartner = self::getHlDataClass(self::PARTNER_HLBLOCK);
        if ($hlPartner != null) {
            $result = $hlPartner::getList(array(
                'filter' => array('UF_PARTNER_ID' => $partner_ID),
                'select' => array('ID')
            ));
            if ($get = $result->fetch()) {

            }
        }
    }*/

    private static function getHlDataClass($id){
        $hlDataClass = null;
        if(CModule::IncludeModule("highloadblock")) {
            $hldata = Bitrix\Highloadblock\HighloadBlockTable::getById($id)->fetch();
            $hlentity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hldata);
            if($hldata['NAME']) {
                $hlDataClass = $hldata['NAME'] . 'Table';
            }
        }
        return $hlDataClass;
    }
    private static function getPriceIdByString($string){
        preg_match('/^\[(\d+)\] \[(.+)\] (.+)/',$string,$matches);
        //print_r($matches);
        $price_id = $matches[1];
        return $price_id;
    }
}