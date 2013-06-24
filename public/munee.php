<?php

define('ROOT_DIR', dirname(dirname(__FILE__)) . '/src' );

require ROOT_DIR . '/vendor/autoload.php';

echo \Munee\Dispatcher::run(new \Munee\Request());