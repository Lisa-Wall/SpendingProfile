<? include_once("inc_header.php"); ?>
<? include_once("inc_menu.php"); ?>

<script>
	var oResetPassword = null;
	function loadPage() { oResetPassword = new ResetPassword(); }
</script>

<div class="page_area">

	<div id="resetpassword/resetted" style="display:none">
		<div class="page_title">Success! You have created a new password</div>

		<p>Your new password has been sent to your email address (<span id="resetpassword/email"></span>).</p>

		You need to retrieve it, and then <a href="signin.php">sign in</a>.<br/><br/>
		It is a good idea to change your password the next time you sign in.
	</div>

	<div id="resetpassword/reset">

		<div class="page_title">Reset your password</div>

		<br/><br/>

		<span id="resetpassword/waitmessage" style="color:red;display:none">Please wait while we reset your password...</span>
		<span id="resetpassword/message" style="color:red"></span>&nbsp;<br/>

		<br/>
		<center>
			<form name="resetpassword" onsubmit="oResetPassword.submit(); return false;" method="post">

				<fieldset class="page_fieldset" style="width:380px">
					<legend class="page_fieldset_title">Enter your email address</legend>

					<table class="page_text" width="380px" border="0" cellpadding="0" cellspacing="5px" align="center">
						<tr><td colspan="2">&nbsp;</td></tr>
						<tr>
							<td class="form_label">Email&nbsp;Address:</td>
							<td><input name="email" class="form_input" type="text" maxlength="64" style="width:280px;"/></td>
						</tr>

						<tr height="40">
							<td colspan="2" align="center">
								<img id="resetpassword/loader" style="visibility:hidden;vertical-align:middle" src="images/icons/loader.gif"/>
								<input name="submit" class="form_button" type="submit" value="Email me a new password" style="width:240px"/>
							</td>
						</tr>
					</table>
				</fieldset>

			</form>
		</center>

		<br/>
		<p align="center">
			No account? Create one <a href="createaccount.php">here</a>.<br/>
			I have an account. Let me <a href="signin.php">sign in</a>.
		</p>

	</div>
</div>

<? include_once("inc_footer.php"); ?>