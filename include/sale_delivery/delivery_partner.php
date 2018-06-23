<?
CModule::IncludeModule("sale");

class PartnerDelivery
{
    function Init()
    {
        return array(
            /* Основное описание */
            "SID" => "partnerdelivery",
            "NAME" => "Доставка юридическому лицу",
            "DESCRIPTION" => "",
            "DESCRIPTION_INNER" => "",

            "BASE_CURRENCY" => COption::GetOptionString("sale", "default_currency", "RUB"),

            "HANDLER" => __FILE__,

            /* Методы обработчика */
            "DBGETSETTINGS" => array("PartnerDelivery", "GetSettings"),
            "DBSETSETTINGS" => array("PartnerDelivery", "SetSettings"),
            "GETCONFIG" => array("PartnerDelivery", "GetConfig"),

            "COMPABILITY" => array("PartnerDelivery", "Compability"),
            "CALCULATOR" => array("PartnerDelivery", "Calculate"),

            /* Список профилей доставки */
            "PROFILES" => array(
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

    function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
    {
        return array(

            "RESULT" => "OK",
            "VALUE" => 0
        );
    }
}

// установим метод CDeliveryMySimple::Init в качестве обработчика события
AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('PartnerDelivery', 'Init'));
?>
