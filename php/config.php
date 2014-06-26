<?php

// Load database settings
// This file has been specifically written for cloudwalkers.
// Make sure to change these values in case you host this somewhere else.

$dbfile = dirname (dirname (dirname (__FILE__))) . '/includes/db.php';

if (file_exists ($dbfile))
{
	require_once $dbfile;

	define('DB_HOST', $dbud["username"]);
	define('DB_USERNAME', $dbud["password"]);
	define('DB_PASSWORD', $dbud["server"]);
	define('DB_NAME', $dbud["dbname"]);
}

define('DB_CHARSET', 'utf8');

if (!defined ('DB_HOST'))
{
	define ('DB_HOST', 'localhost');
}

if (!defined ('DB_USERNAME'))
{
	define ('DB_USERNAME', 'myuser');
}

if (!defined ('DB_PASSWORD'))
{
	define ('DB_PASSWORD', 'myuser');
}

if (!defined ('DB_NAME'))
{
	define ('DB_NAME', 'cloudwalkers_order');
}

define ('CWRESELLER_RESELLER_ID', 1);
define ('CWRESELLER_API', 'https://devapi.cloudwalkers.be/');
//define ('CWRESELLER_API', 'http://cloudwalkers-engine.local/');

define ('CWRESELLER_PRIVATE_KEY', dirname (dirname (__FILE__)) . '/signature/private.pem');
define ('CWRESELLER_PRIVATE_PUBLIC', dirname (dirname (__FILE__)) . '/signature/public.pem');

define ('CWRESELLER_OAUTH2_APPID', 'oauth253ac29c185dbd5.38393073');

define ('TEMPLATE_DIR', dirname (dirname (__FILE__)) . '/templates/');