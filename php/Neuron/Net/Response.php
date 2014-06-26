<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 21/04/14
 * Time: 18:36
 */

namespace Neuron\Net;


use Neuron\Models\User;

class Response {

	private $body = null;
	private $data = null;

	public function setData ($data)
	{
		$this->data = $data;
	}

	public function setBody ($body)
	{
		$this->body = $body;
	}

	public function getBody ()
	{
		return $this->body;
	}

	public function setHeader ($header, $content)
	{
		header ($header . ': ' . $content);
	}

	public function data ()
	{
		if (!isset ($this->data))
		{
			if ($this->getBody ())
			{
				// Should be based on headers.
				$this->data = json_decode ($this->getBody (), true);
			}

			else
			{
				$this->data = array ('error' => array ('message' => 'No response data set.'));
			}
		}

		return $this->data;
	}

	public function redirect ($url)
	{
		$this->setHeader ('Location', $url);
		$this->setData (array ('message' => 'Redirecting to ' . $url));
	}

	public function output ()
	{

	}
} 