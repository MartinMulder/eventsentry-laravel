<?php

namespace MartinMulder\EventSentry\Laravel\Controllers;

use App\Http\Controllers\Controller;
use MartinMulder\EventSentry\Laravel\SearchOptions;
use MartinMulder\EventSentry\Laravel\Facades\EventSentry;

use Illuminate\Http\Request;

class EventSentryController extends Controller
{
    public function index()
    {
    	$options = new SearchOptions();
        $options->setSearchDateRange('Last week');
        $options->setReportType('useraccountchanges');
        $options->setSearchQuery('(NOT targetDomain: PROVGRON AND NOT targetDomain: ODG)');

        $logs = EventSentry::customSearchQuery('useraccountchanges', $options);
        return view('eventsentry::groupchangelogs', compact('logs'));
    }

    public function showReport($report)
    {
    	$options = new SearchOptions();
        $options->setSearchDateRange('Last week');
        $options->setReportType('useraccountchanges');
        $options->setSearchQuery('(NOT targetDomain: PROVGRON AND NOT targetDomain: ODG)');

        $logs = EventSentry::customSearchQuery('useraccountchanges', $options);
        return view('eventsentry::groupchangelogs', compact('logs', 'report'));
    }

    public function saveSearchOptions(Request $request)
    {
    	if (! $request->has('report'))
    	{
    		
    	}
    }
}
