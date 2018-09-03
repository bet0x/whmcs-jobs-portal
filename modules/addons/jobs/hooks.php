<?php

use WHMCS\View\Menu\Item as MenuItem;

function jobs_addCSS(array $vars) {
	// Include our CSS file on all jobs client-side pages
	
	// If the homeTabText Smarty variable is set, then we are on a jobs addon page
	if (isset($vars['homeTabText'])) {
		return '<link href="modules/addons/jobs/clientStyle.css" rel="stylesheet" type="text/css" />';
	}
}

function jobs_addClientAreaLink(MenuItem $nav) {
	// Add a link to the jobs portal in the navbar
	$nav->addChild('Jobs Portal')
		->setURI('/index.php?m=jobs')
		->setOrder(100); // So it will always be at the end
}

add_hook('ClientAreaHeadOutput', 1, jobs_addCSS);
add_hook('ClientAreaPrimaryNavbar', 1, jobs_addClientAreaLink);

?>