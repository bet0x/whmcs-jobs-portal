<?php

namespace WHMCS\Module\Addon\Jobs\Admin;

class AdminDispatcher {
	
	public function dispatch($action, $params, $post, $get) {
		// If no action is supplied then shown the index
		if (!$action) {
			$action = 'index';
		}

		// If the license key is invalid, the constructor will throw an error
		try {
			$controller = new Controller($params);
		} catch (\Exception $e) {
			// Return an error, don't allow any further code to run
			return "<div class='errorbox'><strong>{$e->getMessage()}</strong></div>";
		}

		// Make sure the request is valid
		if (is_callable(array($controller, $action))) {
			if (!empty($post)) {
				return $controller->$action($params, $post, $get);
			} else {
				return $controller->$action($params, null, $get);
			}
		}

		// If not, show an error message
		return '<div class="errorbox"><strong>Invalid action requested. Please try again.</strong></div>';
	}
}

?>