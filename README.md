# READ ME #

Blublu is a miniframework with route, orm, layout and view separated files maked over Slim Framework, with other awesome libraries

## Basic usage ##
Url patterns:

	**site.com/:controller/:action**
	**site.com/:module/:controller/:action**

If module is not passed in url, first url pattern, the app using default module named site

If controller os action is not passed, using default name called index. Exemple:

- **site.com/**
	Call module Site, controller Index and the action is Index
- **site.com/contact**
	Call module Site, controller Contact and the action is Index
- **site.com/contact/result**
	Call module Site, controller Contact and the action is Result

##### Struture of modules: #####

- Default module is: site
	Path: *src/app/Modules/Site/*
- Other modules:
	Path: *src/app/Modules/:module/*

##### Folder structure: #####
- src/app/Modules/**[:module]**/
- src/app/Modules/**[:module]**/Controllers/**[:controller]**Controller.php
- src/app/Modules/**[:module]**/Views/**[:controller]**/**[:action]**.phtml

##### Code structure: #####
	<?php

	namespace Modules\[:module]\Controllers;

	class [:controller]Controller extends \App\Core\Controller
	{
		public function [:action]Action()
		{
			// code
		}
	}

	?>

*Module name and controller name using the first letter in Uppercase.*
*Action name using the first letter in lowercase*
*In view files, controllaer name and action name is in lowercase*

### Methods to using in controller ###
- **$this->getLayout()->isDisabled()**: disable using of layout file
- **$this->setNoRender()**: disable using view
- **$this->getRequest()**: collection os methods for using with request data (this method is a Slim\Request)
- **$this->getRequest()->isPost()**: true, if is a POST request

*In controllers the **_init()** method is call early of the action method.*

### Methods to using in view/layout ###
- **$this->url($array, $routeName)**: make a url with data in $array using pattern of $routeName
- **$this->baseUrl($suffix|null)**: return baseUrl value, this is setted in configs/general.php. If using $suffix, $suffix is concatened before return.
- **$this->partial($file, $data)**: include/return $file content passing $data value to using inside $file

### CSS and Javascript ###
This configurations is setted in configs/assets.php. This configuration structure is like the structure of methods/controllers/action, with options to load files in all request, all module requests, all controller requests. Example:

	return array(
		// Type of assets. Other is 'css'
		'js' => array(
			// Loaded in all requests with layout
			'all' => array(
				'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js',
				'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.js',
				'/js/plugins/modals.js',
				'/js/plugins/ajaxSubmit.js',
				'/js/plugins/ajaxClick.js',
			),
			// Files loaded in site module
			'site' => array(
				// In all requests (actions of all controllers)
				'all' => array(
				),
				// Files loaded in index controller
				'index' => array(
					// In all actions of index controller
					'all' => array(
					),
					// In index action, no more other.
					'index' => array(
						'/js/page/pagesOrder.js',
					),
				),

#### Using Munne project with JS and CSS ####
[Munne](https://github.com/meenie/munee) provide concatenation and minification of javascript and css files, and on the fly imagens resizing. To enable, in configs/general.php using:

	'assets' => array(
		'engine' => 'munee',
		'options' => array(
			'minify' => true, // TRUE to pass minify=true to Munee
			'explicit' => true, // TRUE to add munee path explicity in html tag, if you don't use .htaccess configuration to redirect assets files to munne.php
		),
	),

### Debug with dump ###
Use ladybug dump to dump data. Example: **\ladybug_dump()** or **\ladybug_dump_die()** and other, see ladybug dump documentation.

### Database using: ###
* [Documentation of Idiorm (ORM)](http://idiorm.readthedocs.org/)
* [Documentation of Paris (ActiveRecord)](http://paris.readthedocs.org/)

## Tools inside framework ##
This framework is maked over Slim Framework and using other awesome libraries to make him stronggest:

* "rmccue/requests" => HTTP Request tool
* "padraic/security-multitool" => Many security tools staying to used
* "ircmaxell/password-compat" => password_* PHP 5.5 functions compatibility for PHP versions early than 5.5
* "devster/ubench" => Benchmark
* "meenie/Munee" => Assets manager
* "robmorgan/phinx" => Migrations manager
* "respect/validation" => Validation engine
* "tedivm/stash" => Cache manager
* "raulfraile/ladybug" => Dump library
* "nesbot/Carbon" => DateTime extension
* "j4mie/paris" => A lightweight Active Record implementation for PHP5, built on top of Idiorm

## Installation ##

Using composer:

	composer.phar install

Edit configurations files located in src/app/configs/ folder.