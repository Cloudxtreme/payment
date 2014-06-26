<?php

require_once 'config.php';
require_once 'CWResellerClient.php';
require_once 'Neuron/Net/Client.php';
require_once 'Neuron/Net/Request.php';
require_once 'Neuron/Net/Response.php';
require_once 'Neuron/Core/Template.php';
require_once 'Neuron/Core/Tools.php';
require_once 'Neuron/DB/Database.php';
require_once 'Neuron/DB/MySQL.php';
require_once 'Neuron/DB/Query.php';
require_once 'Neuron/DB/Result.php';

if (!function_exists ('__'))
{
	function __ ($english)
	{
		return $english;
	}
}

/**
 * This method is shameful.
 * @param $password
 * @return string
 */
function cwSimpleCrypt ($password)
{
	$password .= '|||CWSALT' . mt_rand ();

	$encryptionMethod = "AES-256-CBC";
	$secretHash = md5 (TMP_PASSWORD_ENCRYPT);

	return openssl_encrypt($password, $encryptionMethod, $secretHash, false, substr ($secretHash, 0, 16));
}

/**
 * THis method is also shameful.
 * @param $encrypted
 * @return string
 */
function cwSimpleDecrypt ($encrypted)
{
	$encryptionMethod = "AES-256-CBC";
	$secretHash = md5 (TMP_PASSWORD_ENCRYPT);

	$decrypted = openssl_decrypt ($encrypted, $encryptionMethod, $secretHash, false, substr ($secretHash, 0, 16));

	$decrypted = explode ('|||CWSALT', $decrypted);

	if (count ($decrypted) > 1)
	{
		array_pop ($decrypted);
	}

	$decrypted = implode ('|||CWSALT', $decrypted);

	return $decrypted;
}