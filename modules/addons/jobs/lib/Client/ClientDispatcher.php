<?php

namespace WHMCS\Module\Addon\Jobs\Client;

class ClientDispatcher {
	
	public function dispatch($action, $params, $get, $post) {
		// If no action is supplied then shown the index
		if (!$action) {
			$action = 'index';
		}

		$controller = new Controller();

		// Make sure the request is valid
		if (is_callable(array($controller, $action))) {
			return $controller->$action($params, $get, $post);
		}

		// If not, show an error message
		return '<div class="errorbox"><strong>Invalid action requested. Please try again.</strong></div>';
	}
}

?>