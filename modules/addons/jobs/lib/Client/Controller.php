<?php

namespace WHMCS\Module\Addon\Jobs\Client;

// DB abstraction class
use WHMCS\Database\Capsule;

// All addon-related DB models
use WHMCS\Module\Addon\Jobs\Data\Job;
use WHMCS\Module\Addon\Jobs\Data\Applicant;

class Controller {

	// Throw a nice error with a custom message and breadcrumb link
	private function error($vars, $title, $message, array $breadcrumbs) {
		return array(
				'pagetitle'		=> $title,
				'breadcrumb'	=> $breadcrumbs,
				'templatefile'	=> 'jobsError',
				'requirelogin'	=> false,
				'vars'			=> array(
									'message'	=> $message
								)
			);
	}

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
								'dep'			=> $department,
								'departments'	=> $departments,
								'numActive'		=> $numActive,
								'jobs'			=> $jobs
							)
		);
	}

	public function apply($vars, $get = null) {
		// Get the settings set by the Admin
		$homeTabText = $vars['homeTab'];
		$hrEmail = $vars['hremail'];

		// If we haven't been given any get parameters then we need to show an error
		if (is_null($get) || !isset($get['job'])) {
			$title = 'Apply - Error';
			$message = 'You must select a job to apply to!';
			$breadcrumbs = array('index.php?m=jobs' => "Vacant Jobs", 'index.php?m=jobs&action=apply' => "Apply - Error");

			return $this->error($vars, $title, $message, $breadcrumbs);
		}

		$jobID = $get['job'];

		// Try to get the selected job
		try {
			$job = Job::findOrFail($jobID);
		} catch (\Exception $e) {
			$title = 'Apply - Error';
			$message = 'The selected job does not exsist!';
			$breadcrumbs = array('index.php?m=jobs' => "Vacant Jobs", 'index.php?m=jobs&action=apply' => "Apply - Error");
			
			return $this->error($vars, $title, $message, $breadcrumbs);
		}

		$title = "Apply to " . $job->title;

		return array(
			'pagetitle'		=> $title,
			'breadcrumb'	=> array('index.php?m=jobs' => "Vacant Jobs", "index.php?m=jobs&action=apply&job=" . $jobID => $title),
			'templatefile'	=> 'jobsApply',
			'requirelogin'	=> false,
			'vars'			=> array(
								'hrEmail' 		=> $hrEmail,
								'job'			=> $job
							)
		);
	}

}

?>