<?php

namespace Modules\Site\Controllers;

class ErrorController extends \App\Core\Controller
{
	public function indexAction(){}

	public function error404Action()
	{
		$url = $this->getRequest()->getResourceUri();
		if($this->getRequest()->getResourceUri() === '/404')
		{
			$url = '';
		}

		$this->view->url = $url;
	}
}