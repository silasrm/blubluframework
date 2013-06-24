<?php

define('ROOT_DIR', dirname(dirname(__FILE__)) . '/src' );
define('APPLICATION_PATH', ROOT_DIR . '/app/' );
define('DATA_PATH', APPLICATION_PATH . 'data' );
define('PUBLIC_PATH', dirname(__FILE__) );

set_include_path(
	implode(
		PATH_SEPARATOR,
		array(
			ROOT_DIR . '/vendor/',
		)
	)
);

require ROOT_DIR . '/vendor/autoload.php';

defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

$app = new \App\Core\Application(new \Slim\Slim());
$app->run();