<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== TRUE) die();
CModule::AddAutoloadClasses('vapnik.notifications', array(
	'\Vapnik\Main' => 'lib/Main.php',
));

class CVapnikNotificationWrappers // Для избежания проблем с пространством имен в вызове агента
{
	public static function NotificationsWrapper()
	{
		Vapnik\Main::SendNotifications();

		return 'CVapnikNotificationWrappers::NotificationsWrapper();';
	}
} ?>