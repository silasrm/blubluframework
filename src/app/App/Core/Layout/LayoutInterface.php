<?php

/**
 * This interface define properties about layouts manager.
 * @package App
 * @subpackage App\Core\Layout
 */
namespace App\Core\Layout;

interface LayoutInterface
{
	const DEFAULT_LAYOUT = 'layouts/layout.phtml';

	public function getApplication();
	public function setApplication($application);
	public function getDisabled();
	public function setDisabled($disabled);
	public function isDisabled($disabled = true);
	public function render($template);
	public function url(array $parameters = null, $route = 'default');
	public function baseUrl($suffix = null);
	public function partial($viewFile, array $data = null);
}