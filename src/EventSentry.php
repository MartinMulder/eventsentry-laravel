<?php

namespace MartinMulder\EventSentry\Laravel;

use MartinMulder\EventSentry\Laravel\SearchOptions;
use MartinMulder\EventSentry\Laravel\QueryBuilder;
use MartinMulder\EventSentry\Laravel\Exceptions\EventSentryException;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ConnectException;

use MartinMulder\EventSentry\Laravel\Endpoints\Logs;
use MartinMulder\EventSentry\Laravel\Endpoints\CustomSearch;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class EventSentry 
{
	// Use the actual API calls
	use Logs, CustomSearch;

	// Get the last fetch date
	protected $cache_keys = [

	];

	//const CACHE_KEY_NETWORKLOGONS = 'LAST_FETCH_NETWORKLOGONS';
	//const CACHE_KEY_LOGONFAILURES = 'LAST_FETCH_LOGONFAILURES';
	//const CACHE_KEY_GROUPACCOUNTCHANGES = 'LAST_FETCH_GROUPACCOUNTCHANGES';
	//const CACHE_KEY_USERACCOUNTCHANGES = 'LAST_FETCH_USERACCOUNTCHANGES';
	//const CACHE_KEY_DELIMITEDLOGFILES = 'LAST_FETCH_DELIMITEDLOGFILES';


	// Endpoint info
	private $apiUrl;
	private $user;
	private $pass;

	private $logger = null;

	public static $reportTypes = [
		'networkLogons' => 'networkLogons',
		'logonfailures' => 'logonfailures',
		'groupaccountchanges' => 'groupaccountchanges',
		'useraccountchanges' => 'useraccountchanges',
		'delimitedlogfiles' => 'delimitedlogfiles',
	];

	// Default search limit
	private $searchLimit = -1;

	// Default search type
	private $searchType = 'detailed';

	// Default search date range
	private $searchDateRange = 'Today';

	// Generated searchQuery
	private $searchQuery = 'account:SVC_OTAP_ADMIN';

	// Default search sort
	private $searchSort  = "desc";

	// Holds the curl instance to make api calls
	private $httpClient = null;

	// Default profile to use (security)
	private $profile = 5;

	// shouldLog
	private $shouldLog = false;

	public function __construct($apiUrl, $user, $pass, $searchLimit = -1, $searchType = "detailed")
	{
		$this->shouldLog = config('eventsentry.logging', true);

		$guzzleOptions = [];

		if ($this->shouldLog) {
			$guzzleOptions['debug'] = true;
		}

		// Enable logging
		if ( $this->shouldLog ) {
			if (! is_null($this->logger = Log::getFacadeRoot())) {
				$this->logger->debug('Logger initialized');
			}
		}

		// Set the contructor arguments
		$this->apiUrl = $apiUrl;
		$this->user = $user;
		$this->pass = $pass;
		$this->searchLimit = $searchLimit;
		$this->searchType = $searchType;

		// Create a httpClient
		$this->httpClient = new Client(array_merge([
            'base_uri' => $this->apiUrl,
            'verify' => false,
            'cookies' => true,
        ], $guzzleOptions));


 		/** @var HandlerStack $handler */
        $handler = $this->httpClient->getConfig('handler');
        $handler->unshift($this->cookieMiddleware());
       
		$this->switchProfile($this->profile);
	}

	/** 
	 * EventSentry defaults to the BTI profile
	 * The SIEM uses the Security profile, so we need to switch to this.
	 */
	private function switchProfile($id)
	{
		if (is_numeric($id)) {
			$this->callAPI('GET', "profile/switch/" . $id);
		} else {
			throw new EventSentryException('Failed to switch to profile: ' . $id);
		}
	}

	/**
     * Shorthand function to create requests with JSON body and query parameters.
     * @param $method
     * @param string $uri
     * @param array $json
     * @param array $query
     * @param array $options
     * @param boolean $decode JSON decode response body (defaults to true).
     * @return mixed|ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function callAPI($method, $uri = '', array $json = [], array $query = [], array $options = [], $decode = true)
    {
    	if ($uri != "profile/switch/5")
    	{

    	}
    	
    	try {
	    	$response = $this->httpClient->request($method, $this->apiUrl . '/' . $uri, array_merge([
	            'json' => $json,
	            'query' => $query,
	            'auth' => [$this->user,$this->pass],
	        ], $options));	
    	} catch(\GuzzleHttp\Exception\ClientException $e) {
    		$this->log('Failed to call EventSentry API: ' . $uri . " http code: " . $e->getCode() . ' found');
    		dd ($e);
    	}
        

        // Debug the response
        // dd($response);

        return $decode ? json_decode((string)$response->getBody(), true) : (string)$response->getBody();
    }

    // Call the api based on a report
    protected function callReport($method, $report = '', array $json = [], SearchOptions $query = null, array $options = [], $decode = true)
    {

    	// Create a new SearchOptions object if not defined
    	if (! $query) {
    		$query = new SearchOptions();
    	}

    	// Get the cache key 
    	if (self::getCacheKey($report))
    	{
    		$start = Cache::get(self::getCacheKey($report), Carbon::now()->subDay(1));
    		$this->log('Found cache_key: ' . self::getCacheKey($report) . " start: "  . $start->toDateTimeString(), 'debug');
    	} else {
    		// fetch logs from a single day
    		$start = Carbon::now()->subDay(1);
    	}


    	return $this->callAPI($method, $report . '/json', $json, $query->getOptions(), $options, $decode);
    }

    /**
     * Middleware: Add a cookiejar to the connection to limit authentication requests
     * @return callable
     */
    private function cookieMiddleware()
    {
        return Middleware::cookies();
    }

    private function createOptionsUrl($options)
        {
                //throw new Exeption('No options array given') if (! is_array($options) );

                $output = implode('&', array_map(
                    function ($v, $k) { return sprintf("%s=%s", $k, $v); },
                    $options,
                    array_keys($options)
                ));

                return $output;
        }

    private static function getCacheKey($report)
    {
    	if (in_array($report, self::$reportTypes))
    	{
    		return $cache_key = Str::upper('cache_key_' . self::$reportTypes[$report]);
    	} else {
    		throw new EventSentryException('Report type not found: ' . $report);
    	}
    }

    private function log($message, $level = 'error')
    {
    	if ( $this->shouldLog ) {
			if (! is_null($this->logger = Log::getFacadeRoot())) {
				$this->logger->$level($message);
			}
		}
    }

}