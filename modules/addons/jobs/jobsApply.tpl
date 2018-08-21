<form action="index.php?m=jobs&action=submitInfo" method="post">

	<div class="row">
		<div class="col-md-6 form-group">
			<label for="fName">First Name</label><input type="text" id="fName" class="form-control" name="fName">
		</div>

		<div class="col-md-6 form-group">
			<label for="lName">Last name</label><input type="text" id="lName" class="form-control" name="lName">
		</div>
	</div>

	<div class="row">
		<div class="col-md-6 form-group">
			<label for="skype">Skype</label><input type="text" id="skype" class="form-control" name="skype">
		</div>

		<div class="col-md-6 form-group">
			<label for="email">Email</label><input type="email" name="email" class="form-control" id="email">
		</div>
	</div>

	<div class="row">
		<div class="col-md-12 form-group">
			<label for="why">Why do you want to work for {$companyname}?</label>
			<textarea id="why" class="form-control" name="why" cols="30" rows="10"></textarea>
		</div>

		<div class="col-md-12 form-group">
			<label for="exp">Relevant Experince</label>
			<textarea id="exp" class="form-control" name="exp" cols="30" rows="10"></textarea>
		</div>
	</div>

	<div class="row">
		<div class="checkbox col-md-12">
			<input type="hidden" name="jobId" id="jobId" value="{$job->id}">
			<label>
				<input type="checkbox" name="confirm" value="confirm">
				I agree that all of the information above is correct at the time of submission and once submitted it cannot be changed
			</label>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12 form-group">
			<input class="btn btn-primary" type="submit" name="apply-submit" id="apply-submit" value="Submit">
		</div>
	</div>
</form>