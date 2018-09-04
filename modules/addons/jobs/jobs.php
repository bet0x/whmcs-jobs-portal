<?php

/**
* @package    WHMCS
* @author     Matthew Watson
* @copyright  Copyright (c) Matthew Watson
* @version    1.9
* @link       https://www.base.envesko.com
*/

if(!defined("WHMCS")) {
	die("This file cannot be directly accessed.");
}

// Include the license checking function
include 'licenseCheck.php';

// DB abstraction class
use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\Jobs\Admin\AdminDispatcher;
use WHMCS\Module\Addon\Jobs\Client\ClientDispatcher;

function jobs_config() {
	$configArray = array(
		"name" => "Jobs Portal",
		"description" => "This addon allows you to add a job application system to your site.",
		"version" => "1.9",
		"author" => "Matthew Watson",
		"language" => "english",
		"fields" => array(
			"license"		=> array("FriendlyName" => "License Key", "Type" => "text", "Size" => "35", "Description" => "Enter your license key here.", "Default" => ""),
			"hremail" 		=> array("FriendlyName" => "Contact Email", "Type" => "text", "Size" => "25", "Description" => "Enter a contact email here.", "Default" => "humanresoucres@example.com"),
			"homeTab" 		=> array("FriendlyName" => "Home Tab Text", "Type" => "text", "Size" => "25", "Description" => "Enter the text for the home tab here.", "Default" => "Home"),
			"welcomeText" 	=>  array("FriendlyName" => "Welcome Text", "Type" => "textarea", "Rows" => "5", "Columns" => "50", "Description" => "Enter the message to be shown in the home tab.", "Default" => "")
		)
	);

	return $configArray;
}

function jobs_activate() {
	$result = true;

	// Create joblist table
	try {
		Capsule::schema()->create(
			'jobs_joblist',
			function($table) {
				$table->increments('id');
				$table->string('title');
				$table->string('reference');
				$table->text('description');
				$table->text('department');
				$table->text('requirments');
				$table->text('salary');
				$table->boolean('active');
			}
		);
	} catch (\Exception $e) {
		$result = false;
		$error = $e->getMessage();
	}
	
	// Application table
	try {
		Capsule::schema()->create(
			'jobs_applicants',
			function($table) {
				$table->increments('id');
				$table->string('fname');
				$table->string('lname');
				$table->string('email');
				$table->integer('jobid');
				$table->text('why');
				$table->text('experience');
			}
		);
	} catch (\Exception $e) {
		$result = false;
		$error = $e->getMessage();
	}

	// Interviews table
	try {
		Capsule::schema()->create(
			'jobs_interviews',
			function($table) {
				$table->increments('id');
				$table->integer('appid');
				$table->integer('adminid');
				$table->datetime('date');
				$table->text('trans');
				$table->text('notes');
				$table->text('exp');
			}
		);
	} catch (\Exception $e) {
		$result = false;
		$error = $e->getMessage();
	}

	// Settings table
	try {
		Capsule::schema()->create(
			'jobs_settings',
			function($table) {
				$table->string('setting_name');
				$table->primary('setting_name');
				$table->text('setting_val');
			}
		);
	} catch (\Exception $e) {
		$result = false;
		$error = $e->getMessage();
	}

 	try {
		// Insert a default value into the setting for the local key
		Capsule::table('jobs_settings')->insert(
			['setting_name' => 'localkey', 'setting_val' => '']
		);
	} catch (\Exception $e) {
		$result = false;
		$error = $e->getMessage();
	}

	if ($result === true) {
		return array('status' => 'success', 'description' => 'The addon installed successfully!');
	} else {
		return array('status' => 'error', 'description' => "There was an error installing the addon!\n{$error}");
	}
}

function jobs_deactivate() {
	$result = true;

	// Delete all tables
	try {
		Capsule::schema()->drop('jobs_joblist');
		Capsule::schema()->drop('jobs_applicants');
		Capsule::schema()->drop('jobs_interviews');
		Capsule::schema()->drop('jobs_settings');
	} catch (\Exception $e) {
		$result = false;
		$error = $e->getMessage();
	}

	if ($result === true) {
		return array('status' => 'success', 'description' => 'The addon was successfully uninstalled!');
	} else {
		return array('status' => 'error', 'description' => "There was an error uninstalling the addon!\n{$error}");
	}
}

function jobs_output($vars) {
	// Get the action
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

	// Create a dispatcher
	$dispatcher = new AdminDispatcher();
	$response = $dispatcher->dispatch($action, $vars, $_POST, $_REQUEST);
	echo $response;
}

function jobs_clientarea($vars) {
	// Get common module parameters
    $modulelink = $vars['modulelink']; // eg. index.php?m=addonmodule
    $_lang = $vars['_lang']; // an array of the currently loaded language variables

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    $dispatcher = new ClientDispatcher();
    return $dispatcher->dispatch($action, $vars, $_REQUEST, $_POST);
}

function jobs_sidebar($vars)
{
$LANG = $vars['_lang'];

$sidebar = '<span class="header"><img src="images/icons/addonmodules.png" class="absmiddle" width="16" height="16" /> Jobs</span>
<ul class="menu">
<li><a href="addonmodules.php?module=jobs">Home</a></li>
<li><a href="addonmodules.php?module=jobs&action=addJobs">Add Jobs</a></li>
<li><a href="addonmodules.php?module=jobs&action=viewJobs">View Jobs</a></li>
<li><a href="addonmodules.php?module=jobs&action=viewApps">View Applications</a></li>
<li><a href="addonmodules.php?module=jobs&action=viewInter">View Interviews</a></li>
</ul>';

return $sidebar;
}
