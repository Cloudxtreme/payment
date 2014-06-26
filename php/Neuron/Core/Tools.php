<?php


namespace Neuron\Core;

use Neuron\Core\Tools as NeuronCoreTools;
use Exception;
use Neuron\Core\Text;
use XMLWriter;



class Tools
{
	public static function getInput ($dat, $key, $type, $default = false)
	{
		if (is_string ($dat))
		{
			global $$dat;
			$dat = $$dat;
		}

		if (!isset ($dat[$key])) {
			return $default;
		}

		else {
			// Check if the value has the right type
			if (NeuronCoreTools::checkInput ($dat[$key], $type)) 
			{
				switch ($type)
				{
					// For date's return timestamp.
					case 'date':
						$time = explode ('-', $dat[$key]);
						return mktime (0, 0, 1, $time[1], $time[2], $time[0]);
					break;

					default:
						return $dat[$key];
					break;
				}
			}

			else if ($type == 'bool')
			{
				return false;
			}

			else 
			{
				return $default;
			}
		}
	}

	public static function checkInput ($value, $type)
	{
		if ($type == 'text')
		{
			return true;
		}

		else if ($type == 'bool')
		{
			return $value == 1 || $value == 'true';
		}
		
		elseif ($type == 'varchar')
		{
			return true;
		}
		
		elseif ($type == 'password')
		{
			return strlen ($value) > 2;
		}
		
		elseif ($type == 'email')
		{
			//return (bool)preg_match("/^[_a-z0-9-]+(\.[_a-z0-9\+\-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i", $value);
			return filter_var ($value, FILTER_VALIDATE_EMAIL) ? true : false;
		}
		
		elseif ($type == 'username')
		{
			return (bool)preg_match ('/^[a-zA-Z0-9_]{3,20}$/', $value);
		}
		
		elseif ($type == 'village')
		{
			$chk = preg_match ('/^[a-zA-Z0-9\' ]{3,40}$/', $value);
			$value = trim ($value);
			$notempty = !empty ($value);
			$chk = $chk && $notempty;
			return $chk;
		}

		elseif ($type == 'unitname')
		{
			$chk = preg_match ('/^[a-zA-Z0-9 ]{3,20}$/', $value);
			$value = trim ($value);
			$notempty = !empty ($value);
			$chk = $chk && $notempty;
			return $chk;
		}

		elseif ($type == 'date')
		{
			$time = explode ('-', $value);
			return (count ($time) == 3);
		}
		
		elseif ($type == 'md5')
		{
			return strlen ($value) == 32;
		}

		elseif ($type == 'url')
		{
			$regex = '/((https?:\/\/|[w]{3})?[\w-]+(\.[\w-]+)+\.?(:\d+)?(\/\S*)?)/i';
			return (bool)preg_match($regex, $value);

			/*

			$regex = "((https?|ftp)\:\/\/)?"; // Scheme
			$regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
			$regex .= "([a-z0-9-.]*)\.([a-z]{2,3})"; // Host or IP
			$regex .= "(\:[0-9]{2,5})?"; // Port
			$regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path
			$regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
			$regex .= "(#[a-z_.\!-][a-z0-9+\$_.\!-]*)?"; // Anchor

			return (bool)preg_match("/^$regex$/i", $value);
			*/

			//return filter_var ($value, FILTER_VALIDATE_URL) !== false;

			//return (bool)preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $value);
		}

		elseif ($type == 'number')
		{
			return is_numeric ($value);
		}
		
		elseif ($type == 'int')
		{
			return is_numeric ($value) && (int)$value == $value;
		}
		
		else {
		
			return false;
			echo 'fout: '.$type;
		
		}

	}

	public static function putIntoText ($text, $ar = array(), $delimiter = '@@') 
	{
		foreach ($ar as $k => $v) 
		{
			if (is_string ($v) || is_float ($v) || is_int ($v))
			{
				$text = str_replace ($delimiter.$k, $v, $text);
			}
			else if (is_object ($v))
			{
				$text = str_replace ($delimiter.$k, (string)$v, $text);	
			}
			else
			{
				throw new Exception ("putIntoText excepts an array.");
			}
		}
		
		// Remove all remaining "putIntoTexts"
		$text = preg_replace ('/'.$delimiter.'([^ ]+)/s', '', $text);
		
		return $text;
	}
	
	public function date_long ($stamp)
	{
	
		$text = Text::__getInstance ();
		
		$dag = $text->get ('day'.(date ('w', $stamp) + 1), 'days', 'main');
		$maand = $text->get ('mon'.date ('m', $stamp), 'months', 'main');
	
		return NeuronCoreTools::putIntoText (
			$text->get ('longDateFormat', 'dateFormat', 'main'),
			array
			(
				$dag,
				date ('d', $stamp),
				$maand,
				date ('Y', $stamp)
			)
		);
	
	}

	public static function output_text ($input)
	{
		return nl2br ($input);
	}

	public static function output_datepicker ($date)
	{
		if ($date)
		{
			return date ('Y-m-d', $date);
		}
		return '';
	}
	
	public static function splitLongWords ($input)
	{
	
		$array = explode (' ', $input);
		
		foreach ($array as $k => $v)
		{
		
			$array[$k] = wordwrap ($v, 20, ' ', 1);
		
		}
		
		return implode (' ', $array);
	
	}
	
	public static function output_form ($text)
	{
	
		return htmlspecialchars (($text) , ENT_QUOTES, 'UTF-8');
	
	}
	
	public static function output_varchar ($text)
	{
	
		$input = NeuronCoreTools::splitLongWords ($text);
		return htmlspecialchars (($text), ENT_QUOTES, 'UTF-8');
	
	}
	
	public static function writexml (XMLWriter $xml, $data, $item_name = 'item')
	{
		foreach($data as $key => $value)
		{
			if (is_int ($key))
			{
				$key = $item_name;
			}

			if (is_array($value))
			{
				if ($key != 'items')
				{
					$xml->startElement($key);
				}
				
				if (isset ($value['attributes']) && is_array ($value['attributes']))
				{
					foreach ($value['attributes'] as $k => $v)
					{
						$xml->writeAttribute ($k, $v);
					}
					
					unset ($value['attributes']);
				}
				
				NeuronCoreTools::writexml ($xml, $value, substr ($key, 0, -1));
				
				if ($key != 'items')
				{
					$xml->endElement();
				}
			}
			
			elseif ($key == 'element-content')
			{
				$xml->text ($value);
			}
	
			else
			{
				$xml->writeElement($key, $value);
			}
		}
	}
	
	public static function output_xml ($data, $version = '0.1', $root = 'root', $parameters = array (), $sItemName = 'item')
	{	
		$xml = new XmlWriter();
		$xml->openMemory();
		$xml->startDocument('1.0', 'UTF-8');
		$xml->startElement($root);
		$xml->setIndent (true);
		
		if (!empty ($version))
		{
			$xml->writeAttribute ('version', $version);
		}
		
		foreach ($parameters as $paramk => $paramv)
		{
			$xml->writeAttribute ($paramk, $paramv);
		}

		NeuronCoreTools::writexml ($xml, $data, $sItemName);

		$xml->endElement();
		return $xml->outputMemory(true);
	}
	
	private static function xml_escape ($input)
	{
		//$input = str_replace ('"', '&quot;', $input);
		//$input = str_replace ("'", '&apos;', $input);
		
		
		$input = str_replace ('<', '&lt;', $input);
		$input = str_replace ('>', '&gt;', $input);
		$input = str_replace ('&', '&amp;', $input);
		
	
		return $input;
	}
	
	public static function output_partly_xml ($data, $key =  null)
	{
		$output = '<'.$key;
		
		if (isset ($data['attributes']) && is_array ($data['attributes']))
		{
			foreach ($data['attributes'] as $k => $v)
			{
				$output .= ' '.$k.'="'.$v.'"';
			}
		
			unset ($data['attributes']);
		}
		
		$output .= '>';
		if (!is_array ($data))
		{
			$output .= self::xml_escape ($data);
		}
		
		elseif (count ($data) == 1 && isset ($data['element-content']))
		{
			$output .= self::xml_escape ($data['element-content']);
		}
		
		else
		{
			foreach ($data as $k => $v)
			{
				if (is_numeric ($k))
				{
					$k = substr ($key, 0, -1);
				}
				
				$output .= self::output_partly_xml ($v, $k);
			}
		}
		$output .= '</'.$key.'>'."\n";
		
		return $output;
	}
}

?>
