<?php

/**
 * This class contains many methods to manage assets files like CSS and Javascript.
 * @package App
 * @subpackage App\Core
 */
namespace App\Core;

class Asset
{
	protected $_controller = null;
	protected $_css = array();
	protected $_js = array();
	protected $_engine = null;
	protected $_engineOptions = null;

	public function __construct($controller)
	{
		$this->setController($controller);

		$config = $this->getController()->getApplication()->getConfig('general');
		if(!empty($config->assets))
		{
			$this->setEngine($config->assets['engine']);
			$this->setEngineOptions($config->assets['options']);
		}
	}

	public function getController()
	{
		return $this->_controller;
	}

	public function setController($controller)
	{
		$this->_controller = $controller;

		return $this;
	}

	public function getEngine()
	{
	    return $this->_engine;
	}

	public function setEngine($engine)
	{
	    $this->_engine = $engine;

	    return $this;
	}

	public function getEngineOptions()
	{
	    return $this->_engineOptions;
	}

	public function setEngineOptions($engineOptions)
	{
	    $this->_engineOptions = $engineOptions;

	    return $this;
	}

	public function getCss()
	{
	    return $this->_css;
	}

	public function setCss(array $css)
	{
	    $this->_css = $css;

	    return $this;
	}

	public function addCss($file)
	{
		$this->_add($this->_css, $file);

		return $this;
	}

	public function getJs()
	{
	    return $this->_js;
	}

	public function setJs(array $js)
	{
	    $this->_js = $js;

	    return $this;
	}

	public function addJs($file)
	{
		$this->_add($this->_js, $file);

		return $this;
	}

	protected function _add(&$collection, $file)
	{
		$collection[md5($file)] = $file;

		return $this;
	}

	protected function _load()
	{
		$assets = $this->getController()->getApplication()->getConfig('assets');
		$javascript = $assets->js;
		$css = $assets->css;

		############################### Load javascript files ###############################
		// Add javascript files to be loaded all the time
		while($file = array_shift($javascript['all']))
		{
			$this->addJs($file);
		}

		// If exists files to load when call all controllers of the module
		if($javascript[$this->getController()->getModuleName()]['all'])
		{
			while($file = array_shift($javascript[$this->getController()->getModuleName()]['all']))
			{
				$this->addJs($file);
			}
		}

		// If exists files to load when call all actions of the specific controller of the module
		if($javascript[$this->getController()->getModuleName()][$this->getController()->getControllerName()]['all'])
		{
			while($file = array_shift($javascript[$this->getController()->getModuleName()][$this->getController()->getControllerName()]['all']))
			{
				$this->addJs($file);
			}
		}

		// If exists files to load when call a especific action of the controller of the module
		if($javascript[$this->getController()->getModuleName()][$this->getController()->getControllerName()][$this->getController()->getActionName()])
		{
			while($file = array_shift($javascript[$this->getController()->getModuleName()][$this->getController()->getControllerName()][$this->getController()->getActionName()]))
			{
				$this->addJs($file);
			}
		}
		############################### Load javascript files ###############################

		############################### Load css files ###############################
		// Add css files to be loaded all the time
		while($file = array_shift($css['all']))
		{
			$this->addCss($file);
		}

		// If exists files to load when call all controllers of the module
		if($css[$this->getController()->getModuleName()]['all'])
		{
			while($file = array_shift($css[$this->getController()->getModuleName()]['all']))
			{
				$this->addCss($file);
			}
		}

		// If exists files to load when call all actions of the specific controller of the module
		if($css[$this->getController()->getModuleName()][$this->getController()->getControllerName()]['all'])
		{
			while($file = array_shift($css[$this->getController()->getModuleName()][$this->getController()->getControllerName()]['all']))
			{
				$this->addCss($file);
			}
		}

		// If exists files to load when call a especific action of the controller of the module
		if($css[$this->getController()->getModuleName()][$this->getController()->getControllerName()][$this->getController()->getActionName()])
		{
			while($file = array_shift($css[$this->getController()->getModuleName()][$this->getController()->getControllerName()][$this->getController()->getActionName()]))
			{
				$this->addCss($file);
			}
		}
		############################### Load css files ###############################

		return array(
			'css' => $this->getCss(),
			'js' => $this->getJs(),
		);
	}

	protected function _buildByNormal($files)
	{
		$_js = array();
		while($file = array_shift($files['js']))
		{
			$_file = null;
			if(strpos($file, 'http') === 0)
			{
				$_file = $file;
			}
			elseif(file_exists(PUBLIC_PATH . $file))
			{
				$_file = $this->getController()->getApplication()->baseUrl($file);
			}

			if(!is_null($_file))
			{
				$_js[] = sprintf('<script type="text/javascript" src="%s"></script>', $_file);
			}
		}

		$_css = array();
		while($file = array_shift($files['css']))
		{
			$_file = null;
			if(strpos($file, 'http') === 0)
			{
				$_file = $file;
			}
			elseif(file_exists(PUBLIC_PATH . $file))
			{
				$_file = $this->getController()->getApplication()->baseUrl($file);
			}

			if(!is_null($_file))
			{
				$_css[] = sprintf('<link href="%s" media="all" rel="stylesheet" type="text/css" />', $_file);
			}
		}

		return array(
			'js' => (count($_js) > 0)?implode("\n", $_js) . "\n":null,
			'css' => (count($_css) > 0)?implode("\n", $_css) . "\n":null,
		);
	}

	protected function _buildByMunee($files)
	{
		$options = $this->getEngineOptions();
		$minify = null;
		if(!empty($options['minify']) && $options['minify'] == true)
		{
			$minify = '?minify=true';
		}

		$explicit = null;
		if(!empty($options['explicit']) && $options['explicit'] == true)
		{
			if(!is_null($minify))
			{
				$minify = null;
				$explicit = $this->getController()->getApplication()->baseUrl('munee.php?minify=true&files=');
			}
			else
			{
				$explicit = $this->getController()->getApplication()->baseUrl('munee.php?files=');
			}
		}

		$_jsInternals = array();
		$_jsExternals = array();
		while($file = array_shift($files['js']))
		{
			if(strpos($file, 'http') === 0)
			{
				$_jsExternals[] = $file;
			}
			else
			{
				if(file_exists(PUBLIC_PATH . $file))
				{
					$_jsInternals[] = $this->getController()->getApplication()->baseUrl($file);
				}
			}
		}

		$_js = array();
		if(count($_jsExternals) > 0)
		{
			$_js[] = sprintf('<script type="text/javascript" src="%s"></script>', implode(',', $_jsExternals));
		}

		if(count($_jsInternals) > 0)
		{
			$_js[] = sprintf('<script type="text/javascript" src="%s"></script>', $explicit . implode(',', $_jsInternals) . $minify);
		}

		$_cssInternals = array();
		$_cssExternals = array();
		while($file = array_shift($files['css']))
		{
			if(strpos($file, 'http') === 0)
			{
				$_cssExternals[] = $file;
			}
			else
			{
				if(file_exists(PUBLIC_PATH . $file))
				{
					$_cssInternals[] = $this->getController()->getApplication()->baseUrl($file);
				}
			}
		}

		$_css = $_cssExternals;
		if(count($_cssExternals) > 0)
		{
			$_css[] = sprintf('<link href="%s" media="all" rel="stylesheet" type="text/css" />', implode(',', $_cssExternals));
		}
		if(count($_cssInternals) > 0)
		{
			$_css[] = sprintf('<link href="%s" media="all" rel="stylesheet" type="text/css" />', $explicit . implode(',', $_cssInternals) . $minify);
		}

		return array(
			'js' => (count($_js) > 0)?implode("\n", $_js) . "\n":null,
			'css' => (count($_css) > 0)?implode("\n", $_css) . "\n":null,
		);
	}

	public function run()
	{
		$files = $this->_load();

		switch($this->getEngine())
		{
			case 'munee':
				$files = $this->_buildByMunee($files);
			break;
			default:
				$files = $this->_buildByNormal($files);
			break;
		}

		return array(
			'js' => $files['js'],
			'css' => $files['css'],
		);
	}
}