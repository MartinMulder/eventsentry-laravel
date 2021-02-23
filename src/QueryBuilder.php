<?php

namespace MartinMulder\EventSentry\Laravel;

use MartinMulder\EventSentry\Laravel\Exceptions\EventSentryException;

class QueryBuilder
{
	private $field;
	private $operator;

	public function __construct($field, $operator = "and")
	{
		$this->field = $field;

		if (strtolower($operator) == 'or')
		{
			$this->operator = 'or';
		} else {
			$this->operator = 'and';
		}
	}

	public function buildQuery(Array: $types)
	{
		$q = $this->field . ":";

		if (! is_array($types))
		{
			throw new EventSentryException('Types should be an array');
		} else {
			if (count($types) > 1)
			{
				$q .= "(";
				$q .= implode(" ", strtoupper($this->operator) . " ", array_map(
					function ($key, $value) {
						return sprintf("%s", $value);
					}, $types, array_keys($types))
				);
				$q .= ")";
			} else {
				$q .= $types[0];
			}
		}

		return $q;																						
	}
}