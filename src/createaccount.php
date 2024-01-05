<? include_once("inc_header.php"); ?>
<? include_once("inc_menu.php"); ?>

<script>
var oCreateAccount = null;
function loadPage() { oCreateAccount = new CreateAccount(); }
</script>

<div class="page_area">

	<div id="createaccount/created" style="display:none">
		<div class="page_title">Account Successfully Created</div>
		<p>Your account has been created and a temporary password has been sent to your email address. You must retrieve it and then <a href="signin.php" style="color:blue">sign in</a>.</p>
		<p><font color="red">**IMPORTANT NOTE: Check your Junk Mail, Bulk Mail or Spam folder because sometimes the email we just sent often gets mislabeled as spam. The email should arrive in seconds; a few minutes at most.</font></p>
		<p align="center"><input type="button" class="form_button" onclick="window.location = 'signin.php'" value="Proceed to Sign In"></p>
	</div>

	<div id="createaccount/create">

	<div class="page_title">Create Account</div>

		<p>To create an account, enter your email address below. Your email address will be your username. You need to enter your real email address because your first password will be emailed to you.</p><br/>

		<span id="createaccount/waitmessage" style="color:red;display:none">Please wait while we create your account...</span>
		<span id="createaccount/message" style="color:red"></span>&nbsp;<br/><br/>

		<fieldset class="page_fieldset" style="width:400px" align="center">
			<legend class="page_fieldset_title">Create Account</legend>

			<table class="page_text" width="400px" border="0" cellpadding="0" cellspacing="5px" align="center">
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr>
					<td class="form_label">Email Address:</td>
					<td><input id="createaccount/email1" name="email1" class="form_input" type="text" value="" style="width:200px" maxlength="64" onkeypress="oCreateAccount.onKeyPress(event)"/></td>
				</tr>
				<tr>
					<td class="form_label">Retype Email Address:</td>
					<td><input id="createaccount/email2" name="email2" class="form_input" type="text" value="" style="width:200px" maxlength="64" onkeypress="oCreateAccount.onKeyPress(event)"/></td>
				</tr>
				<tr>
					<td colspan="2" class="page_footnote">Note: Your first password will be automatically generated and sent to your email address.</td>
				</tr>
				<tr height="40">
					<td colspan="2" align="center">
						<img id="createaccount/loader" style="visibility:hidden;vertical-align:middle" src="<?=$sImage?>icon=loader.gif"/>
						<input id="createaccount/submit" class="form_button" type="button" value="Submit" onclick="oCreateAccount.submit()"/>
					</td>
				</tr>
			</table>
		</fieldset>

		<br/>

		<center>
			<span align="center">
				I have an account. Let me <a href="signin.php">sign in</a>.<br/>
				Not ready yet? Try the <a href="demo.php">live demo</a>!
			</span>
		</center>
	</div>

</div>

<? include_once("inc_footer.php"); ?>