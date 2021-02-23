<?php

return [

	'connections' => [
		'default' => [
			'host' => env('EVENTSENTRY_HOST', ''),
			'user' => env('EVENTSENTRY_USER', ''),
			'password' => env('EVENTSENTRY_PASS', ''), 
		],
	],

	'logging' => env('EVENTSENTRY_LOGGING', true),
	'log_level' => env('EVENTSENTRY_LOGLEVEL', 'debug'),

	'persist' => env('EVENTSENTRY_PERSIST', true),

];