<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 12/06/14
 * Time: 16:00
 */

namespace Neuron\Net;


use Neuron\Exceptions\NotImplemented;

class Client {

	public static function getInstance ()
	{
		static $in;
		if (!isset ($in))
		{
			$in = new self ();
		}
		return $in;
	}

	private function __construct ()
	{

	}

	public function get (Request $request)
	{
		return $this->api ($request, 'GET');
	}

	public function post (Request $request)
	{
		return $this->api ($request, 'POST');
	}

	public function put (Request $request)
	{
		return $this->api ($request, 'PUT');
	}

	public function delete (Request $request)
	{
		return $this->api ($request, 'DELETE');
	}

	private function api (Request $request, $method)
	{
		$ch = curl_init();

		$post = $request->getRequestBody ();

		curl_setopt($ch, CURLOPT_URL, $request->getUrl () . '?' . $request->getRequestString ());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$headers = array ();
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		switch ($method)
		{
			case 'GET':
			break;

			case 'POST':
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			break;

			case 'DELETE':
				throw new NotImplemented ("Not implemented.");
			break;

			case 'PUT':
				curl_setopt($ch, CURLOPT_PUT, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			break;
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $request->getHeaders ());

		$output = curl_exec($ch);

		// Response
		$response = new Response ();
		$response->setBody ($output);
		return $response;
	}

} 