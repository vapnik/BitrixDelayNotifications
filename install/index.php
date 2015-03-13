<? global $DOCUMENT_ROOT, $MESS;

//IncludeModuleLangFile(__FILE__);


Class vapnik_notifications extends CModule
{
	var $MODULE_ID = "vapnik.notifications";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME = "Рассыка напоминаний";
	var $MODULE_DESCRIPTION = "Рассыка напоминаний";
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function vapnik_notifications()
	{
		$this->MODULE_VERSION = '0.1';
		$this->MODULE_VERSION_DATE = '2015-03-13';
		$this->MODULE_NAME = "Рассыка напоминаний";
		$this->MODULE_DESCRIPTION = 'Рассыка напоминаний';

	}


	function DoInstall()
	{
		RegisterModule("vapnik.notifications");

		return TRUE;
	}

	function DoUninstall()
	{
		UnRegisterModule("askaron.geolocate");

		return TRUE;
	}
}

?>
