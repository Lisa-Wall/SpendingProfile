<div id="account/password" class="page_area" style="width:100%;display:none">
	<div class="page_title">Change Password</div>
	<br/><br/>

	<fieldset class="page_fieldset" style="width:400px" align="center">
		<legend class="page_fieldset_title">Change Password</legend>
		<br/>
		<table class="page_text" width="400px" border="0" cellpadding="0" cellspacing="5px" align="center">
			<tr>
				<td class="form_label">Old Password:</td>
				<td><input id="account/password/old" class="form_input" type="password" value="" style="width:200px" maxlength="64"/></td>
			</tr>
			<tr>
				<td class="form_label">New Password:</td>
				<td><input id="account/password/new" class="form_input" type="password" value="" style="width:200px" maxlength="64"/></td>
			</tr>
			<tr>
				<td class="form_label">Retype New Password:</td>
				<td><input id="account/password/retype" class="form_input" type="password" value="" style="width:200px" maxlength="64"/></td>
			</tr>
			<tr height="40">
				<td colspan="2" align="center">
					<img id="account/password/loader" style="visibility:hidden;vertical-align:middle" src="<?=$sImage?>icon=loader.gif"/>
					<input id="account/password/submit" class="form_button" type="button" value="Submit" onclick="oAccountPassword.set()"/>
				</td>
			</tr>
		</table>
	</fieldset>
</div>