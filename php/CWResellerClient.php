<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 26/06/14
 * Time: 15:34
 */

class CWResellerClient {

	public function __construct ()
	{

	}

	public function post ($data)
	{
		$json = json_encode ($data);

		$privatekey = openssl_get_privatekey (file_get_contents (CWRESELLER_PRIVATE_KEY));

		$signature = null;
		openssl_sign ($json, $signature, $privatekey);

		$data['signature'] = base64_encode ($signature);

		return $data;
	}
} 