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
	 * @param $postbody
	 * @internal param $post
	 * @return string
	 */
	private function sign ($postbody = '')
	{
		$get = array ();
		$get['time'] = time ();
		$get['random'] = mt_rand ();
		$get['reseller'] = CWRESELLER_RESELLER_ID;

		$json = implode (',', $get) . '|' . $postbody;

		$privatekey = openssl_get_privatekey ('file://' . CWRESELLER_PRIVATE_KEY);

		$signature = null;
		openssl_sign ($json, $signature, $privatekey);

		$get['signature'] = base64_encode ($signature);

		return $get;
	}

	public function getPlans ()
	{
		$request = new Request (CWRESELLER_API . 'reseller/' . CWRESELLER_RESELLER_ID . '/plans');

		$request->setQuery ($this->sign ());
		$response = Client::getInstance ()->get ($request);

		return $response->data ();
	}

	public function createAccount ($accountName, $planId)
	{
		$request = new Request (CWRESELLER_API . 'reseller/' . CWRESELLER_RESELLER_ID . '/account');

		$body = array ();
		$body['name'] = $accountName;
		$body['plan'] = $planId;

		$request->setBody (json_encode ($body));

		$request->setQuery ($this->sign (json_encode ($body)));

		$response = Client::getInstance ()->post ($request);

		echo "<h1>Posting to</h1>";
		var_dump ($request->getUrl ());

		echo "<h1>Content</h1>";
		var_dump ($request->getBody ());

		echo "<h1>Response</h1>";
		var_dump ($response->getBody());

		return $response->data ();
	}


} 