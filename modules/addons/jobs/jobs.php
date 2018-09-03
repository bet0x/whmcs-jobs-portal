<?php

/**
* @package    WHMCS
* @author     Matthew Watson
* @copyright  Copyright (c) Matthew Watson
* @version    2.0
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
		"version" => "2.0dev",
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

/*function jobs_output($vars)
{
$LANG = $vars['_lang'];
$action = $_GET['action'];

echo '<link rel="stylesheet" type="text/css" href="\modules\addons\jobs\style.css">';

echo '<div class="jobs">';

echo '<div class="adminbar">
<a href="addonmodules.php?module=jobs"><img src="\modules\addons\jobs\images\computer.png"> Home</a>
<a href="addonmodules.php?module=jobs&action=viewJobs"><img src="\modules\addons\jobs\images\report_user.png"> View Jobs</img></a>
<a href="addonmodules.php?module=jobs&action=viewApps"><img src="\modules\addons\jobs\images\group.png"> View Applicants</a>
<a href="addonmodules.php?module=jobs&action=viewInter"><img src="\modules\addons\jobs\images\report.png"> View Interviews</a>
</div>';

echo '<div class="mainbar"><center><strong>Browse: </strong>
<a href="addonmodules.php?module=jobs&action=viewJobs">Jobs</a> |
<a href="addonmodules.php?module=jobs&action=viewApps">Applicants</a> |
<a href="addonmodules.php?module=jobs&action=viewInter">Interviews</a></div>';

if ($action == 'addJobs')
{
echo '<h2>' . $LANG['addJobsWelcome'] . '</h2>';

echo '<form action="addonmodules.php?module=jobs&action=submitJobs" method="post">' .
'<table class="form" width="50%" border="0" cellspacing="2" cellpadding="3">' .
'<tr><td width="20%" class="fieldlabel"><label for="jobTitle"><strong>Job Title: </strong></label></td><td class="fieldarea"><input type="text" id="jobTitle" name="jobTitle"></input></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobRef"><strong>Job Ref (Admin Use Only): </strong></label></td><td class="fieldarea"><input type="text" id="jobRef" name="jobRef"></input></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobSalary"><strong>Job Salary: </strong></label></td><td class="fieldarea"><input type="text" id="jobSalary" name="jobSalary"></input></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobDesc"><strong>Description: </strong></label></td><td class="fieldarea"><textarea id="jobDesc" name="jobDesc" rows="5" cols="50"></textarea></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobDep"><strong>Department: </strong></label></td><td class="fieldarea"><input type="text" id="jobDep" name="jobDep"></input></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobReq"><strong>Requirments: </strong></label></td><td class="fieldarea"><textarea id="jobReq" name="jobReq" rows="5" cols="50"></textarea></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobAct"><strong>Active: </strong></label></td><td class="fieldarea"><input type="checkbox" name="jobAct" id="jobAct" value="1" checked></input></td></tr>' .
'</table><input type="submit" value="Submit"></input></form>';
}
elseif ($action == 'submitJobs')
{
$jobTitle = $_POST['jobTitle'];
$jobRef = $_POST['jobRef'];
$jobDesc = $_POST['jobDesc'];
$jobDep = $_POST['jobDep'];
$jobReq = $_POST['jobReq'];
$jobAct = $_POST['jobAct'];
$jobSalary = $_POST['jobSalary'];

if ($jobAct == '')
{
$jobAct = '0';
}

$values = array('job_title' => $jobTitle, 'job_reference' => $jobRef, 'job_description' => $jobDesc, 'job_department' => $jobDep, 'job_requirments' => $jobReq,
'job_active' => $jobAct, 'job_salary' => $jobSalary);

$result = insert_query("jobs_joblist", $values);

if ($result == TRUE)
{
echo '<div class="successbox"><strong>Job Added</strong><br />' . $LANG['submitJobsSuccess'] . '</div>';
}
else
{
echo '<div class="errorbox"><strong>Job Not Added</strong><br />' . $LANG['submitJobsUnSuccess'] . '</div>';
}
}
elseif ($action == 'viewJobs')
{
$fields = "job_id,job_title,job_reference,job_description,job_department,job_requirments,job_active,job_salary";

echo '<h2>' . $LANG['viewJobsWelcome'] . '</h2>';

echo '<div class="tablebg"><table class="datatable" id="sortabletbl1" width="100%" border="0" cellspacing="1">' .
'<tr><th><strong>Job ID</strong></th><th><strong>Job Title</strong></th><th><strong>Job Ref</strong></th><th><strong>Job Salary</strong></th><th><strong>Job Desc</strong></th>' .
'<th><strong>Job Dep.</strong></th><th><strong>Job Req</strong></th><th><strong>Job Active?</strong></th>' .
'<th></th></tr>';

$result = select_query("jobs_joblist", $fields);

while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
$jobId = $row['job_id'];
$jobTitle = $row['job_title'];
$jobRef = $row['job_reference'];
$jobDesc = $row['job_description'];
$jobDep = $row['job_department'];
$jobReq = $row['job_requirments'];
$jobAct = $row['job_active'];
$jobSalary = $row['job_salary'];

if ($jobAct == '1')
{
$jobAct = 'Yes';
}
else
{
$jobAct = 'No';
}

echo '<tr><td>' . $jobId . '</td><td>' . $jobTitle . '</td><td>' . $jobRef . '</td><td>' . $jobSalary . '</td><td>' . $jobDesc .
'<td>' . $jobDep . '</td><td>' . $jobReq . '</td><td>' . $jobAct . '</td><td>' .
'<a href="addonmodules.php?module=jobs&action=editJobs&jobId=' . $jobId . '"><img src="\modules\addons\jobs\images\report_edit.png"></a></td></tr>';
}

echo '</table></div>';
}
elseif ($action == 'editJobs')
{
$jobId = $_GET['jobId'];
$fields = "job_id,job_title,job_reference,job_description,job_department,job_requirments,job_active,job_salary";
$where = array("job_id" => $jobId);
$result = mysql_fetch_array(select_query("jobs_joblist", $fields, $where), MYSQL_ASSOC);

echo '<h2>' . $LANG['editJobsWelcome'] . '</h2>';
echo '<form action="addonmodules.php?module=jobs&action=editJobsSubmit" method="post">' .
'<table class="form" width="40%" border="0" cellspacing="2" cellpadding="3">' .
'<td width="20%" class="fieldlabel"><label for="jobTitle"><strong>Job Title: </strong></label></td><td class="fieldarea"><input type="text" id="jobTitle" name="jobTitle" value="' . $result['job_title'] . '"></input></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobRef"><strong>Job Ref (Admin Use Only): </strong></label></td><td class="fieldarea"><input type="text" id="jobRef" name="jobRef" value="' . $result['job_reference'] . '"></input></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobSalary"><strong>Job Salary: </strong></label></td><td class="fieldarea"><input type="text" id="jobSalary" name="jobSalary" value="' . $result['job_salary'] . '"></input></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobDesc"><strong>Description: </strong></label></td><td class="fieldarea"><textarea id="jobDesc" name="jobDesc" rows="5" cols="50">' . $result['job_description'] . '</textarea></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobDep"><strong>Department: </strong></label></td><td class="fieldarea"><input type="text" id="jobDep" name="jobDep" value="' . $result['job_department'] . '"></input></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobReq"><strong>Requirments: </strong></label></td><td class="fieldarea"><textarea id="jobReq" name="jobReq" rows="5" cols="50">' . $result['job_requirments'] . '</textarea></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobAct"><strong>Active: </strong></label></td><td class="fieldarea"><input type="checkbox" name="jobAct" id="jobAct" value="1" checked></input></td></tr>' .
'</table>' .
'<input type="hidden" name="jobId", id="jobId" value="' . $result['job_id'] . '"></input>' .
'<br /><input type="submit" value="Submit"></input></form>';
}
elseif ($action == 'editJobsSubmit')
{
$jobId = $_POST['jobId'];
$jobTitle = $_POST['jobTitle'];
$jobRef = $_POST['jobRef'];
$jobDesc = $_POST['jobDesc'];
$jobDep = $_POST['jobDep'];
$jobReq = $_POST['jobReq'];
$jobAct = $_POST['jobAct'];
$jobSalary = $_POST['jobSalary'];

if ($jobAct == '')
{
$jobAct = '0';
}

$update = array('job_title' => $jobTitle, 'job_reference' => $jobRef, 'job_description' => $jobDesc, 'job_department' => $jobDep,
'job_requirments' => $jobReq, 'job_active' => $jobAct, 'job_salary' => $jobSalary);
$where = array('job_id' => $jobId);

$result = update_query("jobs_joblist", $update, $where);


if ($result == TRUE)
{
echo '<div class="successbox"><strong>Job Edited</strong><br />' . $LANG['editJobsSuccess'] . '</div>';
}
else
{
echo '<div class="errorbox"><strong>Job Not Edited</strong><br /' . $LANG['editJobsUnSuccess'] . '</div>';
}
}
elseif ($action == 'viewApps')
{
$fields = "app_id,app_fname,app_lname,app_address,app_email,app_jobid,app_why,app_exp";

echo '<h2>' . $LANG['viewAppsWelcome'] . '</h2>';

echo '<div class="tablebg"><table class="datatable" id="sortabletbl1" width="100%" border="0" cellspacing="1">' .
'<tr><th><strong>Applicant ID</strong></th><th><strong>Applicant Forename</strong></th><th><strong>Applicant Surname</strong</th>' .
'<th><strong>Applicant Skype</strong></th><th><strong>Applicant Email</strong></th><th><strong>Job Applied For</strong></th>' .
'<th><strong>Why?</strong></th><th><strong>Experince</strong></th><th></th></tr>';

$result = select_query("jobs_applicants", $fields);

while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
$appId = $row['app_id'];
$appFName = $row['app_fname'];
$appLName = $row['app_lname'];
$appAddress = $row['app_address'];
$appEmail = $row['app_email'];
$appJob = $row['app_jobid'];
$appWhy = $row['app_why'];
$appExp = $row['app_exp'];

$resultJob = mysql_fetch_array(select_query("jobs_joblist", "job_title", array('job_id' => $appJob)));
$appJobD = $resultJob[0];

echo '<tr><td>' . $appId . '</td><td>' . $appFName . '</td><td>' . $appLName . '</td><td>' . $appAddress .
'<td>' . $appEmail . '</td><td>' . $appJobD . '</td><td>' . $appWhy . '</td><td>' . $appExp . '</td>' .
'<td><a href="addonmodules.php?module=jobs&action=addInter&appId=' . $appId .
'"><img src="\modules\addons\jobs\images\report_add.png"></a></td></tr>';
}

echo '</table></div>';
}
elseif ($action == 'addInter')
{
$appId = $_GET['appId'];
$query = "SELECT `app_fname`, `app_lname` FROM `jobs_applicants` WHERE `app_id` = '$appId'";
$appInfo = mysql_fetch_array(select_query("jobs_applicants", "app_fname,app_lname", array("app_id" => $appId)), MYSQL_ASSOC);

echo '<h2>' . $LANG['addInterWelcome'] . '</h2>';
echo '<form action="addonmodules.php?module=jobs&action=submitInter" method="post">' .
'<table class="form" width="75%" border="0" cellspacing="2" cellpadding="3">' .
'<td width="20%" class="fieldlabel"><label for="date"><strong>Date/time (YYYY-MM-DD HH:MM:SS): </strong></label></td><td class="fieldarea"><input type="datetime" name="date" id="date"></input></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="admin"><strong>Admin: </strong></label></td><td class="fieldarea"><select name="admin" id="admin">';

$resultAdmins = select_query("tbladmins", "username");

while ($row = mysql_fetch_array($resultAdmins, MYSQL_ASSOC))
{
$adminUsername = $row['username'];

echo '<option value="' . $adminUsername . '">' . $adminUsername . '</option>';
}

echo '</select></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="trans"><strong>Transcript:</strong></label></td><td class="fieldarea"><textarea name="trans" id="trans" cols="100" rows="10"></textarea></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="notes"><strong>Notes:</strong></label></td><td class="fieldarea"><textarea name="notes" id="notes" cols="100" rows="10"></textarea></td></tr>' .
'</table>' .
'<input type="hidden" name="fname" id="fname" value="' . $appInfo['app_fname'] . '"></input>' .
'<input type="hidden" name="lname" id="lname" value="' . $appInfo['app_lname'] . '"></input>' .
'<input type="hidden" name="appid" id="appid" value="' . $appId . '"></input>' .
'<br /><input type="submit" value="Submit"></input></form>';
}
elseif ($action == 'submitInter')
{
$appId = $_POST['appid'];
$fName = $_POST['fname'];
$lName = $_POST['lname'];
$dateTime = $_POST['date'];
$trans = $_POST['trans'];
$admin = $_POST['admin'];
$notes = $_POST['notes'];

$adminQuery = mysql_fetch_array(select_query("tbladmins", "id", array('username' => $admin)));
$adminId = $adminQuery[0];

$values = array('inter_appid' => $appId, 'inter_fname' => $fName, 'inter_lname' => $lName, 'inter_date' => $dateTime, 'inter_admin' => $adminId,
'inter_trans' => $trans, 'inter_notes' => $notes);

$result = insert_query("jobs_interviews", $values);


if ($result == TRUE)
{
echo '<div class="successbox"><strong>Interview Added</strong><br />' . $LANG['addInterSuccess'] . '</div>';
}
else
{
echo '<div class="errorbox"><strong>Interview Not Added</strong><br />' . $LANG['addInterUnSuccess'] . '</div>';
}

}
elseif ($action == 'viewInter')
{
$fields = "inter_id,inter_appid,inter_fname,inter_lname,inter_date,inter_admin,inter_trans";

echo '<h2>' . $LANG['viewInterWelcome'] . '</h2>';

echo '<div class="tablebg"><table class="datatable" id="sortabletbl1" width="100%" border="0" cellspacing="1">' .
'<tr><th><strong>Interview ID</strong></td><th><strong>Applicant ID</strong></th><th><strong>Applicant Forename</strong</th>' .
'<th><strong>Applicant Surname</strong></th><th><strong>Interview Date</strong></th><th><strong>Interviewer</strong></th>' .
'<th><strong>Interview Transcript</strong></th><th><strong>Interview Notes</strong></th></tr>';

$result = select_query("jobs_interviews", $fields);

while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
$interId = $row['inter_id'];
$appId = $row['inter_appid'];
$interFName = $row['inter_fname'];
$interLName = $row['inter_lname'];
$interDate = $row['inter_date'];
$interTrans = $row['inter_trans'];
$interAdmin = $row['inter_admin'];
$interNotes = $row['inter_notes'];
$adminResult = mysql_fetch_array(select_query("tbladmins", "username", array('id' => $interAdmin)));
$adminName = $adminResult[0];

echo '<tr><td>' . $interId . '</td><td>' . $appId . '</td><td>' . $interFName . '</td><td>' . $interLName .
'<td>' . $interDate . '</td><td>' . $adminName . '</td><td>' . $interTrans . '</td><td>' . $interNotes . '</td></tr>';
}

echo '</table></div>';
}
elseif ($action == "submitWelcome")
{
$welcomeText = $_POST['welcomeText'];
$update = array("setting_val" => $welcomeText);
$where = array("setting_id" => "1");

update_query("jobs_settings", $update, $where);


echo '<div class="successbox"><strong>Setting Changed</strong><br />Setting Changed!<br /><a href="addonmodules.php?module=jobs">Go back</a></div>';
}
else
{
echo '<h2>Change Client Welcome Text</h2>';
$welcomeText = mysql_fetch_array(select_query("jobs_settings", "setting_val"));

echo '<form action="addonmodules.php?module=jobs&action=submitWelcome" method="post"><textarea rows="4" cols="100" name="welcomeText">' . $welcomeText[0] . '</textarea><br /><input type="submit" value"Submit"></input></form>';

echo '<div style="width:50%;float:left;"><h2>Quick Add Job</h2>';

echo '<form action="addonmodules.php?module=jobs&action=submitJobs" method="post">' .
'<table class="form" width="95%" border="0" cellspacing="2" cellpadding="3">' .
'<tr><td width="20%" class="fieldlabel"><label for="jobTitle"><strong>Job Title: </strong></label></td><td class="fieldarea"><input type="text" id="jobTitle" name="jobTitle"></input></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobRef"><strong>Job Ref (Admin Use Only): </strong></label></td><td class="fieldarea"><input type="text" id="jobRef" name="jobRef"></input></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobSalary"><strong>Job Salary: </strong></label></td><td class="fieldarea"><input type="text" id="jobSalary" name="jobSalary"></input></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobDesc"><strong>Description: </strong></label></td><td class="fieldarea"><textarea id="jobDesc" name="jobDesc" rows="5" cols="50"></textarea></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobDep"><strong>Department: </strong></label></td><td class="fieldarea"><input type="text" id="jobDep" name="jobDep"></input></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobReq"><strong>Requirments: </strong></label></td><td class="fieldarea"><textarea id="jobReq" name="jobReq" rows="5" cols="50"></textarea></td></tr>' .
'<td width="20%" class="fieldlabel"><label for="jobTitle"><label for="jobAct"><strong>Active: </strong></label></td><td class="fieldarea"><input type="checkbox" name="jobAct" id="jobAct" value="1" checked></input></td></tr>' .
'</table><input type="submit" value="Submit"></input></form></div>';

$fields = "app_id,app_fname,app_lname,app_jobid";

echo '<div style="width:50%;float:right;"><h2>Latest Applicants</h2>';

echo '<div class="tablebg"><table class="datatable" id="sortabletbl1" width="100%" border="0" cellspacing="1">' .
'<tr><th><strong>Applicant ID</strong></th><th><strong>Applicant Forename</strong></th><th><strong>Applicant Surname</strong</th>' .
'<th><strong>Job Applied For</strong></th></tr>';

$result = select_query("jobs_applicants", $fields);

while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
$appId = $row['app_id'];
$appFName = $row['app_fname'];
$appLName = $row['app_lname'];
$appJob = $row['app_jobid'];

$resultJob = mysql_fetch_array(select_query("jobs_joblist", "job_title", array('job_id' => $appJob)));
$appJobD = $resultJob[0];

echo '<tr><td>' . $appId . '</td><td>' . $appFName . '</td><td>' . $appLName . '</td><td>' . $appJobD . '</td></tr>';
}

echo '</table></div></div>';

}

echo '</div>';
}*/

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
