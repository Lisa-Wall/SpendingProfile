<?
	$bHttps = true;
	define('URL', (isset($bHttps) && $bHttps ? 'https://' : 'http://').'www.spendingprofile.com/');
	define('SERVER', (isset($bHttps) && $bHttps ? 'https://' : 'http://').'www.spendingprofile.com/server/index.php');

	define('ROOT_PATH', '');

	//SQL Authentication
	define('SQL_HOST'    , 'localhost');
	define('SQL_USERNAME', '<username>');
	define('SQL_PASSWORD', '<password>');
	define('SQL_DATABASE', '<database>');
	
	define('SQL_HOST_DEMO'    , 'localhost');
	define('SQL_USERNAME_DEMO', '<username>');
	define('SQL_PASSWORD_DEMO', '<password>');
	define('SQL_DATABASE_DEMO', '<database>');

	//SMTP
	define('SMTP_FEEDBACK', '<emailaddress>');
	define('SMTP_FROM'    , '<emailaddress>');
	define('SMTP_ADMIN'   , '<emailaddress>');
	define('SMTP_SERVER'  , '<emailaddress>');

	//Logging information.
	define('LOG_REQUESTS', false);
	define('LOG_EVENT', false);
	define('LOG_PATH', '<path>');
	define('LOG_HTML', false);
	define('LOG_PATH_HTML', '<path>');
	define('LOG_NOTIFY', true);
	define('LOG_NOTIFY_EMAILS', '<emailaddress>'); //add more separated by ';' no spaces.
	define('ADMIN_EMAIL', '<emailaddress>');

	//Debugging settings
	define('DEBUG_PATH', '<path>');
	define('DEBUG_ENABLED', false);

	//Page settings.
	define('PAGE_WIDTH_OUTER', '760px');
	define('PAGE_WIDTH_INNER', '973px');

	define('VERSION', '3.2.5');
?>