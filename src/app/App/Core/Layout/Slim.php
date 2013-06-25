<?php

/**
 * This class manage layout file and view file.
 * @package App
 * @subpackage App\Core\Layout
 */
namespace App\Core\Layout;

class Slim extends \Slim\View implements LayoutInterface
{
	protected $_disabled = false;
	protected $_application = null;
	protected $_layout = null;

	public function __construct($application)
	{
		$this->setApplication($application);
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

	public function getDisabled()
	{
		return $this->_disabled;
	}

	public function setDisabled($disabled)
	{
		$this->_disabled = $disabled;

		return $this;
	}

	public function isDisabled($disabled = true)
	{
		$this->setDisabled($disabled);

		return $this;
	}

	public function url(array $parameters = null, $route = 'default')
	{
		$url = $this->getApplication()->getFramework()->urlFor($route, $parameters);

		if(substr($url, -1) === '?')
		{
			$url = substr($url, 0, -1);
		}

		return $url;
	}

	public function baseUrl($suffix = null)
	{
		return $this->getApplication()->baseUrl($suffix);
	}

	public function unsetData($key)
	{
		unset($this->data[$key]);
	}

	public function fetch($template)
	{
		$layout = $this->getLayout();
		$this->unsetData('layout');
		$result = $this->render($template);

		if(!$this->getDisabled())
		{
			if(is_string($layout))
			{
				$result = $this->renderLayout($layout, $result);
			}
		}

		return $result;
	}

	public function setLayout($filename)
	{
		$this->_layout = 'layouts/' . $filename . '.phtml';

		return $this;
	}

	public function getLayout()
	{
		$layout = $this->getData('layout');
		if(is_null($layout))
		{
			$layout = $this->_layout;
		}

		if(is_null($layout))
		{
			$app = $this->getApplication()->getFramework();
			if(isset($app))
			{
				$layout = $app->config('layout');
			}
		}

		if(is_null($layout))
		{
			$layout = self::DEFAULT_LAYOUT;
		}

		return $layout;
	}

	protected function renderLayout($layout, $layoutContent)
	{
		$currentTemplate = $this->templatePath;
		$this->setData('layoutContent', $layoutContent);
		$result = $this->render($layout);
		$this->templatePath = $currentTemplate;
		$this->unsetData('layoutContent');

		return $result;
	}

	public function renderNoView($viewBasePath)
	{
		$content = ob_get_clean();

		if(!$this->getDisabled())
		{
			$this->setTemplatesDirectory($viewBasePath);
			$content = $this->renderLayout($this->getLayout(), $content);
		}

		echo $content;
	}

	public function partial($viewFile, array $data = null)
	{
		$fullpathFile = $this->getTemplatesDirectory() . '/' . $viewFile;

		if(!file_exists($fullpathFile))
		{
			throw new \App\Core\Exception\ViewNotExistsException('View file not exists: ' . $fullpathFile);
		}
		$data['_baseUrl'] = $this->baseUrl();
		extract($data);
        ob_start();
        require $fullpathFile;
        return ob_get_clean();
	}
}