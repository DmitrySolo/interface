<?
/*CModule::IncludeModule("sale");

class SdekDelivery
{
    function Init()
    {
        return array(
            /* Основное описание */
            /*"SID" => "sdekdelivery1",
            "NAME" => "Доставка Логистической Службой СДЭК",
            "DESCRIPTION" => "",
            "DESCRIPTION_INNER" => "",

            "BASE_CURRENCY" => COption::GetOptionString("sale", "default_currency", "RUB"),

            "HANDLER" => __FILE__,

            /* Методы обработчика */
            /*"DBGETSETTINGS" => array("SdekDelivery", "GetSettings"),
            "DBSETSETTINGS" => array("SdekDelivery", "SetSettings"),
            "GETCONFIG" => array("SdekDelivery", "GetConfig"),

            "COMPABILITY" => array("SdekDelivery", "Compability"),
            "CALCULATOR" => array("SdekDelivery", "Calculate"),

            /* Список профилей доставки */
            /*"PROFILES" => array(
                "deliverypoint" => array(
                    "TITLE" => "Доставка до пункта выдачи в Вашем городе",
                    "DESCRIPTION" => "Срок доставки до 3 дней",


                    "RESTRICTIONS_WEIGHT" => array(0), // без ограничений
                    "RESTRICTIONS_SUM" => array(0), // без ограничений
                ),
                "curier" => array(
                    "TITLE" => "Доставка курьером до двери",
                    "DESCRIPTION" => "Срок доставки до 3 дней",

                    "RESTRICTIONS_WEIGHT" => array(0), // без ограничений
                    "RESTRICTIONS_SUM" => array(0), // без ограничений
                ),
            )
        );
    }
/////////////////////////////////////////////////////////////////////////////////////////
    function GetConfig()
    {
        $arConfig = array(
            "CONFIG_GROUPS" => array(
                "deliverypoint" => "Пункты Выдачи",
                "curier" => "Курьерская доставка",

            ),

            "CONFIG" => array(),
        );

        return $arConfig;
    }
////////////////////////////////////////////////////////////////////////////////////
    // подготовка настроек для занесения в базу данных
    function SetSettings($arSettings)
    {
        // Проверим список значений стоимости. Пустые значения удалим из списка.
        foreach ($arSettings as $key => $value)
        {
            if (strlen($value) > 0)
                $arSettings[$key] = doubleval($value);
            else
                unset($arSettings[$key]);
        }

        // вернем значения в виде сериализованного массива.
        // в случае более простого списка настроек можно применить более простые методы сериализации.
        return serialize($arSettings);
    }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // подготовка настроек, полученных из базы данных
    function GetSettings($strSettings)
    {
        // вернем десериализованный массив настроек
        return unserialize($strSettings);
    }


    static function _GetPrice($idOrd,$morz,$cur=false){
        if (CModule::IncludeModule("iblock")) {
            $workDay=$_SESSION['WORK_TIME'];
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $arOrder = Array("SORT"=>"ASC");
            $arFilter = Array('IBLOCK_ID'=>18,"NAME"=>$_SESSION['TF_LOCATION_SELECTED_CITY_NAME']);
            $select=array('UF_DEL_TIME','UF_LI1_PRICE','UF_LI2_PRICE','UF_LI3_PRICE','UF_LI4_PRICE');
            $res=CIBlockSection::GetList($arOrder ,$arFilter,false,$select);
            $res=$res->getNext();
            $id=$res['ID'];
            $delTime=$res['UF_DEL_TIME'];
            $logIndex=array(1=>$res['UF_LI1_PRICE'],$res['UF_LI2_PRICE'],$res['UF_LI3_PRICE'],$res['UF_LI4_PRICE']);

            $arResult['DEL_TIME']=date('d',$workDay['TIME']+60*60*24*($delTime+1));//ARRESULT

            $arOrder = Array("SORT"=>"ASC");
            $arFilter = Array('SECTION_ID'=>"$id");
            $arNavStartParams=array('nTopCount'=>1);
            $arSelectFields=array('NAME');

            $res=CIBlockElement::GetList(
                $arOrder,
                $arFilter,
                $arGroupBy = false,
                $arNavStartParams,
                $arSelectFields
            );
            $res=$res->getNext();
            $arResult['ADDRESS']=$res['NAME'];

///////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////
            $arSelect=array('ID');
            $res=CIBlockElement::GetElementGroups(
                $idOrd,false,
                $arSelect
            );
            $res=$res->getNext();
            $sId=$res['ID'];
////////////////////////////////////////////////////////////////
            $arOrder = Array("SORT"=>"ASC");
            $arFilter = Array('IBLOCK_ID'=>'10','ID'=>$sId);
            $select = Array('UF_LOGISTYC_INDEX');

            $res=CIBlockSection::GetList(
                $arOrder,
                $arFilter,false,
                $select,false
            );
            $res=$res->getNext();
            $price=$logIndex[$res['UF_LOGISTYC_INDEX']];
            if(!$cur){
                //echo $price."*".$morz."*".($morz / 8)."<br>";
                if($price<=$morz / 6){
                    return $price=0;
                }else{
                    return $price;
                }
            }else{
                return $price * 2;//curier
            }


///////////////////////////////////////////////////////////////
        }
        return 0;
    }

    function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
    {
        $totalPrice= $arOrder['PRICE'];
        //print_r($arOrder);

        $arItems=array();
        foreach($arOrder['ITEMS'] as $item){
            $res = CIBlockElement::GetByID($item['PRODUCT_ID']);
            $ar_res = $res->GetNext();
            $res=CIBlockSection::GetList(array(), array('IBLOCK_ID'=>'10','ID'=>$ar_res['IBLOCK_SECTION_ID']),false, array('UF_LOGISTYC_INDEX'),false);
            $res=$res->getNext();
            $l_index=($res['UF_LOGISTYC_INDEX']>1)?$res['UF_LOGISTYC_INDEX']:1;
            $arItems[''.$l_index.'_'.$item['PRODUCT_ID']]=$item['PRODUCT_ID'];
        }
        krsort($arItems);

        $price=0;
        if ($_SESSION['TF_LOCATION_SELECTED_CITY_NAME']!='Воронеж') {
            if ($profile == 'curier') {
                if($totalPrice > 49999){
                    $price=0;
                }else{
                    foreach($arItems as $prId){
                        $res=CIBlockElement::GetProperty(10,$prId,array(),array("CODE"=>"morz"));
                        $res=$res->GetNext();
                        $morz=$res['VALUE'];
                        if($price!=0){
                            $price+=self::_GetPrice($prId,$morz)*0.1;
                        }else{
                            $price+=self::_GetPrice($prId,$morz,$cur=true);
                        }
                    }

                }
            }
            if ($profile == 'deliverypoint') {
                if($totalPrice > 29999){
                    $price=0;
                }else{
                    foreach($arItems as $prId){
                        $res=CIBlockElement::GetProperty(10,$prId,array(),array("CODE"=>"morz"));
                        $res=$res->GetNext();
                        $morz=$res['VALUE'];
                        if($price!=0){
                            $price+=self::_GetPrice($prId,$morz)*0.1;
                        }else{
                            $price+=self::_GetPrice($prId,$morz);
                        }
                    }
                }
            }
        } else{
            if ($profile == 'curier') {
                if($totalPrice > 5999){
                    $price=0;
                }else{
                    foreach($arItems as $prId){
                        $res=CIBlockElement::GetProperty(10,$prId,array(),array("CODE"=>"morz"));
                        $res=$res->GetNext();
                        $morz=$res['VALUE'];
                        if($price!=0){
                            $price+=self::_GetPrice($prId,$morz,$cur=true)*0.1;
                        }else{
                            $price+=self::_GetPrice($prId,$morz);
                        }
                    }
                }
            }
            if ($profile == 'deliverypoint') {
                $price = 0;
            }
        }

        //$price=$price*0.5;


        return array(

            "RESULT" => "OK",
            "VALUE" =>$price
        );
    }
}

// установим метод CDeliveryMySimple::Init в качестве обработчика события
AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('SdekDelivery', 'Init'));
*/

/*

$module_id="ipol.sdek";
CModule::IncludeModule($module_id);

// ��������� ����� CDeliverySDEK::Init � �������� ����������� �������
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module_id.'/classes/general/sdekdelivery.php'))
	AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('CDeliverySDEK', 'Init'));

*/


?>
