<?php

/**
 * This class make magic in load resources for application is start
 * @package App
 * @subpackage App\Core
 */
namespace App\Core;

class Application
{
	const CONFIG_MERGE_REPLACE = 0;
	const CONFIG_MERGE_RECURSIVE = 1;

	protected $_configs = array();
	protected $_framework = null;
	protected $_isDispatchable = false;
	protected $_layout = null;

	public function __construct(&$framework)
	{
		$this->setFramework($framework);
		$this->initTools();

		$this->_buildConfigs();
		$this->initFrameworkConfiguration();
	}

	public function getFramework()
	{
		return $this->_framework;
	}

	public function setFramework($framework)
	{
		$this->_framework = $framework;

		return $this;
	}

	protected function _buildConfigs()
	{
		$this->_buildConfig();

		if(APPLICATION_ENV === 'development')
		{
			$this->_buildConfig(APPLICATION_ENV);
		}

		return $this;
	}

	protected function _buildConfig($enviroment = null)
	{
		if(!is_null($enviroment))
		{
			$enviroment .= '/';
		}

		$files = glob(APPLICATION_PATH . 'configs/' . $enviroment . '*');

		while($f = array_shift($files))
		{
			if(is_file($f))
			{
				preg_match('/.*\/([a-z]+).php$/', $f, $confFilenameParts);

				if(count($confFilenameParts) == 2)
				{
					$confContent = require_once $f;
					if(is_array($confContent))
					{
						$confContent = (object)$confContent;
					}
					else
					{
						$confContent = (object)array();
					}

					$this->setConfig($confFilenameParts[1], $confContent);
				}
			}
		}
	}

	public function getConfigs()
	{
		return $this->_configs;
	}

	public function setConfig($key, $value, $mode = self::CONFIG_MERGE_RECURSIVE)
	{
		if(array_key_exists($key, $this->_configs) && $mode == self::CONFIG_MERGE_RECURSIVE)
		{
			$old = (array)$this->_configs[$key];
			$this->_configs[$key] = (object)array_merge($old, (array)$value);
		}
		else
		{
			$this->_configs[$key] = $value;
		}

		return $this;
	}

	public function getConfig($key)
	{
		return $this->_configs[$key];
	}

	/**
	 * Method make configuration of the framework like route, debug, log and others
	 * @return \App\Core\Application
	 */
	public function initFrameworkConfiguration()
	{
		$app = $this;

		// Set debug and log configuration
		$app->getFramework()->config(array(
			// 'log.enable' => $this->getConfig('general')->log,
			'debug' => $this->getConfig('general')->debug
		));

		// If exists view configuration on general settings.
		if(!empty($this->getConfig('general')->view))
		{
			// Set view class manager
			$viewClass = $this->getConfig('general')->view;
			$view = new $viewClass($this);
			$app->getFramework()->view($view);

			$this->setLayout($view);
		}

		// If exists configuration of the layout filename on general settings.
		if(!empty($this->getConfig('general')->layout))
		{
			$this->getFramework()->config(array(
				'layout' => $this->getConfig('general')->layout,
			));
		}

		//If log is enabled, configure the LogWriter
		if($this->getConfig('general')->log && is_array($this->getConfig('general')->log))
		{
			// Set log configuration
			$app->getFramework()->config((array)$this->getConfig('general')->log);

			// If log writer configuration is an object instance, set it as the log writer
			if(is_object($app->getFramework()->config('log.writer')))
			{
				$app->getFramework()->getLog()->setWriter(
					$app->getFramework()->config('log.writer')
				);
			}
		}

		$app->getFramework()->error(function (\Exception $e) use ($app) {
			if($e instanceOf \App\Core\Exception\ControllerNotExistsException
				|| $e instanceOf \App\Core\Exception\ControllerActionNotExistsException)
			{
				try
				{
					$app->getFramework()->flashNow('error', $e->getMessage());

					if($app->getFramework()->getLog())
					{
						$app->getFramework()->getLog()->debug($e->getMessage());
					}

					$app->getFramework()->notFound();
				}
				catch(\Exception $e2)
				{
					$app->getFramework()->flashNow('error', $e2->getMessage());

					if($app->getFramework()->getLog())
					{
						$app->getFramework()->getLog()->debug($e2->getMessage());
					}

					$controller = $app->loadController(
						array(),
						array('module' => 'site', 'controller' => 'error', 'action' => 'index')
					);
					$controller->getView()->exception = $e2;
					$controller->run();
				}
			}
			else
			{
				$app->getFramework()->flashNow('error', $e->getMessage());

				if($app->getFramework()->getLog())
				{
					$app->getFramework()->getLog()->debug($e->getMessage());
				}

				$controller = $app->loadController(
					array(),
					array('module' => 'site', 'controller' => 'error', 'action' => 'index')
				);
				$controller->getView()->exception = $e;
				$controller->run();
			}
		});

		$app->getFramework()->notFound(function () use ($app) {
			$controller = $app->loadController(
				func_get_args(),
				array('module' => 'site', 'controller' => 'error', 'action' => 'error404'),
				'/404'
			);
			$controller->run();
		});

		// Set routes configuration
		$routes = (array)$this->getConfig('routes');
		foreach($routes as $routeName => $route)
		{
			$app->getFramework()->map($route[0], function() use ($app, $route){
				$controller = $app->loadController(func_get_args(), $route[1], $route[0]);
				$controller->run();
			})
			->via('GET', 'POST', 'PUT', 'DELETE')
			->name($routeName);

		}

		return $this;
	}

	/**
	 * This method make controller class loader and call action method.
	 * @param  array $parameters array with data of the route parser
	 * @param  array $defaults array with data contain default value of module, controller and action
	 * @param  string $route string contain route pattern
	 * @return \App\Core\Controller
	 */
	public function loadController(array $parameters, array $defaults, $route = null)
	{
		//Extract default values of the route to variable with name $module, $controller and $action,
		//if this names exists in array $defaults
		extract($defaults, EXTR_OVERWRITE);

		//Set default value of $module, if is not exists, is not extracted of the array $defaults
		if(!isset($module))
		{
			$module = 'index';
		}

		//Set default value of $controller, if is not exists, is not extracted of the array $defaults
		if(!isset($controller))
		{
			$controller = 'index';
		}

		//Set default value of $action, if is not exists, is not extracted of the array $defaults
		if(!isset($action))
		{
			$action = 'index';
		}

		// Walk inside array $parameters searching empty register and, if existis, remove.
		array_walk($parameters, function($item, $k) use (&$parameters){
			if(empty($item))
			{
				unset($parameters[$k]);
			}
		});

		if(count($parameters) > 2)
		{
			$module = array_shift($parameters);
		}

		if(count($parameters) == 1)
		{
			$controller = array_shift($parameters);
		}
		elseif(count($parameters) == 2)
		{
			$controller = array_shift($parameters);
			$action = array_shift($parameters);
		}

		$file = $this->getControllerFilename($module, $controller);
		$class = $this->getControllerName($module, $controller);

		if(!file_exists($file))
		{
			throw new \App\Core\Exception\ControllerNotExistsException('Resource does not exists.');
		}

		$controllerObj = new $class($this);
		$controllerObj->setModuleName($module);
		$controllerObj->setControllerName($controller);
		$controllerObj->setActionName($action);

		$action = $action . 'Action';
		if(!method_exists($controllerObj, $action) || !is_callable(array($controllerObj, $action)))
		{
			throw new \App\Core\Exception\ControllerActionNotExistsException('Page does not exists in this resource.');
		}

		return $controllerObj;
	}

	public function getControllerName($module, $controller)
	{
		$moduleName = ucfirst(strtolower($module));
		$controllerName = ucfirst(strtolower($controller)) . 'Controller';

		return '\\Modules\\' . $moduleName . '\\Controllers\\' . $controllerName;
	}

	public function getControllerFilename($module, $controller)
	{
		$moduleName = ucfirst(strtolower($module));
		$controllerName = ucfirst(strtolower($controller)) . 'Controller';

		return APPLICATION_PATH . 'Modules/' . $moduleName . '/Controllers/' . $controllerName . '.php';
	}

	public function baseUrl($suffix = null)
	{
		// If $suffix initialize with /, remove
		if(strpos($suffix, '/') === 0)
		{
			$suffix = substr($suffix, 1);
		}

		return $this->getConfig('general')->baseUrl . $suffix;
	}

	public function uri()
	{
		$request = new \Slim\Http\Request(\Slim\Environment::getInstance());
		return $request->getResourceUri();
	}

	public function isDispatchable($status = true)
	{
		$this->_isDispatchable = $status;
	}

	public function getIsDispatchable()
	{
		return $this->_isDispatchable;
	}

	/**
	 * This method load all package with manually class loader.
	 * @return \App\Core\Application
	 */
	public function initTools()
	{
		// Ladybug_dump loader
		\Ladybug\Loader::loadHelpers();

		return $this;
	}

	public function getLayout()
	{
	    return $this->_layout;
	}

	public function setLayout($layout)
	{
	    $this->_layout = $layout;

	    return $this;
	}

	public function run()
	{
		$this->getFramework()->run();
	}
}