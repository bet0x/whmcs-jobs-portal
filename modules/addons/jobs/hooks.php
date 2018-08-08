<?php

function jobs_show($vars)
{
	$query = "SELECT `setting_val` FROM `jobs_settings`";
	$data = mysql_query($query);
	if (!mysql_num_rows($data))
	{
		$isactive = 0;
	}
	else
	{
		$isactive = 1;
	}
	$returned_values = array("jobs_isactive" => $isactive);
	return $returned_values;
}

add_hook("ClientAreaPage",1,"jobs_show");

?>