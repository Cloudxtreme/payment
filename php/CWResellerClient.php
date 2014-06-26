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
		$get['debug'] = isset ($_GET['debug']) ? 1 : 0;

		return $get;
	}

	public function getPlans ()
	{
		$request = new Request (CWRESELLER_API . '1/resellers/' . CWRESELLER_RESELLER_ID . '/plans');

		$request->setQuery ($this->sign ());
		$response = Client::getInstance ()->get ($request);

		return $response->data ();
	}

	public function createAccount ($accountName, $planId)
	{
		echo '<h1>Creating account</h1>';

		$request = new Request (CWRESELLER_API . '1/resellers/' . CWRESELLER_RESELLER_ID . '/accounts');

		$body = array ();
		$body['name'] = $accountName;
		$body['plan'] = $planId;

		$request->setJSON ($body);

		$request->setQuery ($this->sign (json_encode ($body)));

		$response = Client::getInstance ()->post ($request);

		echo "<h2>Posting to</h2>";
		var_dump ($request->getUrl ());

		echo "<h2>Content</h2>";
		var_dump ($request->getBody ());

		echo "<h2>Response</h2>";
		var_dump ($response->getBody());

		$data = $response->data ();

		$id = $data['account']['id'];

		echo '<h2>GOT ID ' . $id . '</h2>';
		return $id;
	}

	public function addLicense ($accountId, $planId, $start, $end)
	{
		echo '<h1>Adding license</h1>';

		$request = new Request (CWRESELLER_API . '1/accounts/' . $accountId . '/licenses');

		$body = array ();
		$body['plan'] = $planId;
		$body['start'] = date ('c', $start);
		$body['end'] = date ('c', $end);

		$request->setJSON ($body);

		$request->setQuery ($this->sign (json_encode ($body)));

		$response = Client::getInstance ()->post ($request);

		echo "<h2>Posting to</h2>";
		var_dump ($request->getUrl ());

		echo "<h2>Content</h2>";
		var_dump ($request->getBody ());

		echo "<h2>Response</h2>";
		var_dump ($response->getBody());

		$data = $response->data ();

		$id = $data['license']['plan'];

		echo '<h2>GOT PLAN ID ' . $id . '</h2>';
		return $id;
	}

	public function addUser ($accountId, $email, $password, $firstname, $lastname)
	{
		echo '<h1>Adding user</h1>';

		$request = new Request (CWRESELLER_API . '1/accounts/' . $accountId . '/users');

		$body = array ();
		$body['email'] = $email;
		$body['name'] = $lastname;
		$body['password'] = $password;
		$body['firstName'] = $firstname;

		$request->setJSON ($body);

		$request->setQuery ($this->sign (json_encode ($body)));

		$response = Client::getInstance ()->post ($request);

		echo "<h2>Posting to</h2>";
		var_dump ($request->getUrl ());

		echo "<h2>Content</h2>";
		var_dump ($request->getBody ());

		echo "<h2>Response</h2>";
		var_dump ($response->getBody());

		$data = $response->data ();

		$id = $data['user']['id'];

		echo '<h2>GOT USER ID ' . $id . '</h2>';
		return $id;
	}
} 