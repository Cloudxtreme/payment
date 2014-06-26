<?php

require_once 'include.php';

$client = new CWResellerClient ();

$client->createAccount ("CWTEST " . date ('d/m/Y H:i:s'), 1);