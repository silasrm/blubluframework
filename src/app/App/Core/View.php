<?php

/**
 * This class contains many methods to manage view files and pass parameters to them.
 * @package App
 * @subpackage App\Core
 */
namespace App\Core;

class View
{
	protected $_application = null;
	protected $_data = null;

	public function __construct($application)
	{
		$this->setApplication($application);
		$this->setData(new \stdClass);
		$this->app = $this->getApplication();
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

	public function getData()
	{
	    return $this->_data;
	}

	public function setData($data)
	{
	    $this->_data = $data;

	    return $this;
	}

	public function url(array $parameters = null, $route = 'default')
	{
		return $this->getApplication()->getLayout()->url($parameters, $route);
	}

	public function __set($key, $value)
	{
		$this->_data->$key = $value;

		return $this;
	}

	public function __get($key)
	{
		return $this->_data->$key;
	}
}