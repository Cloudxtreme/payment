<?php

require_once 'include.php';

$client = new CWResellerClient ();

$email = 'thijs+' . mt_rand () . '@catlab.be';
$password = mt_rand ();
$firstname = 'Thijs ' . mt_rand ();
$lastname = 'VdS ' . mt_rand ();

$accountId = $client->createAccount ("CW " . date ('d/m H:i'), 2);
$client->addLicense ($accountId, 2, time (), time () + 60 * 60 * 24 * 365);
$client->addUser ($accountId, $email, $password, $firstname, $lastname);
