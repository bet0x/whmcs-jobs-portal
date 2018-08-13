<?php

namespace WHMCS\Module\Addon\Jobs\Client;

// DB abstraction class
use WHMCS\Database\Capsule;

// All addon-related DB models
use WHMCS\Module\Addon\Jobs\Data\Job;
use WHMCS\Module\Addon\Jobs\Data\Applicant;

class Controller {

	public function index($vars) {
		// Get the settings set by the Admin
		$homeTabText = $vars['homeTab'];
		$hrEmail = $vars['hremail'];

		return array(
			'pagetitle'		=> 'Vacant Jobs',
			'breadcrumb'	=> array('index.php?m=jobs' => "Vacant Jobs"),
			'templatefile'	=> 'jobs',
			'requirelogin'	=> false,
			'vars'			=> array('homeTabText' => $homeTabText, 'hrEmail' => $hrEmail)
		);
	}

}

?>