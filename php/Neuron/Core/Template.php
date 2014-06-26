<?php


namespace Neuron\Core;

use Neuron\Core\Text;
use Neuron\Core\Tools;



// A few ugly methods because this wasn't very well designed at first? ;-)
$catlab_template_path = '';

function set_template_path ($path)
{
	global $catlab_template_path;
	$catlab_template_path = $path;

	if (!defined ('TEMPLATE_DIR'))
	{
		define ('TEMPLATE_DIR', $path);
	}
}

function get_template_path ()
{
	global $catlab_template_path;
	return $catlab_template_path;
}

function add_to_template_path ($path, $priorize = true, $folder = null)
{
	if ($folder)
	{
		$path = $folder . '|' . $path;
	}

	if (get_template_path () == '')
	{
		set_template_path ($path);
	}

	else if ($priorize)
	{
		set_template_path ($path . PATH_SEPARATOR . get_template_path ());
	}
	else
	{
		set_template_path (get_template_path () . PATH_SEPARATOR . $path);
	}
}

// Backwards compatability stuff
if (defined ('DEFAULT_TEMPLATE_DIR'))
{
	add_to_template_path (DEFAULT_TEMPLATE_DIR, false);
}

if (defined ('TEMPLATE_DIR'))
{
	add_to_template_path (TEMPLATE_DIR, true);
}

class Template
{

	private $values = array ();
	private $lists = array ();
	
	private $sTextFile = null;
	private $sTextSection = null;
	
	private $objText = null;
	
	public static function load ()
	{
	
	}

	/**
	* I believe this is a nicer way to do the directory setting.
	*/
	public static function setTemplatePath ($path)
	{
		set_template_path ($path);
	}

	/**
	* Add a folder to the template path.
	* @param $path: path to add
	* @param $prefix: only templates starting with given prefix will be loaded from this path.
	*/
	public static function addTemplatePath ($path, $prefix, $priorize = false)
	{
		add_to_template_path ($path, $priorize, $prefix);
	}
	
	public static function getUniqueId ()
	{
		if (!isset ($_SESSION['tc']))
		{
			$_SESSION['tc'] = time ();
		}
		
		$_SESSION['tc'] ++;
		
		return $_SESSION['tc'];
	}
	
	private static function getTemplatePaths ()
	{
		return explode (PATH_SEPARATOR, get_template_path ());
	}
	
	// Text function
	public function setTextSection ($sTextSection, $sTextFile = null)
	{
		$this->sTextSection = $sTextSection;
		
		if (isset ($sTextFile))
		{
			$this->sTextFile = $sTextFile;
		}
	}
	
	public function setTextFile ($sTextFile)
	{
		$this->sTextFile = $sTextFile;
	}

	public function set ($var, $value, $overwrite = false, $first = false)
	{
		$this->setVariable ($var, $value, $overwrite, $first);
	}
	
	// Intern function
	private function getText ($sKey, $sSection = null, $sFile = null, $sDefault = null)
	{
		if (!isset ($this->objText))
		{
			$this->objText = Text::__getInstance ();
		}
		
		$txt = Tools::output_varchar
		(
			$this->objText->get 
			(
				$sKey, 
				isset ($sSection) ? $sSection : $this->sTextSection, 
				isset ($sFile) ? $sFile : $this->sTextFile,
				false
			)
		);

		if (!$txt)
		{
			return $sDefault;
		}

		return $txt;
	}

	public function setVariable ($var, $value, $overwrite = false, $first = false)
	{
		if ($overwrite)
		{	
			$this->values[$var] = $value;
		}
		
		else 
		{
			if (isset ($this->values[$var]))
			{
				if ($first)
				{	
					$this->values[$var] = $value.$this->values[$var];
				}
				
				else 
				{
					$this->values[$var].= $value;
				}
			}
			
			else 
			{
				$this->values[$var] = $value;
			}
		}
	}
	
	public function addListValue ($var, $value)
	{
		$this->lists[$var][] = $value;
	}
	
	public function putIntoText ($txt, $params = array ())
	{
		return Tools::putIntoText ($txt, $params);
	}
	
	public function sortList ($var)
	{
		if (isset ($this->lists[$var]))
		{
			sort ($this->lists[$var]);
		}
	}
	
	public function usortList ($var, $function)
	{
		if (isset ($this->lists[$var]))
		{
			usort ($this->lists[$var], $function);
		}
	}
	
	public function isTrue ($var)
	{
		return isset ($this->values[$var]) && $this->values[$var];
	}
	
	private static function getFilename ($template)
	{
		foreach (self::getTemplatePaths () as $v)
		{
			// Split prefix and folder
			$split = explode ('|', $v);

			if (count ($split) === 2)
			{
				$prefix = array_shift ($split);
				$folder = implode ('|', $split);
				$templatefixed = substr ($template, 0, strlen ($prefix));

				if ($templatefixed == $prefix)
				{

					$templaterest = substr ($template, strlen ($templatefixed));
					if (is_readable ($folder . '/' . $templaterest))
					{
						return $folder . '/' . $templaterest;
					}
				}
			}
			else
			{
				if (is_readable ($v . '/' . $template))
				{
					return $v . '/' . $template;
				}
			}
		}
		
		return false;	
	}
	
	public static function hasTemplate ($template)
	{
		return self::getFilename ($template) != false;
	}
	
	public function getClickTo ($sKey, $sSection = null, $sFile = null)
	{
		if (!isset ($this->objText))
		{
			$this->objText = Text::__getInstance ();
		}
		
		return $this->objText->getClickTo ($this->getText ($sKey, $sSection, $sFile));
	}

	public function parse ($template, $text = null)
	{
		/* Set static url adress */
		$this->set ('STATIC_URL', TEMPLATE_DIR);
		
		// SEt unique id
		$this->set ('templateID', self::getUniqueId ());

		ob_start ();
		
		if (! $filename = $this->getFilename ($template))
		{
			echo '<h1>Template not found</h1>';
			echo '<p>The system could not find template "'.$template.'"</p>';
			
			$filename = null;
		}
		
		foreach ($this->values as $k => $v)
		{
			$$k = $v;
		}
		
		foreach ($this->lists as $k => $v)
		{
			$n = 'list_'.$k;	
			$$n = $v;
		}
		
		
		if (isset ($filename))
		{
			include $filename;
		}
		
		$val = ob_get_contents();
		ob_end_clean();

		return $val;
	}
}
?>
