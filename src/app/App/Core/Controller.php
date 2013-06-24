<?php

/**
 * This class contains many methods for use in app controllers
 * @package App
 * @subpackage App\Core
 */
namespace App\Core;

class Controller
{
	protected $_application = null;
	protected $_moduleName = null;
	protected $_controllerName = null;
	protected $_actionName = null;
	protected $_noRender = false;
	protected $_asset = null;
	public $view = null;

	public function __construct($application)
	{
		$this->setApplication($application);
		$this->setView(new View($application));
		$this->setAsset(new Asset($this));

		$this->_init();
	}

	public function getApplication()
	{
		return $this->_application;
	}

	public function setApplication($application)
	{
		$this->_application = $application;

		return $this;
	}

	public function getLog()
	{
		return $this->getApplication()->getFramework()->getLog();
	}

	protected function _init()
	{
		return $this;
	}

	public function before(){}
	public function after(){}

	public function getRequest()
	{
		return $this->getApplication()->getFramework()->request();
	}

	public function getResponse()
	{
		return $this->getApplication()->getFramework()->response();
	}

	public function redirect($url)
	{
		$this->getResponse()->redirect($url);
	}

	public function getParam($key)
	{
		return $this->getRequest()->params($key);
	}

	public function setView($view)
	{
		$this->view = $view;

		return $this;
	}

	public function getView()
	{
		return $this->view;
	}

	public function getViewBasePath()
	{
		return APPLICATION_PATH . 'Modules/' . ucfirst($this->getModuleName()) . '/Views/';
	}

	public function getViewPath()
	{
		return $this->getViewBasePath() . $this->getViewFilename();
	}

	public function getViewFilename()
	{
		return $this->getActionName() . '.phtml';
	}

	public function getModuleName()
	{
		return $this->_moduleName;
	}

	public function setModuleName($moduleName)
	{
		$this->_moduleName = $moduleName;

		return $this;
	}
	public function getControllerName()
	{
		return $this->_controllerName;
	}

	public function setControllerName($controllerName)
	{
		$this->_controllerName = $controllerName;

		return $this;
	}
	public function getActionName($suffix = null)
	{
		return $this->_actionName . $suffix;
	}

	public function setActionName($actionName)
	{
		$this->_actionName = $actionName;

		return $this;
	}

	public function flash($key, $value)
	{
		$this->getFramework()->flash($key, $value);
	}

	public function flashNow($key, $value)
	{
		$this->getFramework()->flashNow($key, $value);
	}

	public function getNoRender()
	{
		return $this->_noRender;
	}

	public function setNoRender($noRender = true)
	{
		$this->_noRender = $noRender;

		return $this;
	}

	public function getLayout()
	{
		return $this->getApplication()->getLayout();
	}

	public function getAsset()
	{
	    return $this->_asset;
	}

	public function setAsset($asset)
	{
	    $this->_asset = $asset;

	    return $this;
	}

	public function buildAssets()
	{
		$assets = $this->getAsset()->run();

		$this->getView()->css = $assets['css'];
		$this->getView()->js = $assets['js'];
	}

	public function run()
	{
		if(!$this->getApplication()->getIsDispatchable())
		{
			$this->before();
			call_user_func(array($this, $this->getActionName('Action')));
			$this->after();
			// var_dump($this->getViewBasePath());die;
			$this->getApplication()->getFramework()->config(
				array('templates.path' => $this->getViewBasePath())
			);

			$viewFilename = $this->getControllerName() . '/' . $this->getViewFilename();
			if(!$this->getNoRender())
			{
				if(!file_exists($this->getViewBasePath() . $viewFilename))
				{
					throw new \Exception('View file not found. File path: ' . $this->getViewBasePath() . $viewFilename);
				}

				$this->buildAssets();

				$this->getApplication()
					->getFramework()
					->render(
						$this->getControllerName() . '/' . $this->getViewFilename(),
						(array)$this->getView()->getData()
					);
			}
			else
			{
				$this->getApplication()->getLayout()->renderNoView($this->getViewBasePath());
			}

			$this->getApplication()->isDispatchable();
		}

		return $this;
	}
}