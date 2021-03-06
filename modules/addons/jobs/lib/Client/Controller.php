<?php

/**
* @package    WHMCS
* @author     Matthew Watson
* @copyright  Copyright (c) Matthew Watson
* @version    1.9
* @link       https://www.base.envesko.com
*/

namespace WHMCS\Module\Addon\Jobs\Client;

// DB abstraction class
use WHMCS\Database\Capsule;

// All addon-related DB models
use WHMCS\Module\Addon\Jobs\Data\Job;
use WHMCS\Module\Addon\Jobs\Data\Applicant;

// License checking helper
use WHMCS\Module\Addon\Jobs\Helper\LicenseHelper;

class Controller {

	public function __construct($vars) {
		// Check that the license is valid
		$license = $vars['license'];

		$localKeyRow = Capsule::table('jobs_settings')
							->where('setting_name', 'localkey')
							->get();

		$results = LicenseHelper::checkLicense($license, $localKeyRow->setting_val);

		if ($results['status'] != 'Active') {
			// Throw an error if the license is not active
			throw new \Exception("Your license key is {$results['status']}!");
		}

		// If it is valid, get the local key and store it in the DB
		$localKey = $results['localkey'];
		try {
			// Insert a default value into the setting for the local key
			Capsule::table('jobs_settings')
					->where('setting_name', 'localkey')
					->update(['setting_val' => $localKey]);
		} catch (\Exception $e) {
			throw new \Exception("Error updating localkey: {$e->getMessage()}");
		}
	}

	// Throw a nice error with a custom message and breadcrumb link
	private function error($vars, $title, $message, array $breadcrumbs) {
		$homeTabText = $vars['homeTab'];

		return array(
				'pagetitle'		=> $title,
				'breadcrumb'	=> $breadcrumbs,
				'templatefile'	=> 'jobsError',
				'requirelogin'	=> false,
				'vars'			=> array(
									'message'	=> $message,
									'homeTabText' 	=> $homeTabText,
								)
			);
	}

	public function index($vars, $get = null, $post = null) {
		// Get the settings set by the Admin
		$homeTabText = $vars['homeTab'];
		$hrEmail = $vars['hremail'];
		$welcomeText = $vars['welcomeText'];

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
								'welcomeText'	=> $welcomeText,
								'dep'			=> $department,
								'departments'	=> $departments,
								'numActive'		=> $numActive,
								'jobs'			=> $jobs
							)
		);
	}

	public function apply($vars, $get = null, $post = null) {
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
								'job'			=> $job,
								'homeTabText' 	=> $homeTabText
							)
		);
	}

	public function submitInfo($vars, $get = null, $post = null) {
		// Get the settings set by the Admin
		$homeTabText = $vars['homeTab'];
		$hrEmail = $vars['hremail'];

		// Make sure information was submitted
		if (is_null($post) || !isset($post['jobId'])) {
			$title = "Apply - Error";
			$message = "No information was submitted!";
			$breadcrumbs = array('index.php?m=jobs' => "Vacant Jobs", "index.php?m=jobs&action=submitInfo" => "Apply - Error");

			return $this->error($vars, $title, $message, $breadcrumbs);
		}

		$fName = $post['fName'];
		$lName = $post['lName'];
		$email = $post['email'];
		$jobID = $post['jobId'];
		$why = $post['why'];
		$exp = $post['exp'];
		$confirm = $post['confirm'];

		// Make sure all fields were completed
		if ($fName == '' || $lName == '' || $email == '' || $why == '' || $exp == '') {
			$title = "Apply - Error";
			$message = "Not all information was submitted. Please try again.";
			$breadcrumbs = array('index.php?m=jobs' => "Vacant Jobs", "index.php?m=jobs&action=apply&job=" . $jobID => $title, "index.php?m=jobs&action=submitInfo" => "Apply - Error");

			return $this->error($vars, $title, $message, $breadcrumbs);
		} elseif ($confirm == '' || !isset($post['confirm'])) {
			$title = "Apply - Error";
			$message = "You did not accept our terms and conditions. Please read them and try again.";
			$breadcrumbs = array('index.php?m=jobs' => "Vacant Jobs", "index.php?m=jobs&action=apply&job=" . $jobID => $title, "index.php?m=jobs&action=submitInfo" => "Apply - Error");

			return $this->error($vars, $title, $message, $breadcrumbs);
		}

		// Try to add the applicant to the system
		$app = new Applicant;
		$app->fname = $fName;
		$app->lname = $lName;
		$app->email = $email;
		$app->jobid = $jobID;
		$app->why = $why;
		$app->experience = $exp;

		try {
			$app->save();
		} catch (\Exception $e) {
			$title = "Apply - Error";
			$message = "There was an unspecified error saving your application. Please try again.";
			$breadcrumbs = array('index.php?m=jobs' => "Vacant Jobs", "index.php?m=jobs&action=apply&job=" . $jobID => $title, "index.php?m=jobs&action=submitInfo" => "Apply - Error");

			return $this->error($vars, $title, $message, $breadcrumbs);
		}

		// If we've gotten this far then the application has been successfully submitted
		$job = Job::find($jobID);

		// Send an email to HR notifying them of an applicant
		$message = "{$fName} {$lName} has applied for a position. Here are their details:
				<br />
				<br />
				<strong>Name: </strong>{$fName} {$lName}<br />
				<strong>Position: </strong>{$job->title}<br />
				<br />
				You can find the rest of their details in the Admin Control Panel.";
		$headers = "From: {$hrEmail}\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		mail($hrEmail, 'New Applicant Notification', $message, $headers);

		return array(
			'pagetitle'		=> $title,
			'breadcrumb'	=> array('index.php?m=jobs' => "Vacant Jobs", "index.php?m=jobs&action=apply&job=" . $jobID => $title),
			'templatefile'	=> 'jobsSubmit',
			'requirelogin'	=> false,
			'vars'			=> array(
								'hrEmail' 		=> $hrEmail,
								'app'			=> $app,
								'exp'			=> $exp,
								'homeTabText' 	=> $homeTabText
							)
		);
	}
}

?>