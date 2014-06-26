<?php

// Load database settings
$dbfile = dirname (dirname (__FILE__)) . '';

if (file_exists ($dbfile))
{
	echo 'db file found.';
}