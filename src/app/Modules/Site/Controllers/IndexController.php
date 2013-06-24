<?php

namespace Modules\Site\Controllers;

class IndexController extends \App\Core\Controller
{
	public function indexAction()
	{
		$this->view->world = 'World';
	}

	public function norenderAction()
	{
		$this->setNoRender();

		echo 'Print content of action with no view render';
	}

	public function nolayoutAction()
	{
		$this->getLayout()->isDisabled();
	}
}