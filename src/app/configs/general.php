<?php

return array(
	'mode' => 'production',
	'debug' => false,
	'view' => '\App\Core\Layout\Slim',
	'layout' => 'layouts/layout.phtml',
	'log' => array(
		'log.enable' => true,
		'log.level' => \Slim\Log::INFO,
		'log.writer' => new \Slim\Extras\Log\DateTimeFileWriter(
			array(
				'path' => DATA_PATH . '/logs',
			)
		),
	),
	'assets' => array(
		// none or false indicate no one engine, OR munee for use Munee project (https://github.com/meenie/munee)
		// 'engine' => 'none',
		'engine' => 'munee',
		'options' => array(
			'minify' => true, // TRUE to pass minify=true to Munee
			'explicit' => true, // TRUE to add munee path explicity in html tag
		),
	),
	'baseUrl' => '/',
	'db' => array(
		'host' => 'localhost',
		'dbname' => '',
		'username' => '',
		'password' => '',
	),
);