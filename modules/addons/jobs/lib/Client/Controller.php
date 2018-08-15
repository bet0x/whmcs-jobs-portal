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
								'job'			=> $job
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
		$skype = $post['skype'];
		$email = $post['email'];
		$jobID = $post['jobId'];
		$why = $post['why'];
		$exp = $post['exp'];
		$confirm = $post['confirm'];

		// Make sure all fields were completed
		if ($fName == '' || $lName == '' || $skype == '' || $email == '' || $why == '' || $exp == '') {
			$title = "Apply - Error";
			$message = "Not all information was submitted. Please try again.";
			$breadcrumbs = array('index.php?m=jobs' => "Vacant Jobs", "index.php?m=jobs&action=apply&job=" . $jobID => $title, "index.php?m=jobs&action=submitInfo" => "Apply - Error");

			return $this->error($vars, $title, $message, $breadcrumbs);
		} elseif ($confirm = '') {
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
		$app->address = $skype;
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
		$message = "{$fName} {$lName} has applied for a position at {$vars['companyname']}. Here are their details:
				<br />
				<br />
				<strong>Name: </strong>{$fname} {$lName}<br />
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
								'exp'			=> $exp
							)
		);
	}
}

?>