<?php

namespace WHMCS\Module\Addon\Jobs\Admin;

class AdminDispatcher {
	
	public function dispatch($action, $params) {
		// If no action is supplied then shown the index
		if (!$action) {
			$action = 'index';
		}

		$controller = new Controller();

		// Make sure the request is valid
		if (is_callable(array($controller, $action))) {
			return $controller->$action($params);
		}

		// If not, show an error message
		return '<div class="errorbox"><strong>Invalid action requested. Please try again.</strong></div>';
	}
}

?>