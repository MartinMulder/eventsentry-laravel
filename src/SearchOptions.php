<?php

namespace MartinMulder\EventSentry\Laravel;

use MartinMulder\EventSentry\Laravel\Exceptions\EventSentryException;

class SearchOptions
{
	// Defaults
	private $searchLimit = -1;
	private $searchType = 'default';
	private $reportType = 'groupaccountchanges';
	private $searchSort = 'DESC';

	// Search for periode
	private $searchDateRange = 'today';
	private $searchFromDate = null;
	private $searchFromTime = null;
	private $searchToDate = null;
	private $searchToTime = null;

	// The query
	private $searchQuery = null;

	// Allowed options
	public static $allowedSort = ['desc' => 'desc', 'asc' => 'asc'];
	public static $allowedDateRange = ['Last 3 hours' => 'Last 3 hours', 'Today' => 'Today', 'Yesterday' => 'Yesterday', 'Last week' => 'Last week', 'Custom' => 'Custom'];
	public static $allowedType = ['detailed' => 'detailed'];

	public function setSearchSort($sort)
	{
		if (!in_array($sort, self::$allowedSort))
		{
			throw new EventSentryException("Invalid sort type: " . $sort);
		} else {
			$this->searchSort = $sort;
		}
	}	
	
	public function setSearchType($type)
	{
		if (!in_array($type, self::$allowedType))
		{
			throw new EventSentryException("Invalid type: " . $type);
		} else {
			$this->searchType = $type;
		}
	}	

	public function setSearchDateRange($range, $custom = null)
	{
		if (!in_array($range, self::$allowedDateRange))
		{
			throw new EventSentryException('Invalid dateRange: ' . $range . " allowed values: [" . implode(',', self::$allowedDateRange) . "]");
		} else {
			$this->searchDateRange = $range;

			if ($range == 'Custom')
			{
				try {
					$this->searchFromDate = $custom['searchFromDate'];
					$this->searchFromTime = $custom['searchFromTime'];
					$this->searchToDate = $custom['searchToDate'];
					$this->searchToTime = $custom['searchToTime'];
				} catch(\Exception $e) {
					throw new EventSentryException('Invalid custom date/time range');
				}
			} else {
				// Clear fields
				$this->searchFromDate = null;
				$this->searchFromTime = null;
				$this->searchToDate = null;
				$this->searchToTime = null;
			}
		}
	}

	public function setSearchLimit($limit)
	{
		if (! is_numeric($limit))
		{
			throw new EventSentryException("Invalid searchLimit: " . $limit);
		} else {
			$this->searchLimit = $limit;
		}
	}

	public function getReportType()
	{
		return $this->reportType;
	}

	public function setReportType($type)
	{
		if (! in_array($type, EventSentry::$reportTypes))
		{
			throw new EventSentryException('Invalid reportType: ' . $type);
		} else {
			$this->reportType = $type;
		}
	}

	public function getEncodedSearchQuery()
	{
		return urlencode($this->searchQuery);
	}

	public function getSearchQuery()
	{
		return $this->searchQuery;
	}

	public function setSearchQuery($query)
	{
		$this->searchQuery = $query;
	}

	public function getOptions()
	{
		$opts = [
			'search.type' => $this->searchType,
			'search.dateRange' => $this->searchDateRange,
			'search.sort' => $this->searchSort,
			'search.limit' => $this->searchLimit,
			'search.order' => 'recorddate',
		];

		if ($this->searchDateRange == "Custom")
		{
			$opts['search.fromDate'] = $this->searchFromDate;
			$opts['search.fromTime'] = $this->searchFromTime;
			$opts['search.toDate'] = $this->searchToDate;
			$opts['search.toTime'] = $this->searchToTime;
		}

		$opts['search.query'] = $this->getSearchQuery();

		return $opts;
	}

/*
	public function getOptionsUrl()
	{
		$options = $this->getOptions();
		$output = implode('&', array_map(
			function ($v, $k) { return sprintf("%s=%s", $k, $v); },
			$options,
			array_keys($options)
		));

		return $output;
	}
*/
}
