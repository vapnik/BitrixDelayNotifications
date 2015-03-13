<?
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", TRUE);
define("NOT_CHECK_PERMISSIONS", TRUE);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('sale');
CModule::IncludeModule('vapnik.notifications');
Vapnik\Main::SendNotifications();
die();