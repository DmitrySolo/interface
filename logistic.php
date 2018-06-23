<?php
    Class Logistic {

        static $CdekPVZFilename = 'cdekRequest.txt';
        static $punktyVidachyPath = '$_SERVER[\'DOCUMENT_ROOT\'].\'/punkty-vydachi/\'';

        public static function getPvzCount($partnerCount) {




            if( file_exists(self::$punktyVidachyPath.self::$CdekPVZFilename)){
                $sts_query = unserialize( file_get_contents(self::$punktyVidachyPath.self::$CdekPVZFilename));
            }else{
                $sts_query  =  ISDEKservice::getPVZ_sts();
                file_put_contents(self::$punktyVidachyPath.self::$CdekPVZFilename,serialize($sts_query));
            }

            $sts_city_key = array_flip($sts_query['pvz']['CITY'])[$_SESSION["TF_LOCATION_SELECTED_CITY_NAME"]];
            $sts_pvz_array = $sts_query['pvz']['PVZ'][$sts_city_key];

           return $colvo = count($sts_pvz_array)+$partnerCount;

        }







    }
