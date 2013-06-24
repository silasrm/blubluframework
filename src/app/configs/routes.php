<?php

return array(
	'error404' => array(
		'/404',
		array('module' => 'site', 'controller' => 'error', 'action' => 'error404')
	),
	'default' => array(
		'/(:controller(/:action))(/)?',
		array('module' => 'site')
	),
	'default_full' => array(
		'/(:module(/:controller(/:action)))(/)?',
		array()
	),
);