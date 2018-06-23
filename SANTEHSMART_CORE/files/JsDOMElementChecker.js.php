<?php
/**
 * @var $args
 */
$mod_ajax_code = md5(time()/2);
$_SESSION["MOD_AJAX_CODE"][] = $mod_ajax_code;
$_SESSION["MOD_AJAX_CODE"] = array_slice($_SESSION["MOD_AJAX_CODE"], -3);
?>
if (window.JsDOMElementCheckerTimer) window.clearTimeout(window.JsDOMElementCheckerTimer);
window.JsDOMElementCheckerTimer = window.setTimeout(function () {
    var object = document.getElementsByClassName('<?=$args['object']?>');
    if (object.length) {
        console.log('FINDING..... <?=$args['object']?> WAS FOUND!!!');
        console.log(object);
    }
    else {
        var ua= navigator.userAgent, tem,
            M= ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
        if(/trident/i.test(M[1])){
            tem=  /\brv[ :]+(\d+)/g.exec(ua) || [];
            return 'IE '+(tem[1] || '');
        }
        if(M[1]=== 'Chrome'){
            tem= ua.match(/\b(OPR|Edge)\/(\d+)/);
            if(tem!= null) return tem.slice(1).join(' ').replace('OPR', 'Opera');
        }
        M= M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
        if((tem= ua.match(/version\/(\d+)/i))!= null) M.splice(1, 1, tem[1]);
        navigator.saywho = M.join(' ');

        console.log('FINDING..... <?=$args['object']?> WAS NOT FOUND!!!');

        <?=$args['command']?>

//        $.post( window.location, {
//            ajax_mode: 'Y',
//            ajax_code: '<?//=$mod_ajax_code?>//',
//            run: 'AjaxJsDOMElementChecker',
//            browser: navigator.saywho
//        } ,function( msg ) {
//            console.log(msg);
//        }, "json");
    }
}, 5000);