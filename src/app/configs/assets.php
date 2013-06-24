<?php

return array(
	'js' => array(
		// Loaded in all requests with layout
		'all' => array(
			'http://code.jquery.com/jquery-2.0.0.min.js'
		),
		// Module name
		'site' => array(
			// Loaded in all controllers requests with layout
			'all' => array(
				'/assets/js/main.js'
			),
			// Controller name
			'index' => array(
				// Loaded in all actions requests with layout
				'all' => array(
					// Files not exists, but not loaded. So for example.
					'/assets/js/1.js',
					'/assets/js/2.js',
					'/assets/js/3.js',
				),
				// Action name
				'index' => array(
					'/assets/js/slide.js',
				),
			),
		),
	),
	'css' => array(
		// Loaded in all requests with layout
		'all' => array(
			'/assets/css/main.css'
		),
		// Module name
		'site' => array(
			// Controller name
			'index' => array(
				// Loaded in all actions
				'all' => array(
					// Files not exists, but not loaded. So for example.
					'/assets/css/1.css',
					'/assets/css/2.css',
					'/assets/css/3.css',
				),
				// Action name
				'index' => array(
					// Files not exists, but not loaded. So for example.
					'/assets/css/slide.css',
				),
			),
		),
	),
);