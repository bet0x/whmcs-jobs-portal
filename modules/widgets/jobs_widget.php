<?php

function widget_jobs_widget($vars)
{
	$noApps = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM `jobs_applicants`"));
	

	$content = "<center><p>So far <strong>" . $noApps[0] . "</strong> people have applied for a job!</center></p>";

	return array('title' => "Jobs System", 'content' => $content);
}

add_hook("AdminHomeWidgets",1,"widget_jobs_widget");

?>