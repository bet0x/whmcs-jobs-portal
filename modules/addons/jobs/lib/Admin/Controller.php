<?php

namespace WHMCS\Module\Addon\Jobs\Admin;

// DB abstraction class
use WHMCS\Database\Capsule;

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

	// Index action
	public function index($vars) {
		// Get common module parameters
        $modulelink = $vars['modulelink'];
        $LANG = $vars['_lang']; // An array of the currently loaded language variables

        // Get the settings needed
        $hrEmail = $vars['hremail'];
        $homeTabText = $vars['hometab'];

        $output = '<h2>Change Client Welcome Text</h2>';
		$welcomeText = Capsule::table('jobs_settings')->select('setting_val')->where('setting_id', '=', 1)->get();

		$output .= '<form action="addonmodules.php?module=jobs&action=submitWelcome" method="post"><textarea rows="4" cols="100" name="welcomeText">' . $welcomeText[0] . '</textarea><br /><input type="submit" value"Submit"></input></form>';

		return $this->header($vars) . $output;
	}
}

?>