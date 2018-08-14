<form action="index.php?m=jobs&action=submitInfo" method="post">

	<div class="row">
		<div class="col2half fboxjla">
			<div class="internalpadding">
				<label for="fName"><strong>First Name: </strong></label><input type="text" id="fName" name="fName">
			</div>
		</div>

		<div class="col2half fboxjla">
			<div class="internalpadding">
				<label for="lName"><strong>Surname: </strong></label><input type="text" id="lName" name="lName">
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col2half fboxjla">
			<div class="internalpadding">
				<label for="skype"><strong>Skype: </strong></label><input type="text" id="skype" name="skype">
			</div>
		</div>

		<div class="col2half fboxjla">
			<div class="internalpadding">
				<label for="email"><strong>Email: </strong></label><input type="email" name="email" id="email">
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col2half fboxjla">
			<div class="internalpadding">
				<label for="why"><strong>Why do you want to work for {$companyname}?</strong></label><br /><textarea id="why" name="why" cols="30" rows="10"></textarea>
			</div>
		</div>

		<div class="col2half fboxjla">
			<div class="internalpadding">
				<label for="exp"><strong>Experince:</strong></label><br /><textarea id="exp" name="exp" cols="30" rows="10"></textarea>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="fboxjl">
			<div class="internalpadding">
				<input type="hidden" name="jobId" id="jobId" value="{$job->id}">
				<input type="checkbox" name="confirm" value="confirm">&nbsp;<strong>I agree that all of the information above is correct at the time of submission and once submitted it cannot be changed</strong>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col2half fboxjl">
			<div class="internalpadding">
				<input class="btn btn-primary" type="submit" name="apply-submit" id="apply-submit" value="Submit">
			</div>
		</div>
	</div>
</form>