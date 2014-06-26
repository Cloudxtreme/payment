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