<div class="page-header">
    <div class="styled_title"><h1>Job Vacancies</h1></div>
</div>

<ul class="nav nav-tabs">
	{if $dep eq ""}
		<li class="active"><a href="index.php?m=jobs">{$homeTabText}</a></li>
	{else}
		<li><a href="index.php?m=jobs">{$homeTabText}</a></li>
	{/if}

	{foreach $departments as $obj}
		{if $dep eq $obj->department}
			<li class="active"><a href="index.php?m=jobs&dep={$obj->department}">{$obj->department}</a></li>
		{else}
			<li><a href="index.php?m=jobs&dep={$obj->department}">{$obj->department}</a></li>
		{/if}
	{/foreach}
</ul>

	{* If we have the home tab selected, show the welcome text *}
	{if $dep eq ""}
		<p>Some welcome text</p>
	{/if}

	{* If there are not active jobs, show an error message *}
	{if $numActive lt 1}
		<div class="alert alert-warning textcenter">There are no positions currently open. Please check again later.</div>
	{elseif $dep neq ""}
		{* Show all jobs in the given department *}
		{foreach $jobs as $job}
			<div class="row"><div class="well">
				<div class="internalpadding">
					<h3>{$job->title}</h3>
					<p><strong>Department: </strong>{$job->department}</p>
					<p><strong>Salary: </strong>{$job->salary}</p>
					<p><strong>Job Description: </strong></p><p>{$job->description}</p>
					<p><strong>Job Requirements: </strong></p><p>{$job->requirments}</p>
					<form><input type="button" class="btn btn-primary" onClick="parent.location='index.php?m=jobs&action=apply&job={$job->id}'" value="Apply Now"></input></form>
				</div>
			</div>
		{/foreach}
	{/if}