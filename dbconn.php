<?
define("DBPersistent", false);
$DBType = "mysql";
$DBHost = "localhost";
$DBLogin = "partnersts";
$DBPassword = "partnersts";
$DBName = "bitrix_48";
$DBDebug = false;
$DBDebugToFile = false;

define("BX_USE_MYSQLI", true);
define("BX_TEMPORARY_FILES_DIRECTORY", "/var/www/west/data/bitrix-tmp");

define("BX_PULL_SKIP_INIT", true);

define("DELAY_DB_CONNECT", true);
define("CACHED_b_file", 3600);
define("CACHED_b_file_bucket_size", 10);
define("CACHED_b_lang", 3600);
define("CACHED_b_option", 3600);
define("CACHED_b_lang_domain", 3600);
define("CACHED_b_site_template", 3600);
define("CACHED_b_event", 3600);
define("CACHED_b_agent", 3660);
define("CACHED_menu", 3600);

/*define("BX_CACHE_TYPE", "memcache");
define("BX_CACHE_SID", $_SERVER["DOCUMENT_ROOT"]."#01");
define("BX_MEMCACHE_HOST", "unix:///var/run/memcached/memcached.sock");
define("BX_MEMCACHE_PORT", "0");*/

//define("BX_CACHE_TYPE", "files");

define("BX_UTF", true);
define("BX_FILE_PERMISSIONS", 0644);
define("BX_DIR_PERMISSIONS", 0755);
//@umask(~BX_DIR_PERMISSIONS);
define("BX_DISABLE_INDEX_PAGE", true);

define('BX_UTF', true);

date_default_timezone_set("Europe/Moscow");

//define("BX_COMPOSITE_DEBUG", true); //Включаем дебаг
//define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/log.txt"); //Включаем логирование
?>
