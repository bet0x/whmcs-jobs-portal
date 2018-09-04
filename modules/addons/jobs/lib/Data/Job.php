<?php

/**
* @package    WHMCS
* @author     Matthew Watson
* @copyright  Copyright (c) Matthew Watson
* @version    1.9
* @link       https://www.base.envesko.com
*/

namespace WHMCS\Module\Addon\Jobs\Data;

// DB abstraction class
use WHMCS\Database\Capsule;

// ORM class
use WHMCS\Model\AbstractModel;

// https://laravel.com/docs/5.6/eloquent
class Job extends AbstractModel {
	// Table associated with the model
	protected $table = 'jobs_joblist';

	// We don't have timestamps on this table
	public $timestamps = false;
}

?>