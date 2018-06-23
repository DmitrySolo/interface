<?php
function clear_old_user_permitions(){
    $ar_geo_prices_users=array(
        'BASE'=>2,
        'CFOVOR'=>12,
        'CFO'=>13,
    );
    foreach($_SESSION["SESS_AUTH"]["GROUPS"] as $key=>$group){
        if(in_array($group,$ar_geo_prices_users))unset($_SESSION["SESS_AUTH"]["GROUPS"][$key]);
    }
    $_SESSION["SESS_AUTH"]["GROUPS"][] = 2;
}

function geo_cfo(){
    $_SESSION["SESS_AUTH"]["GROUPS"][] = 13;
}

function geo_cfo_vrn(){
    $_SESSION["SESS_AUTH"]["GROUPS"][] = 12;
}

use Bitrix\Main;

function onBeforeBasketGEO(Main\Event $event){
//function onBeforeBasketGEO(&$arFields){
    global $USER;
    $iblock_id=10;
    if(!$USER->IsAdmin()){  
        if(CModule::IncludeModule("iblock")){
            //$_SESSION["BASKETFIELDS"]=array();
            $arResultPrices = CIBlockPriceTools::GetCatalogPrices($iblock_id, ['BASE','CFO','CFOVOR']);//todo change this
	        $arResultPricesAllow = CIBlockPriceTools::GetAllowCatalogPrices($arResultPrices);
	        
	        $arSelect = array(
			    "ID",
			    "IBLOCK_ID",
			    "CODE",
			    "XML_ID",
			    "NAME",
			    "ACTIVE",
			    "DATE_ACTIVE_FROM",
			    "DATE_ACTIVE_TO",
			    "SORT",
			    "DATE_CREATE",
			    "CREATED_BY",
			    "TIMESTAMP_X",
			    "MODIFIED_BY",
			    "TAGS",
			    "IBLOCK_SECTION_ID",
			    "DETAIL_PAGE_URL",
			    "LIST_PAGE_URL",
		    );
		    
		    if (!empty($arResultPrices))
		    {
				foreach ($arResultPrices as &$value)
				{
				    if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
					    continue;
				    $arSelect[] = $value["SELECT"];
			    }
			    unset($value);
		    }
		    
		    //$item = $event->getParameter("ENTITY");
		    $basket = $event->getParameter("ENTITY");
		    foreach ($basket as $item) {
            $db_el=CIBlockElement::GetList(array(),array('IBLOCK_ID'=>$iblock_id,'XML_ID'=>$item->getField('PRODUCT_XML_ID')/*'XML_ID'=>$arFields['PRODUCT_XML_ID']*/), false, false, $arSelect);
            if($ar_res=$db_el->GetNext()){
			    $ar_res["CAT_PRICES"] = $arResultPrices;
			    $ar_res['PRICES_ALLOW'] = $arResultPricesAllow;
			    
			    $ar_res['CONVERT_CURRENCY'] = array();
	            
			    $ar_res["PRICE_MATRIX"] = false;
			    $ar_res["PRICES"] = array();
			    $ar_res['MIN_PRICE'] = false;
	            
	            $ar_res["PRICES"] = CIBlockPriceTools::GetItemPrices($iblock_id, $ar_res["CAT_PRICES"], $ar_res, false);
				    if (!empty($ar_res['PRICES']))
					    $ar_res['MIN_PRICE'] = CIBlockPriceTools::getMinPriceFromList($ar_res['PRICES']);
				
				$ar_res['MIN_PRICE_EXTRA']['VALUE']=$ar_res['MIN_PRICE']['VALUE'];
				$ar_res['MIN_PRICE_EXTRA']['ID']=$ar_res['MIN_PRICE']['ID'];
                if(count($_SESSION["SESS_AUTH"]["GROUPS"])){
                    $key_extra_price='';
                    $key_extra_price_okr='';
                    foreach($ar_res['PRICES'] as $price_key=>$price_array){
                        if($price_key!='BASE'&&$price_key!='SPEC'){
                            if($price_array['VALUE']>0){
                                if(empty($key_extra_price)) $key_extra_price = $price_key;
                                elseif(strlen($price_key)<strlen($key_extra_price)) $key_extra_price_okr = $price_key;
                                else{
                                    $key_extra_price_okr = $key_extra_price;
                                    $key_extra_price = $price_key;
                                }
                            }
                        }
                    }
                    if(!empty($key_extra_price)){
                        $ar_res['MIN_PRICE_EXTRA']['VALUE']=$ar_res['PRICES'][$key_extra_price]['VALUE'];
                        $ar_res['MIN_PRICE_EXTRA']['ID']=$ar_res['PRICES'][$key_extra_price]['ID'];
                    }
                }
                /*$arFields['PRICE']=$ar_res['MIN_PRICE_EXTRA']['VALUE'];
                $arFields['BASE_PRICE']=$ar_res['MIN_PRICE_EXTRA']['VALUE'];
                $arFields['PRODUCT_PRICE_ID']=$ar_res['MIN_PRICE_EXTRA']['ID'];
                $arFields['CUSTOM_PRICE']='Y';*/
                $item->setField('CUSTOM_PRICE', 'Y');
                //$item->setPrice('BASE_PRICE', $ar_res['MIN_PRICE_EXTRA']['VALUE']);
                $item->setField('PRICE', $ar_res['MIN_PRICE_EXTRA']['VALUE']);
                $item->setField('PRODUCT_PRICE_ID', $ar_res['MIN_PRICE_EXTRA']['ID']);
                $item->setField('CALLBACK_FUNC', '');
                $item->setField('ORDER_CALLBACK_FUNC', '');
                $item->setField('PRODUCT_PROVIDER_CLASS', '');
                //$_SESSION["BASKETFIELDS"]=$ar_res["PRICES"];
                
                $item->save();
                //$_SESSION["BASKETFIELDS"]=$item;
                //$basket->save();
			}
            //$_SESSION["BASKETFIELDS"]=$arFields;
            }
            $result = new Main\EventResult( Main\EventResult::SUCCESS, array("ENTITY", $basket) );
            $event->addResult($result);
            return $result;
	    }
	}
}

function onBeforeBasketPartner(Main\Event $event){
    $iblock_id=10;
    $partner_ID=isPartnerClient();
    $basket = $event->getParameter("ENTITY");
    if($partner_ID)
    if(CModule::IncludeModule("iblock")){
        //$_SESSION["BASKETFIELDS"]=array();
        $arResultPrices = CIBlockPriceTools::GetCatalogPrices($iblock_id, ['purchasePrice_'.$partner_ID]);//todo change this
        $arResultPricesAllow = CIBlockPriceTools::GetAllowCatalogPrices($arResultPrices);

        $arSelect = array(
            "ID",
            "IBLOCK_ID",
            "CODE",
            "XML_ID",
            "NAME",
            "ACTIVE",
            "DATE_ACTIVE_FROM",
            "DATE_ACTIVE_TO",
            "SORT",
            "DATE_CREATE",
            "CREATED_BY",
            "TIMESTAMP_X",
            "MODIFIED_BY",
            "TAGS",
            "IBLOCK_SECTION_ID",
            "DETAIL_PAGE_URL",
            "LIST_PAGE_URL",
        );

        if (!empty($arResultPrices))
        {
            foreach ($arResultPrices as &$value)
            {
                if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
                    continue;
                $arSelect[] = $value["SELECT"];
            }
            unset($value);
        }

        //$item = $event->getParameter("ENTITY");
        foreach ($basket as $item) {
            $db_el=CIBlockElement::GetList(array(),array('IBLOCK_ID'=>$iblock_id,'XML_ID'=>$item->getField('PRODUCT_XML_ID')/*'XML_ID'=>$arFields['PRODUCT_XML_ID']*/), false, false, $arSelect);
            if($ar_res=$db_el->GetNext()){
                $ar_res["CAT_PRICES"] = $arResultPrices;
                $ar_res['PRICES_ALLOW'] = $arResultPricesAllow;

                $ar_res['CONVERT_CURRENCY'] = array();

                $ar_res["PRICE_MATRIX"] = false;
                $ar_res["PRICES"] = array();
                $ar_res['MIN_PRICE'] = false;

                $ar_res["PRICES"] = CIBlockPriceTools::GetItemPrices($iblock_id, $ar_res["CAT_PRICES"], $ar_res, false);
                if (!empty($ar_res['PRICES']))
                    $ar_res['MIN_PRICE'] = CIBlockPriceTools::getMinPriceFromList($ar_res['PRICES']);

                /*$arFields['PRICE']=$ar_res['MIN_PRICE_EXTRA']['VALUE'];
                $arFields['BASE_PRICE']=$ar_res['MIN_PRICE_EXTRA']['VALUE'];
                $arFields['PRODUCT_PRICE_ID']=$ar_res['MIN_PRICE_EXTRA']['ID'];
                $arFields['CUSTOM_PRICE']='Y';*/
                $item->setField('CUSTOM_PRICE', 'Y');
                //$item->setPrice('BASE_PRICE', $ar_res['MIN_PRICE_EXTRA']['VALUE']);
                $item->setField('PRICE', $ar_res['MIN_PRICE']['VALUE']);
                $item->setField('PRODUCT_PRICE_ID', $ar_res['MIN_PRICE']['ID']);
                $item->setField('CALLBACK_FUNC', '');
                $item->setField('ORDER_CALLBACK_FUNC', '');
                $item->setField('PRODUCT_PROVIDER_CLASS', '');
                //$_SESSION["BASKETFIELDS"]=$ar_res["PRICES"];

                $item->save();
                //$_SESSION["BASKETFIELDS"]=$item;
                //$basket->save();
            }
            //$_SESSION["BASKETFIELDS"]=$arFields;
        }
    }
    $result = new Main\EventResult( Main\EventResult::SUCCESS, array("ENTITY", $basket) );
    $event->addResult($result);
    return $result;
}

function doGEOLogic(){
    global $USER;
    $iblock_id=29;
    
    clear_old_user_permitions();
    
    if(!$USER->IsAdmin()){  
        if(CModule::IncludeModule("iblock")){
            $db_sec=CIBlockSection::GetList(array(),array('IBLOCK_ID'=>$iblock_id),false,array('ID','UF_DIST_ON','CODE'));
            while($ar_sec=$db_sec->getNext()){
                $db_el=CIBlockElement::GetList(array(),array('IBLOCK_ID'=>$iblock_id,'SECTION_ID'=>$ar_sec['ID']),false,array('ID','CODE','NAME'));
                while($ar_el=$db_el->getNext()){
                    if($ar_el['NAME']==$_SESSION['TF_LOCATION_SELECTED_CITY_NAME']){
                        if($ar_sec['UF_DIST_ON']>0) AddEventHandler("main", "OnProlog", "geo_".$ar_sec['CODE']);
                        $db_props = CIBlockElement::GetProperty(IntVal($iblock_id), $ar_el['ID'], array("sort" => "asc"), Array("CODE"=>"city_price_on"));
                        if(($ar_props = $db_props->Fetch()) && IntVal($ar_props["VALUE"])>0)
                            AddEventHandler("main", "OnProlog", "geo_".$ar_sec['CODE']."_".$ar_el['CODE']);
                    }
                }
            }
            //AddEventHandler("sale", "OnBeforeBasketAdd", "onBeforeBasketGEO");
            /*Main\EventManager::getInstance()->addEventHandler(
                'sale',
                'OnSaleBasketBeforeSaved',
                'onBeforeBasketGEO'
            );*/
        }
    }
}
//AddEventHandler("main", "OnBeforeProlog", "doGEOLogic");

Main\EventManager::getInstance()->addEventHandler(
    'sale',
    'OnSaleBasketBeforeSaved',
    'onBeforeBasketPartner'
);
