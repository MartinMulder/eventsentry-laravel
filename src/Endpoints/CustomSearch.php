<?php

namespace MartinMulder\EventSentry\Laravel\Endpoints;

use MartinMulder\EventSentry\Laravel\SearchOptions;
use MartinMulder\EventSentry\Laravel\Exceptions\EventSentryException;

use Illuminate\Support\Facades\Log;

trait CustomSearch
{
	public function customSearchQuery($reportType, SearchOptions $options = null)
	{
		if (! in_array($reportType, self::$reportTypes))
		{
			throw new EventSentryException("ReportType not supported: " . $reportType);
		} 

		if (! $options)
		{
			$options = new SearchOptions();
		}

		if (! $options->getSearchQuery())
		{
			$query = '';
			$options->setSearchQuery($query);
		}

		if ($this->shouldLog)
		{
			Log::debug("Calling URL: " . $this->apiUrl);
		}

		return $this->callReport('GET', self::$reportTypes[$reportType], [], $options);
	}
}