<?php

namespace MartinMulder\EventSentry\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class EventSentry extends Facade
{
	protected static function getFacadeAccessor()
	{
		return "eventsentry";
	}
}