<div class="page-header">
    <div class="styled_title"><h1>Job Vacancies</h1></div>
</div>


{php}
$hrEmail = $template->getVariable('hremail')->value;
$companyName = $template->getVariable('companyname')->value;
$homeTabText = $template->getVariable('homeTab')->value;
$action = $_GET['action'];
$welcomeText = mysql_fetch_array(select_query("jobs_settings", "setting_val"));
$results = mysql_query("SELECT DISTINCT job_department FROM jobs_joblist");
if ($action == '')
{

		echo '<ul class="nav nav-tabs">';
		if ($_GET['dep'] == '')
		{
			echo '<li class="active"><a href="index.php?m=jobs">' . $homeTabText . '</a></li>';
		}
		else
		{
			echo '<li><a href="index.php?m=jobs">' . $homeTabText . '</a></li>';
		}
		while ($row = mysql_fetch_array($results, MYSQL_ASSOC))
		{
			if ($_GET['dep'] == $row['job_department'])
			{
				echo '<li class="active"><a href="index.php?m=jobs&dep=' . $row['job_department'] . '">' . $row['job_department'] . '</a></li>';
			}
			else
			{
				echo '<li><a href="index.php?m=jobs&dep=' . $row['job_department'] . '">' . $row['job_department'] . '</a></li>';
			}

		}

		echo '</ul>';

		if ($_GET['dep'] == '')
		{
			echo '<p>' . $welcomeText[0] . '</p>';
		}

		$fields = "job_id,job_title,job_reference,job_description,job_department,job_requirments,job_active,job_salary";
		$mysqlResult = select_query("jobs_joblist", $fields, array('job_active' => 1));

		if (mysql_num_rows($mysqlResult) == 0)
		{
			echo '<div class="alert alert-warning textcenter">There are no positions currently open. Please check again later.</div>';
		}
		else
		{

			$department = $_GET['dep'];
			$fields = "job_id,job_title,job_reference,job_description,job_department,job_requirments,job_active,job_salary";
			$mysqlResult = select_query("jobs_joblist", $fields, array('job_active' => 1, 'job_department' => $department));
			while ($row = mysql_fetch_array($mysqlResult, MYSQL_ASSOC))
			{
				echo '<div class="row"><div class="well"><div class="internalpadding">';

				echo '<h3>' . $row['job_title'] . '</h3>';
				echo '<p><strong>Department: </strong>' . $row['job_department'] . '</p>';
        		echo '<p><strong>Salary: </strong>' . $row['job_salary'] . '</p>';
				echo '<p><strong>Job Description: </strong></p><p>' . $row['job_description'] . '</p>';
				echo '<p><strong>Job Requirements: </strong></p><p>' . $row['job_requirments'] . '</p>';
				echo '<form><input type="button" class="btn btn-primary" onClick="parent.location=\'index.php?m=jobs&action=apply&job=' . $row['job_id'] . '\'" value="Apply Now"></input></form>';

				echo '</div></div></div>';
			}
		}
}
elseif ($action == 'apply')
{
$jobIdApply = $_GET['job'];
echo '

	<form action="index.php?m=jobs&action=submitInfo" method="post">

<div class="row">
<div class="col2half fboxjla">
<div class="internalpadding">

		<label for="fName"><strong>First Name: </strong></label><input type="text" id="fName" name="fName"></input>

</div>
</div>

<div class="col2half fboxjla">
<div class="internalpadding">

		<label for="lName"><strong>Surname: </strong></label><input type="text" id="lName" name="lName"></input>

</div>
</div>
</div>

<div class="row">
<div class="col2half fboxjla">
<div class="internalpadding">

		<label for="skype"><strong>Skype: </strong></label><input type="text" id="skype" name="skype"></input>

</div>
</div>

<div class="col2half fboxjla">
<div class="internalpadding">

		<label for="email"><strong>Email: </strong></label><input type="email" name="email" id="email"></input>

</div>
</div>
</div>

<div class="row">
<div class="col2half fboxjla">
<div class="internalpadding">

		<label for="why"><strong>Why do you want to work for ' . $companyName . '?</strong><br /><textarea id="why" name="why" cols="30" rows="10"></textarea>

</div>
</div>

<div class="col2half fboxjla">
<div class="internalpadding">

		<label for="exp"><strong>Experince:</strong><br /><textarea id="exp" name="exp" cols="30" rows="10"></textarea>

</div>
</div>
</div>

<div class="row">
<div class="fboxjl">
<div class="internalpadding">

		<input type="hidden" name="jobId" id="jobId" value="' . $jobIdApply . '"></input>
		<input type="checkbox" name="confirm" value="confirm">&nbsp;<strong>I agree that all of the information above is correct at the time of submission and once submitted it cannot be changed</strong>

</div>
</div>
</div>

<div class="row">
<div class="col2half fboxjl">
<div class="internalpadding">

<input class="btn btn-primary" type="submit" name="apply-submit" id="apply-submit" value="Submit"></input>

</div>
</div>
</div>
</form>

';
}
elseif ($action == 'submitInfo')
{
	$fName = $_POST['fName'];
	$lName = $_POST['lName'];
	$skype = $_POST['skype'];
	$email = $_POST['email'];
	$jobId = $_POST['jobId'];
	$why = $_POST['why'];
	$exp = $_POST['exp'];
	$confirm = $_POST['confirm'];

	if ($fName == '' || $lName == '' || $skype == '' || $email == '' || $why == '' || $exp == '')
	{
		$error = 'Not all fileds were filled out!';
	}
	elseif ($confirm == '')
	{
		$error = 'You did not accept our terms and conditions!';
	}
	else
	{
		$query = "INSERT INTO `jobs_applicants` (`app_fname`, `app_lname`, `app_address`, `app_email`, `app_jobid`, `app_why`, `app_exp`) VALUES ('$fName', '$lName', '$skype', '$email', '$jobId', '$why', '$exp')";
		$values = array('app_fname' => $fName, 'app_lname' => $lName, 'app_address' => $skype, 'app_email' => $email, 'app_jobid' => $jobId, 'app_why' => $why, 'app_exp' => $exp);

		$result = insert_query("jobs_applicants", $values);
	}

	if ($result == TRUE)
	{
		$query = "SELECT `app_id` FROM `jobs_applicants` WHERE `app_fname` = '$fName' AND `app_lname` = '$lName' AND `app_why` = '$why'";
		$result = mysql_fetch_array(mysql_query($query));
		echo '<div class="alert alert-success textcenter"><p>You have successfully applied for the job! If you do not hear anything within 72 hours, please Email <a href="mailto:' . $hrEmail . '">' . $hrEmail . '</a> and include things such as your application ID &amp; job ID for reference purposes.</p></div>';
		echo '<div class="alert alert-warning textcenter"><p><strong>Your Appication Reference Number is: </strong>' . $result[0] . '</p></div>';

		$message = $fName . ' ' . $lName . " has applied for a staff posistion at " . $companyName . ".<br /><br />Here are their details:<br /><br /><strong>Name: </strong>" . $fName . " " . $lName . "<br /><strong>Email: </strong>" . $email . "<br /><strong>Skype: </strong>" . $skype . "<br /><br />Check the Admin CP for more information on the applicant.";
		$headers = "From: " . $hrEmail . "\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		mail($hrEmail, 'New Applicant Notification', $message, $headers);
	}
	else
	{
		echo '<div class="alert alert-warning textcenter"><p>There was an error with your application!</p></div>';
		echo '<div class="alert alert-error textcenter"><p><strong>Error: </strong>' . $error . '</p></div>';
		echo $result;
	}
}
{/php}
