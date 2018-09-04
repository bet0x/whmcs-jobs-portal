<?php

/**
* @package    WHMCS
* @author     Matthew Watson
* @copyright  Copyright (c) Matthew Watson
* @version    1.9
* @link       https://www.base.envesko.com
*/

namespace WHMCS\Module\Addon\Jobs\Client;

class ClientDispatcher {
	
	public function dispatch($action, $params, $get, $post) {
		// If no action is supplied then shown the index
		if (!$action) {
			$action = 'index';
		}

		// If the license key is invalid, the constructor will throw an error
		try {
			$controller = new Controller($params);
		} catch (\Exception $e) {
			// Return an error, don't allow any further code to run
			return array(
				'pagetitle'		=> "Invalid License",
				'breadcrumb'	=> array('index.php?m=jobs' => "Invalid License"),
				'templatefile'	=> 'jobsError',
				'requirelogin'	=> false,
				'vars'			=> array(
									'message'	=> "An incorrect license key is being used for the Jobs addon!"
								)
			);
		}

		// Make sure the request is valid
		if (is_callable(array($controller, $action))) {
			return $controller->$action($params, $get, $post);
		}

		// If not, show an error message
		return '<div class="errorbox"><strong>Invalid action requested. Please try again.</strong></div>';
	}
}

?>