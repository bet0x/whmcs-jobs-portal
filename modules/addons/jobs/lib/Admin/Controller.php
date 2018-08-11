<?php

namespace WHMCS\Module\Addon\Jobs\Admin;

// DB abstraction class
use WHMCS\Database\Capsule;

// All addon-related DB models
use WHMCS\Module\Addon\Jobs\Data\Job;
use WHMCS\Module\Addon\Jobs\Data\Applicant;
use WHMCS\Module\Addon\Jobs\Data\Interview;


class Controller {

	// Header output to go before all content
	private function header($vars) {
		$output = '<link rel="stylesheet" type="text/css" href="\modules\addons\jobs\style.css">

		<div class="jobs">

		<div class="adminbar"
		<a href="addonmodules.php?module=jobs"><img src="\modules\addons\jobs\images\computer.png"> Home</a>
		<a href="addonmodules.php?module=jobs&action=viewJobs"><img src="\modules\addons\jobs\images\report_user.png"> View Jobs</img></a>
		<a href="addonmodules.php?module=jobs&action=viewApps"><img src="\modules\addons\jobs\images\group.png"> View Applicants</a>
		<a href="addonmodules.php?module=jobs&action=viewInterviews"><img src="\modules\addons\jobs\images\report.png"> View Interviews</a>
		</div>

		<div class="mainbar"><center><strong>Browse: </strong>
		<a href="addonmodules.php?module=jobs&action=viewJobs">Jobs</a> |
		<a href="addonmodules.php?module=jobs&action=viewApps">Applicants</a> |
		<a href="addonmodules.php?module=jobs&action=viewInterviews">Interviews</a></div>
		';

		return $output;
	}

	// Footer content to go after all pages
	private function footer($vars) {
		$output = '</div>';

		return $output;
	}

	// Index action
	public function index($vars, $post = null, $get = null) {
		// Get common module parameters
        $modulelink = $vars['modulelink'];
        $LANG = $vars['_lang']; // An array of the currently loaded language variables

        // Get the settings needed
        $hrEmail = $vars['hremail'];
        $homeTabText = $vars['hometab'];

        // Get data needed from DB
        $welcomeText = Capsule::table('jobs_settings')->select('setting_val')->where('setting_id', '=', 1)->get();

        $output = '<h2>Change Client Welcome Text</h2>';

		$output .= '<form action="addonmodules.php?module=jobs&action=submitWelcome" method="post"><textarea rows="4" cols="100" name="welcomeText">' . $welcomeText[0] . '</textarea><br /><input type="submit" value"Submit"></input></form>

			<div style="width:50%;float:left;"><h2>Quick Add Job</h2>
				<form action="addonmodules.php?module=jobs&action=submitJobs" method="post">
					<table class="form" width="95%" border="0" cellspacing="2" cellpadding="3">
						<tr><td width="20%" class="fieldlabel"><label for="jobTitle"><strong>Job Title: </strong></label></td><td class="fieldarea"><input type="text" id="jobTitle" name="jobTitle"></input></td></tr>
							<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobRef"><strong>Job Ref (Admin Use Only): </strong></label></td><td class="fieldarea"><input type="text" id="jobRef" name="jobRef"></input></td></tr>
							<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobSalary"><strong>Job Salary: </strong></label></td><td class="fieldarea"><input type="text" id="jobSalary" name="jobSalary"></input></td></tr>
							<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobDesc"><strong>Description: </strong></label></td><td class="fieldarea"><textarea id="jobDesc" name="jobDesc" rows="5" cols="50"></textarea></td></tr>
							<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobDep"><strong>Department: </strong></label></td><td class="fieldarea"><input type="text" id="jobDep" name="jobDep"></input></td></tr>
							<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobReq"><strong>Requirments: </strong></label></td><td class="fieldarea"><textarea id="jobReq" name="jobReq" rows="5" cols="50"></textarea></td></tr>
							<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobAct"><strong>Active: </strong></label></td><td class="fieldarea"><input type="checkbox" name="jobAct" id="jobAct" value="1" checked></input></td></tr>
					</table>
					<input type="submit" value="Submit"></input>
				</form>
			</div>';

		$output .= '<div style="width:50%;float:right;"><h2>Latest Applicants</h2>

			<div class="tablebg"><table class="datatable" id="sortabletbl1" width="100%" border="0" cellspacing="1">
				<tr>
					<th><strong>Applicant ID</strong></th><th><strong>Applicant Forename</strong></th><th><strong>Applicant Surname</strong</th><th><strong>Job Applied For</strong></th>
				</tr></table></div></div>';

		return $this->header($vars) . $output . $this->footer($vars);
	}

	public function addJobs($vars, $post = null, $get = null) {
		// Get common module parameters
        $modulelink = $vars['modulelink'];
        $LANG = $vars['_lang']; // An array of the currently loaded language variables

        // If we are editing an existing record, check it exists then add the data to the form
        if (!is_null($get) && isset($get['jobId'])) {
        	echo 'editing';
        	// Make sure there is a job with the given ID
        	try {
        		$job = Job::findOrFail($get['jobId']);
        	} catch (\Exception $e) {
        		return $this->header($vars) . '<div class="errorbox"><strong>The specified job does not exsist!</strong></div>' . $this->footer($vars);
        	}
        } else {
        	echo 'not editing';
        	$job = new Job;
        }

        $active = ($job->active == 1) ? 'checked' : '';

		$output = '<h2>' . $LANG['addJobsWelcome'] . '</h2>' . '

			<form action="addonmodules.php?module=jobs&action=submitJobs" method="post">
				<input type="hidden" name="id" value="' . $job->id . '">
				<table class="form" width="50%" border="0" cellspacing="2" cellpadding="3">
					<tr><td width="20%" class="fieldlabel"><label for="jobTitle"><strong>Job Title: </strong></label></td><td class="fieldarea"><input type="text" id="jobTitle" name="jobTitle" value="' . $job->title . '"></input></td></tr>
					<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobRef"><strong>Job Ref (Admin Use Only): </strong></label></td><td class="fieldarea"><input type="text" id="jobRef" name="jobRef" value="' . $job->reference . '"></input></td></tr>
					<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobSalary"><strong>Job Salary: </strong></label></td><td class="fieldarea"><input type="text" id="jobSalary" name="jobSalary" value="' . $job->salary . '"></input></td></tr>
					<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobDesc"><strong>Description: </strong></label></td><td class="fieldarea"><textarea id="jobDesc" name="jobDesc" rows="5" cols="50">' . $job->description . '</textarea></td></tr>
					<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobDep"><strong>Department: </strong></label></td><td class="fieldarea"><input type="text" id="jobDep" name="jobDep" value="' . $job->department . '"></input></td></tr>
					<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobReq"><strong>Requirments: </strong></label></td><td class="fieldarea"><textarea id="jobReq" name="jobReq" rows="5" cols="50">' . $job->requirments . '</textarea></td></tr>
					<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobAct"><strong>Active: </strong></label></td><td class="fieldarea"><input type="checkbox" name="jobAct" id="jobAct" value="1" '. $active . '></input></td></tr>
				</table><input type="submit" value="Submit"></input>
			</form>';

		return $this->header($vars) . $output . $this->footer($vars);
	}

	public function submitJobs($vars, $post = null, $get = null) {
		// Get common module parameters
        $modulelink = $vars['modulelink'];
        $LANG = $vars['_lang']; // An array of the currently loaded language variables

		// If no POST variables were sent, return an error
		if (is_null($post)) {
			return $this->header($vars) . '<div class="errorbox"><strong>Invalid data sent. Please try again.</strong></div>' . $this->footer($vars);
		}

		// Get POST vatiables needed
		$jobID = $post['id'];
		$jobTitle = $post['jobTitle'];
		$jobRef = $post['jobRef'];
		$jobDesc = $post['jobDesc'];
		$jobDep = $post['jobDep'];
		$jobReq = $post['jobReq'];
		$jobAct = $post['jobAct'];
		$jobSalary = $post['jobSalary'];

		if ($jobAct = '') {
			$jobAct = 0;
		}

		if (empty($post['id'])) {
			$job = new Job;
		} else {
			$job = Job::find($jobID);
		}
		
		$job->title = $jobTitle;
		$job->reference = $jobRef;
		$job->description = $jobDesc;
		$job->department = $jobDep;
		$job->requirments = $jobReq;
		$job->active = $jobAct;
		$job->salary = $jobSalary;

		try {
			$job->save();
		} catch (\Exception $e) {
			return $this->header($vars) . "<div class='errorbox'><strong>{$LANG['submitJobsUnSuccess']}: {$e->getMessage()}</strong></div>" . $this->footer($vars);
		}

		return $this->header($vars) . '<div class="successbox"><strong>Job Added</strong><br />' . $LANG['submitJobsSuccess'] . '</div>' . $this->footer($vars);
	}

	public function viewJobs($vars, $post = null, $get = null) {
		// Get common module parameters
        $modulelink = $vars['modulelink'];
        $LANG = $vars['_lang']; // An array of the currently loaded language variables

        // Get all jobs in table
        $jobs = Job::all();

        $output = '<h2>' . $LANG['viewJobsWelcome'] . '</h2>

			<div class="tablebg"><table class="datatable" id="sortabletbl1" width="100%" border="0" cellspacing="1">
				<tr><th><strong>Job ID</strong></th><th><strong>Job Title</strong></th><th><strong>Job Ref</strong></th><th><strong>Job Salary</strong></th><th><strong>Job Desc</strong></th>
				<th><strong>Job Dep.</strong></th><th><strong>Job Req</strong></th><th><strong>Job Active?</strong></th>
				<th></th></tr>';

		foreach ($jobs as $job) {
			if ($job->active) {
				$active = 'Yes';
			} else {
				$active = 'No';
			}

			$output .= '<tr><td>' . $job->id . '</td><td>' . $job->title . '</td><td>' . $job->reference . '</td><td>' . $job->salary . '</td><td>' . $job->description . '<td>' . $job->department . '</td><td>' . $job->requirments . '</td><td>' . $active . '</td><td><a href="addonmodules.php?module=jobs&action=addJobs&jobId=' . $job->id . '"><img src="\modules\addons\jobs\images\report_edit.png"></a></td></tr>';
		}

		$output .= '</table></div>';

		return $this->header($vars) . $output . $this->footer($vars);
	}
}

?>