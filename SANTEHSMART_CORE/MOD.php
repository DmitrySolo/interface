<?php


class MOD{
    public static function MAKE($action, $args){
        $action = 'make__'.$action;
        if(method_exists(self::class, $action)){
            return self::$action($args);
        }
        return null;
    }
    public static function RUN($action, $args){
        $action = 'run__'.$action;
        if(method_exists(self::class, $action)){
            self::$action($args);
        }
    }


    private static function run__JsDOMElementChecker($args){
        if(isset($args['object'])){
            include __DIR__.'/files/JsDOMElementChecker.js.php';
        }
    }
}