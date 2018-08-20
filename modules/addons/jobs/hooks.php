<?php

function jobs_addCSS($vars) {
	// Include our CSS file on all jobs client-side pages
	
	// If the homeTabText Smarty variable is set, then we are on a jobs addon page
	if (isset($vars['homeTabText'])) {
		return '<link href="modules/addons/jobs/clientStyle.css" rel="stylesheet" type="text/css" />';
	}
}

add_hook('ClientAreaHeadOutput', 1, jobs_addCSS);

?>