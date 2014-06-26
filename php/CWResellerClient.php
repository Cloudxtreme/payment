<?php
use Neuron\Net\Client;
use Neuron\Net\Request;

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

	/**
	 * Sign an array of data.
	 * @param $data
	 * @return string
	 */
	private function sign (&$data)
	{
		$json = json_encode ($data);

		$privatekey = openssl_get_privatekey (file_get_contents (CWRESELLER_PRIVATE_KEY));

		$signature = null;
		openssl_sign ($json, $signature, $privatekey);

		return base64_encode ($signature);
	}

	public function getPlans ()
	{
		$request = new Request (CWRESELLER_API . 'reseller/' . CWRESELLER_RESELLER_ID . '/plans');

		$data = array ();
		$data['time'] = time ();
		$data['random'] = mt_rand ();
		$data['signature'] = $this->sign ($data);

		$request->setQuery ($data);

		$response = Client::getInstance ()->get ($request);
		return $response->data ();
	}
} 