<?php

namespace WHMCS\Module\Addon\Jobs\Client;

// DB abstraction class
use WHMCS\Database\Capsule;

// All addon-related DB models
use WHMCS\Module\Addon\Jobs\Data\Job;
use WHMCS\Module\Addon\Jobs\Data\Applicant;

class Controller {

	public function index($vars, $get = null) {
		// Get the settings set by the Admin
		$homeTabText = $vars['homeTab'];
		$hrEmail = $vars['hremail'];

		// Get all possible departments from the database
		$departments = Capsule::table('jobs_joblist')
								->select('department')
								->groupBy('department')
								->get();

		// Get the number of active jobs
		$numActive = Job::where('active', 1)->count();

		if (!is_null($get) && isset($get['dep'])) {
			$department = $get['dep'];

			// Get all active jobs in the selected department
			$jobs = Job::where([
				['active', '=', 1], 
				['department', '=', $department]
			])->get();
		} else {
			$department = '';
			$jobs = false;
		}

		return array(
			'pagetitle'		=> 'Vacant Jobs',
			'breadcrumb'	=> array('index.php?m=jobs' => "Vacant Jobs"),
			'templatefile'	=> 'jobsIndex',
			'requirelogin'	=> false,
			'vars'			=> array(
								'homeTabText' 	=> $homeTabText, 
								'hrEmail' 		=> $hrEmail,
								'dep'			=> $department,
								'departments'	=> $departments,
								'numActive'		=> $numActive,
								'jobs'			=> $jobs
							)
		);
	}

}

?>