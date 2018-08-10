<?php

namespace WHMCS\Module\Addon\Jobs\Data;

// DB abstraction class
use WHMCS\Database\Capsule;

// ORM class
use WHMCS\Database\Eloquent\Model;

// https://laravel.com/docs/5.6/eloquent
class Job extends Model {
	// Table associated with the model
	protected $table = 'jobs_joblist';

	// We don't have timestamps on this table
	public $timestamps = false;
}

?>