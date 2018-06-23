<?
CModule::IncludeModule("sale");

class SdekDelivery
{
	function Init()
	{

		return array(
			/* Основное описание */
			"SID" => "sdekdelivery1",
			"NAME" => "Доставка Логистической Службой СДЭК",
			"DESCRIPTION" => "",
			"DESCRIPTION_INNER" => "",

			"BASE_CURRENCY" => COption::GetOptionString("sale", "default_currency", "RUB"),

			"HANDLER" => __FILE__,

			/* Методы обработчика */
			"DBGETSETTINGS" => array("SdekDelivery", "GetSettings"),
			"DBSETSETTINGS" => array("SdekDelivery", "SetSettings"),
			"GETCONFIG" => array("SdekDelivery", "GetConfig"),

			"COMPABILITY" => array("SdekDelivery", "Compability"),
			"CALCULATOR" => array("SdekDelivery", "Calculate"),

			/* Список профилей доставки */
			"PROFILES" => array(

                "partner" => array(
                    "TITLE" => "Забрать из пункта выдачи",
                    "DESCRIPTION" => "Срок доставки до 3 дней",

                ),
				"deliverypoint" => array(
					"TITLE" => "Забрать из пункта выдачи СДЭК",
					"DESCRIPTION" => "Срок доставки до 3 дней",
				),
				"curier" => array(
					"TITLE" => "Доставка курьером до двери",
					"DESCRIPTION" => "Срок доставки до 3 дней",

				)

			)
		);
	}
/////////////////////////////////////////////////////////////////////////////////////////
	function GetConfig()
	{
		$arConfig = array(
			"CONFIG_GROUPS" => array(
                "partner" => "sd доставка",
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

	    $Logistic = Logistic::getInstance($_SESSION['TF_LOCATION_SELECTED_CITY_NAME']);
		$totalPrice = $arOrder['PRICE'];


        $partnersPVZArr = $Logistic->partnersPVZArr;
        foreach ($partnersPVZArr as $officeName =>$info){};


		if( $profile == 'partner' && $partnersPVZArr ){

			$price = $partnersPVZArr[$officeName]['pickups'][1]['delivery']['pickUp']['price'];

 		foreach ($partnersPVZArr as $pointTitle => $pointInfo) {
    		 foreach ($pointInfo['pickups'] as $pickup) {



				 $min = $pickup['delivery']['pickUp']['min'];
				 $max = $pickup['delivery']['pickUp']['max'];

                 if($min == 0 && $max == 1){
                     $timeStr = '1 день';
                 }elseif ($min  && $max ==1){
                     $timeStr = '1-2 дня';
                 }elseif ($min == $max){
                     $timeStr = $max.' дня';
                 }else{
                     $timeStr = $min.' - '.$max.' дня';
                 }

                 $_SESSION['DELIVERY_TIME_PVZ'] = $timeStr;




			 }


 }




		}elseif (  $profile == 'curier' && $partnersPVZArr[$officeName]['pickups'][1]['delivery']['curier']  ){

           if($totalPrice >  $partnersPVZArr[$officeName]['pickups'][1]['delivery']['curier']['rule']){
               $price= $partnersPVZArr[$officeName]['pickups'][1]['delivery']['curier']['else'];
		   }else{
               $price= $partnersPVZArr[$officeName]['pickups'][1]['delivery']['curier']['price'];
		   }
		}
		else{
            $totalWeight = 0;
            foreach($arOrder['ITEMS'] as $item){



                if( $item['WEIGHT'] > 0 )
                    $totalWeight += $item['WEIGHT'];
                else
                    $totalWeight += 3000;
            }

            $answer = Logistic::calcAll($totalWeight);
			$price = $answer['value'];
 			$price = ($profile != 'curier')? $price:$price*1.5;
           	$answer = Logistic::calcAll($totalWeight);
            $price = $answer['value'];
            $price = ($profile != 'curier')? $price:$price*1.5;
           // pre($answer);
            if($answer['min'] == 0 && $answer['max'] == 1){
                $timeStr = '1 день';
            }elseif ($answer['min']==1  && $answer['max'] ==1){
                $timeStr = '1-2 дня';
            }elseif ($answer['min'] == $answer['max']){
                $timeStr = $answer['max'].' дня';
            }else{
                $timeStr = $answer['min'].' - '.$answer['max'].' дня';
            }

            $_SESSION['DELIVERY_TIME'] = $timeStr;
        }



        return array(

            "RESULT" => "OK",
            "VALUE" =>$price
        );

	}
}

// установим метод CDeliveryMySimple::Init в к;честве обработчика события
AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('SdekDelivery', 'Init'));





?>
