<?php

namespace MartinMulder\EventSentry\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

use MartinMulder\EventSentry\Laravel\Exceptions\EventSentryException;

class GroupChangeLogMessage extends Model
{
	// valid actions
	const MEMBER_REMOVED = 'Member Removed';
	const MEMBER_ADDED = 'Member Added';
	const GROUP_MODIFIED = 'Modified';
	const GROUP_CREATED = 'Created';
	const GROUP_DELETED = 'Deleted';

	// Used for empty view dropdown
	const EMPTYACTION = '';

	public static $allowed_actions = [
		self::EMPTYACTION => self::EMPTYACTION,
		self::MEMBER_REMOVED => self::MEMBER_REMOVED,
		self::MEMBER_ADDED => self::MEMBER_ADDED,
		self::GROUP_MODIFIED => self::GROUP_MODIFIED,
		self::GROUP_CREATED => self::GROUP_CREATED,
		self::GROUP_DELETED => self::GROUP_DELETED
	];

	protected $fillable = ['memberName', 'targetAccountID', 'eventID', 'computerType', 'computer', 'dateTime', 'eventNumber', 'callerDomain', 'action', 'groupType', 'callerAccount', 'callerLogonID', 'sourceIP', 'targetDomain', 'groupScope', 'sourceComputer', 'memberAccounntID', 'accountGroup'];

	protected $hidden = ['created_at', 'updated_at'];

    protected $dates = ['created_at', 'updated_at', 'dateTime'];

    public static function boot()
    {
    	parent::boot();

    	// Sort searches by dateTime
    	static::addGlobalScope('order', function(Builder $builder) {
    		$builder->orderBy('dateTime', 'desc');
    	});

    	// Check if the log message contains an invalid action
    	static::saving(function ($groupchangelogmessage) {
    		if (! in_array($groupchangelogmessage->action, self::$allowed_actions) ) {
    			Log::error('GroupChangeLog action for event ' . $groupchangelogmessage->eventID . ' not allowed: ' . $groupchangelogmessage->action);
    			throw new EventSentryException('GroupChangeLog action not allowed: ' . $groupchangelogmessage->action);
    		}
    	});
    }

    // Define scopes

    // Filter on group
    public function scopeGroup(Builder $builder, $groupName)
    {
    	return $builder->where('accountGroup', '=', $groupName);
    }

    // Filter on multiple groups
    public function scopeOrGroups(Builder $builder, $groups)
    {
    	if (is_array($groups)) {
    		$builder->where(function($builder) use($groups) {
    			foreach($groups as $group) {
    				$builder->orWhere('accountGroup', '=', $group);
    			}
    		});
    	}

    	return $builder;
    }

    // Ignore some EventID's
    public function scopeIgnoreEventID(Builder $builder, $eventID)
    {
    	if (is_numeric($eventID))
    	{
    		$builder->where('eventID', '!=', $eventID);
    	}

    	return $builder;
    }

    // Filter on username
    public function scopeUser(Builder $builder, $user)
    {
    	return $builder->where('memberAccountID', 'like', '%' . $user);
    }

    // Filter on date/time
    public function scopeLastMonth(Builder $builder)
    {
    	$startDate = Carbon::now()->subMonth()->startOfMonth()->toDateTimeString();
    	$endDate = Carbon::now()->subMonth()->endOfMonth()->toDateTimeString();

    	return $builder->whereBetween('created_at', [$startDate, $endDate]);
    }
}