<div id="account/email" class="page_area" style="width:100%;display:none">
	<div class="page_title">Change Email Address</div>
	<p>Your email address is also your username. If you change it, you have to use the new address to sign into your account. Your password will remain the same as before.</p>
	<p>You must be the true owner of the email address you give here. If not, you may accidentally lock yourself out of your account. For example, if you ever forget your password and request a new one, it will be emailed to this address for you to retrieve.</p>

	<fieldset class="page_fieldset" style="width:400px" align="center">
		<legend class="page_fieldset_title">Change Email Address</legend>

		<table class="page_text" border="0" cellpadding="0" cellspacing="5px" align="center">
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td class="form_label">Current Email:</td>
				<td id="account/email/old"></td>
			</tr>
			<tr>
				<td class="form_label">New Email:</td>
				<td><input id="account/email/new" class="form_input" type="text" value="" style="width:200px" maxlength="64"/></td>
			</tr>

			<tr>
				<td class="form_label">Retype New Email:</td>
				<td><input id="account/email/retype" class="form_input" type="text" value="" style="width:200px" maxlength="64"/></td>
			</tr>

			<tr height="40">
				<td colspan="2" align="center">
					<img id="account/email/loader" style="visibility:hidden;vertical-align:middle" src="<?=$sImage?>icon=loader.gif"/>
					<input id="account/email/submit" class="form_button" type="button" value="Submit" onclick="oAccountEmail.set()"/>
				</td>
			</tr>
		</table>
	</fieldset>
</div>