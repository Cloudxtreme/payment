<?php

// Load database settings
$dbfile = dirname (dirname (__FILE__)) . '/includes/db.php';

if (file_exists ($dbfile))
{
	echo 'db file found (' . $dbfile . ')';
}
else
{
	echo 'db file found (' . $dbfile . ')';
}