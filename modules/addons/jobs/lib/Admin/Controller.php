<?php

/**
* @package    WHMCS
* @author     Matthew Watson
* @copyright  Copyright (c) Matthew Watson
* @version    1.9
* @link       https://www.base.envesko.com
*/

namespace WHMCS\Module\Addon\Jobs\Admin;

// DB abstraction class
use WHMCS\Database\Capsule;

// All addon-related DB models
use WHMCS\Module\Addon\Jobs\Data\Job;
use WHMCS\Module\Addon\Jobs\Data\Applicant;
use WHMCS\Module\Addon\Jobs\Data\Interview;

// License checking helper
use WHMCS\Module\Addon\Jobs\Helper\LicenseHelper;

// WHMCS-provided model for tbladmins
use WHMCS\User\Admin;

class Controller {

	public function __construct($vars) {
		$LANG = $vars['_lang'];

		// Check that the license is valid
		$license = $vars['license'];

		$localKeyRow = Capsule::table('jobs_settings')
							->where('setting_name', 'localkey')
							->get();

		$results = LicenseHelper::checkLicense($license, $localKeyRow->setting_val);

		if ($results['status'] != 'Active') {
			// Throw an error if the license is not active
			throw new \Exception("{$LANG['licenseKeyStatus']} {$results['status']}!");
		}

		// If it is valid, get the local key and store it in the DB
		$localKey = $results['localkey'];
		try {
			// Insert a default value into the setting for the local key
			Capsule::table('jobs_settings')
					->where('setting_name', 'localkey')
					->update(['setting_val' => $localKey]);
		} catch (\Exception $e) {
			throw new \Exception("{$LANG['licenseKeyError']} {$e->getMessage()}");
		}
	}

	private function error($title, $message) {
		$output = "<div class='errorbox'>
						<strong><span class='title'>{$title}</span></strong>
						<br />
						{$message}
					</div>";

		return $output;
	}

	private function success($title, $message) {
		$output = "<div class='successbox'>
						<strong><span class='title'>{$title}</span></strong>
						<br />
						{$message}
					</div>";

		return $output;
	}

	// Header output to go before all content
	private function header($vars) {
		$LANG = $vars['_lang'];

		$output = "<link rel='stylesheet' type='text/css' href='\modules\addons\jobs\style.css'>

		<div class='jobs'>

			<div class='adminbar'>
				<a href='addonmodules.php?module=jobs'><img src='\modules\addons\jobs\images\computer.png'> {$LANG['home']}</a>
				<a href='addonmodules.php?module=jobs&action=viewJobs'><img src='\modules\addons\jobs\images\\report_user.png'> {$LANG['viewJobs']}</img></a>
				<a href='addonmodules.php?module=jobs&action=viewApps'><img src='\modules\addons\jobs\images\group.png'> {$LANG['viewApps']}</a>
				<a href='addonmodules.php?module=jobs&action=viewInterviews'><img src='\modules\addons\jobs\images\\report.png'> {$LANG['viewInters']}</a>
			</div>

			<div class='mainbar'><center><strong>{$LANG['browse']}: </strong>
				<a href='addonmodules.php?module=jobs&action=viewJobs'>{$LANG['jobs']}</a> |
				<a href='addonmodules.php?module=jobs&action=viewApps'>{$LANG['apps']}</a> |
				<a href='addonmodules.php?module=jobs&action=viewInterviews'>{$LANG['inters']}</a>
			</div>
		";

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

        $apps = Applicant::orderby('id', 'desc')
        				->take(10)
        				->get();

		$output = "
			<div style='width:50%;float:left;'><h2>{$LANG['jobQuickAdd']}</h2>
				<form action='addonmodules.php?module=jobs&action=submitJobs' method='post'>
					<table class='form' width='95%' border='0' cellspacing='2' cellpadding='3'>
						<tr><td width='20%' class='fieldlabel'><label for='jobTitle'><strong>{$LANG['jobTitle']}: </strong></label></td><td class='fieldarea'><input type='text' id='jobTitle' name='jobTitle'></input></td></tr>
							<td width='20%' class='fieldlabel'><label for='jobTitle'><label for='jobRef'><strong>{$LANG['jobRef']}: </strong></label></td><td class='fieldarea'><input type='text' id='jobRef' name='jobRef'></input></td></tr>
							<td width='20%' class='fieldlabel'><label for='jobTitle'><label for='jobSalary'><strong>{$LANG['jobSalary']}: </strong></label></td><td class='fieldarea'><input type='text' id='jobSalary' name='jobSalary'></input></td></tr>
							<td width='20%' class='fieldlabel'><label for='jobTitle'><label for='jobDesc'><strong>{$LANG['jobDescription']}: </strong></label></td><td class='fieldarea'><textarea id='jobDesc' name='jobDesc' rows='5' cols='50'></textarea></td></tr>
							<td width='20%' class='fieldlabel'><label for='jobTitle'><label for='jobDep'><strong>{$LANG['jobDepartment']}: </strong></label></td><td class='fieldarea'><input type='text' id='jobDep' name='jobDep'></input></td></tr>
							<td width='20%' class='fieldlabel'><label for='jobTitle'><label for='jobReq'><strong>{$LANG['jobRequirements']}: </strong></label></td><td class='fieldarea'><textarea id='jobReq' name='jobReq' rows='5' cols='50'></textarea></td></tr>
							<td width='20%' class='fieldlabel'><label for='jobTitle'><label for='jobAct'><strong>{$LANG['active']}: </strong></label></td><td class='fieldarea'><input type='checkbox' name='jobAct' id='jobAct' value='1' checked></input></td></tr>
					</table>
					<input type='submit' value='{$LANG['submit']}'></input>
				</form>
			</div>";

		$output .= "<div style='width:50%;float:right;'><h2>{$LANG['latestApplicants']}</h2>

			<div class='tablebg'>
				<table class='datatable' id='sortabletbl1' width='100%' border='0' cellspacing='1'>
					<tr>
						<th><strong>{$LANG['appID']}</strong></th><th><strong>{$LANG['appForename']}</strong></th><th><strong>{$LANG['appSurname']}</strong</th><th><strong>{$LANG['appJob']}</strong></th>
					</tr>";

		foreach ($apps as $app) {
			$job = Job::find($app->jobid);

			$output .= '<tr><td>' . $app->id. '</td><td>' . $app->fname . '</td><td>' . $app->lname . '</td><td>' . $job->title . '</td></tr>';
		}

		$output .=	'</table>
			</div></div>';

		return $this->header($vars) . $output . $this->footer($vars);
	}

	public function addJobs($vars, $post = null, $get = null) {
		// Get common module parameters
        $modulelink = $vars['modulelink'];
        $LANG = $vars['_lang']; // An array of the currently loaded language variables

        // If we are editing an existing record, check it exists then add the data to the form
        if (!is_null($get) && isset($get['jobId'])) {
        	// Make sure there is a job with the given ID
        	try {
        		$job = Job::findOrFail($get['jobId']);
        	} catch (\Exception $e) {
        		return $this->header($vars) . $this->error($LANG['jobNotFoundTitle'], $LANG['jobNotFoundMessage']) . $this->footer($vars);
        	}
        } else {
        	$job = new Job;
        }

        $active = ($job->active == 1) ? 'checked' : '';

		$output = '<h2>' . $LANG['addJobsWelcome'] . '</h2>' . '

			<form action="addonmodules.php?module=jobs&action=submitJobs" method="post">
				<input type="hidden" name="id" value="' . $job->id . '">
				<table class="form" width="50%" border="0" cellspacing="2" cellpadding="3">
					<tr><td width="20%" class="fieldlabel"><label for="jobTitle"><strong>' . $LANG['jobTitle'] . ': </strong></label></td><td class="fieldarea"><input type="text" id="jobTitle" name="jobTitle" value="' . $job->title . '"></input></td></tr>
					<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobRef"><strong>' . $LANG['jobRef'] . ': </strong></label></td><td class="fieldarea"><input type="text" id="jobRef" name="jobRef" value="' . $job->reference . '"></input></td></tr>
					<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobSalary"><strong>' . $LANG['jobSalary'] . ': </strong></label></td><td class="fieldarea"><input type="text" id="jobSalary" name="jobSalary" value="' . $job->salary . '"></input></td></tr>
					<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobDesc"><strong>' . $LANG['jobDescription'] . ': </strong></label></td><td class="fieldarea"><textarea id="jobDesc" name="jobDesc" rows="5" cols="50">' . $job->description . '</textarea></td></tr>
					<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobDep"><strong>' . $LANG['jobDepartment'] . ': </strong></label></td><td class="fieldarea"><input type="text" id="jobDep" name="jobDep" value="' . $job->department . '"></input></td></tr>
					<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobReq"><strong>' . $LANG['jobRequirements'] . ': </strong></label></td><td class="fieldarea"><textarea id="jobReq" name="jobReq" rows="5" cols="50">' . $job->requirments . '</textarea></td></tr>
					<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobAct"><strong>' . $LANG['active'] . ': </strong></label></td><td class="fieldarea"><input type="checkbox" name="jobAct" id="jobAct" value="1" '. $active . '></input></td></tr>
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
			return $this->header($vars) . $this->error($LANG['errorOccurred'], $LANG['invalidData']) . $this->footer($vars);
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

		if ($jobAct = '' || !isset($post['jobAct'])) {
			$jobAct = 0;
		} else {
			$jobAct = 1;
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
			return $this->header($vars) . $this->error($LANG['submissionUnsuccessful'], "{$LANG['submitJobsUnSuccess']}: {$e->getMessage()}") . $this->footer($vars);
		}

		return $this->header($vars) . $this->success($LANG['jobAdded'], $LANG['submitJobsSuccess']) . $this->footer($vars);
	}

	public function viewJobs($vars, $post = null, $get = null) {
		// Get common module parameters
        $modulelink = $vars['modulelink'];
        $LANG = $vars['_lang']; // An array of the currently loaded language variables

        // Get all jobs in table
        $jobs = Job::all();

        $output = '<h2>' . $LANG['viewJobsWelcome'] . "</h2>

			<div class='tablebg'><table class='datatable' id='sortabletbl1' width='100%' border='0' cellspacing='1'>
				<tr><th><strong>{$LANG['jobID']}</strong></th><th><strong>{$LANG['jobTitle']}</strong></th><th><strong>{$LANG['jobRef']}</strong></th><th><strong>{$LANG['jobSalary']}</strong></th><th><strong>{$LANG['jobDescription']}</strong></th>
				<th><strong>{$LANG['jobDep']}</strong></th><th><strong>{$LANG['jobRequirements']}</strong></th><th><strong>{$LANG['jobActive']}</strong></th>
				<th></th></tr>";

		foreach ($jobs as $job) {
			if ($job->active) {
				$active = $LANG['yes'];
			} else {
				$active = $LANG['no'];
			}

			$output .= '<tr><td>' . $job->id . '</td><td>' . $job->title . '</td><td>' . $job->reference . '</td><td>' . $job->salary . '</td><td>' . $job->description . '<td>' . $job->department . '</td><td>' . $job->requirments . '</td><td>' . $active . '</td><td><a href="addonmodules.php?module=jobs&action=addJobs&jobId=' . $job->id . '"><img src="\modules\addons\jobs\images\report_edit.png"></a></td></tr>';
		}

		$output .= '</table></div>';

		return $this->header($vars) . $output . $this->footer($vars);
	}

	public function viewApps($vars, $post = null, $get = null) {
		// Get common module parameters
        $modulelink = $vars['modulelink'];
        $LANG = $vars['_lang']; // An array of the currently loaded language variables

        // Get all applications in table
        $apps = Applicant::all();

        $output = '<h2>' . $LANG['viewAppsWelcome'] . "</h2>

        	<div class='tablebg'><table class='datatable' id='sortabletbl1' width='100%' border='0' cellspacing='1'>
				<tr>
					<th><strong>{$LANG['appID']}</strong></th><th><strong>{$LANG['appForename']}</strong></th><th><strong>{$LANG['appSurname']}</strong</th><th><strong>{$LANG['appEmail']}</strong></th><th><strong>{$LANG['appJob']}</strong></th><th><strong>{$LANG['why']}</strong></th><th><strong>{$LANG['experience']}</strong></th><th></th>
				</tr>";

		foreach ($apps as $app) {
			$job = Job::find($app->jobid);

			$output .= '
				<tr>
					<td>' . $app->id . '</td><td>' . $app->fname. '</td><td>' . $app->lname . '</td><td>' . $app->email . '</td><td>' . $job->title . '</td><td>' . $app->why . '</td><td>' . $app->experience . '</td><td><a href="addonmodules.php?module=jobs&action=addInter&appId=' . $app->id . '"><img src="\modules\addons\jobs\images\report_add.png"></a></td>
				</tr>';
		}

		$output .= '</table></div>';

		return $this->header($vars) . $output . $this->footer($vars);
	}

	public function addInter($vars, $post = null, $get = null) {
		// Get common module parameters
        $modulelink = $vars['modulelink'];
        $LANG = $vars['_lang']; // An array of the currently loaded language variables

        // Make sure we have been given an ID
		if (is_null($get) || !isset($get['appId'])) {
			return $this->header($vars) . $this->error($LANG['noApplicantTitle'], $LANG['noApplicantMessage']) . $this->footer($vars);
		}

		$appID = $get['appId'];

		// Make sure the application exists
		try {
			$app = Applicant::findOrFail($appID);
		} catch (\Exception $e) {
			return $this->header($vars) . $this->error($LANG['appNotFoundTitle'], "{$LANG['appNotFoundMessage']}: {$e->getMessage()}") . $this->footer($vars);
		}

		$admins = Admin::all();

		$output = '<h2>' . $LANG['addInterWelcome'] . "</h2>

			<form action='addonmodules.php?module=jobs&action=submitInter' method='post'>
				<table class='form' width='75%' border='0' cellspacing='2' cellpadding='3'>
					<td width='20%' class='fieldlabel'><label for='date'><strong>{$LANG['date']} (YYYY-MM-DD HH:MM:SS): </strong></label></td><td class='fieldarea'><input type='datetime-local' name='date' id='date'></input></td></tr>
					<td width='20%' class='fieldlabel'><label for='admin'><strong>{$LANG['admin']}: </strong></label></td><td class='fieldarea'><select name='admin' id='admin'>";

		// Add all admins to the dropdown
		foreach ($admins as $admin) {
			$output .= '<option value="' . $admin->id . '">' . $admin->username . '</option>';
		}

		$output .= "</select></td></tr>
					<td width='20%' class='fieldlabel'><label for='trans'><strong>{$LANG['transcript']}:</strong></label></td><td class='fieldarea'><textarea name='trans' id='trans' cols='100' rows='10'></textarea></td></tr>
					<td width='20%' class='fieldlabel'><label for='notes'><strong>{$LANG['notes']}:</strong></label></td><td class='fieldarea'><textarea name='notes' id='notes' cols='100' rows='10'></textarea></td></tr>
				</table>
				<input type='hidden' name='appid' id='appid' value='{$appID}'></input>
				<br /><input type='submit' value='{$LANG['submit']}'></input></form>";

		return $this->header($vars) . $output . $this->footer($vars);
	}

	public function submitInter($vars, $post = null, $get = null) {
		// Get common module parameters
        $modulelink = $vars['modulelink'];
        $LANG = $vars['_lang']; // An array of the currently loaded language variables

		// If no POST variables were sent, return an error
		if (is_null($post)) {
			return $this->header($vars) . $this->error($LANG['errorOccurred'], $LANG['invalidData']) . $this->footer($vars);
		}

		$appId = $post['appid'];
		$dateTime = $post['date'];
		$trans = $post['trans'];
		$admin = $post['admin'];
		$notes = $post['notes'];

		$interview = new Interview;
		$interview->appid = $appId;
		$interview->date = $dateTime;
		$interview->trans = $trans;
		$interview->adminid = $admin;
		$interview->notes = $notes;

		try {
			$interview->save();
		} catch (\Exception $e) {
			return $this->header($vars) . $this->error($LANG['submissionUnsuccessful'], "{$LANG['addInterUnSuccess']}: {$e->getMessage()}") . $this->footer($vars);
		}

		return $this->header($vars) . $this->success($LANG['interviewAdded'], $LANG['addInterSuccess']) . $this->footer($vars);
	}

	public function viewInterviews($vars, $post = null, $get = null) {
		// Get common module parameters
        $modulelink = $vars['modulelink'];
        $LANG = $vars['_lang']; // An array of the currently loaded language variables

        $interviews = Interview::all();

        $output = '<h2>' . $LANG['viewInterWelcome'] . "</h2>

			<div class='tablebg'>
				<table class='datatable' id='sortabletbl1' width='100%' border='0' cellspacing='1'>
					<tr>
					<th><strong>{$LANG['interID']}</strong></td><th><strong>{$LANG['appID']}</strong></th><th><strong>{$LANG['appForename']}</strong</th><th><strong>{$LANG['appSurname']}</strong></th><th><strong>{$LANG['interDate']}</strong></th><th><strong>{$LANG['interviewer']}</strong></th><th><strong>{$LANG['interTranscript']}</strong></th><th><strong>{$LANG['interNotes']}</strong></th></tr>";

		foreach ($interviews as $interview) {
			// Get the admin and applicant associated with the interview
			$admin = Admin::find($interview->adminid);
			$applicant = Applicant::find($interview->appid);

			$output .= '<tr><td>' . $interview->id . '</td><td>' . $applicant->id . '</td><td>' . $applicant->fname . '</td><td>' . $applicant->lname . '<td>' . $interview->date . '</td><td>' . $admin->username . '</td><td>' . $interview->trans . '</td><td>' . $interview->notes . '</td></tr>';
		}

		$output .= '</table></div>';

		return $this->header($vars) . $output . $this->footer($vars);
	}
}

?>