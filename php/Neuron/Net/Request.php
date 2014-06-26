<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 21/04/14
 * Time: 18:36
 */

namespace Neuron\Net;


use Neuron\Models\User;

class Request {

	private $url;
	private $method;
	private $input;
	private $user;
	private $requeststring;
	private $body;

	public function __construct ($url = null)
	{
		if (strpos ($url, '?') !== false)
		{
			$parts = explode ('?', $url);
			$query = array_pop ($parts);
			$this->setQueryString ($query);
			$url = implode ('?', $parts);
		}

		if (isset ($url))
		{
			$this->setUrl ($url);
		}
	}

	public function setUrl ($url)
	{
		$this->url = $url;
	}

	public function getUrl ()
	{
		return $this->url;
	}

	public function setMethod ($method)
	{
		$this->method = $method;
	}

	public function getMethod ()
	{
		return $this->method;
	}

	public function setInput ($input)
	{
		$this->input = $input;
	}

	public function getInput ($input = null)
	{
		if (isset ($input))
		{
			if (isset ($this->input[$input]))
			{
				return $this->input[$input];
			}
			return null;
		}
		return $this->input;
	}

	public function setUser (User $user)
	{
		$this->user = $user;
	}

	public function getUser ()
	{
		return $this->user;
	}

	public function setQuery (array $data)
	{
		$this->setQueryString (http_build_query ($data));
	}

	public function setQueryString ($string)
	{
		$this->setRequestString ($string);
	}

	public function setRequestString ($requeststring)
	{
		$this->requeststring = $requeststring;
	}

	public function getRequestString ()
	{
		return $this->requeststring;
	}

	public function setBody ($body)
	{
		$this->body = $body;
	}

	public function setRequestBody ($body)
	{
		$this->body = $body;
	}

	public function getRequestBody ()
	{
		return $this->body;
	}

	public function getApplication ()
	{
		return 0;
	}
} 