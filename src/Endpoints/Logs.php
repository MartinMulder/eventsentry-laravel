<?php

namespace MartinMulder\EventSentry\Laravel\Endpoints;

use MartinMulder\EventSentry\Laravel\SearchOptions;

trait Logs {

	public function getLogs()
	{
		// Buidl a search obkect
		$options = new SearchOptions();

		$options->setSearchType($this->searchType);
		$options->setSearchSort($this->searchSort);
		$options->setSearchDateRange('Today');
		$options->setSearchQuery('account:U399');

		// Get the last fetch date
		//$last = Cache::
		// $options = [
		// 	'search.type' => $this->searchType,
		// 	'search.sort' => $this->searchSort,
		// 	'search.dateRange' => urlencode($this->searchDateRange),
		// 	'search.query' => '',
		// ];

		return $this->callReport('GET', self::$reportTypes['networkLogons'], [], $options);
	}
}