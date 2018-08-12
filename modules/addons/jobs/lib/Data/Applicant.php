<?php

namespace WHMCS\Module\Addon\Jobs\Data;

// DB abstraction class
use WHMCS\Database\Capsule;

// ORM class
use WHMCS\Model\AbstractModel;

// https://laravel.com/docs/5.6/eloquent
class Applicant extends AbstractModel {
	// Table associated with the model
	protected $table = 'jobs_applicants';

	// We don't have timestamps on this table
	public $timestamps = false;
}

?>