<div id="account/tellafriend" class="page_area" style="width:100%;display:none">
	<div class="page_title">Tell A Friend</div>
	<p>Do you like Spending Profile? Tell your friends about it! All you need to do is give their email address below.</p>

	<fieldset class="page_fieldset" style="width:520px" align="center">
		<legend class="page_fieldset_title">Tell A Friend</legend>

		<table class="page_text" border="0" cellpadding="0" cellspacing="10px" align="center">
			<tr>
				<td class="form_label">Your friend`s email address:</td>
				<td><input id="account/tellafriend/email" class="form_input" type="text" value="" style="width:250px" maxlength="64"/></td>
			</tr>
			<tr>
				<td class="form_label">Brief message to include in the <br/>invitation email to your friend:</td>
				<td><textarea id="account/tellafriend/message" class="form_textarea" style="width:250px" maxlength="255"/></textarea></td>
			</tr>
			<tr height="40">
				<td colspan="2" align="center">
					<img id="account/tellafriend/loader" style="visibility:hidden;vertical-align:middle" src="<?=$sImage?>icon=loader.gif"/>
					<input id="account/tellafriend/submit" class="form_button" type="button" value="Submit" onclick="oAccountTellAFriend.set()"/>
				</td>
			</tr>
		</table>
	</fieldset>

	<br/><br/><br/>
	<div class="page_title">Your Referral List</div>
	<br/>
	<table class="account_menu" border="0" width="100%">
		<tr>
			<td class="account_menu_header" align="center" width="120px">Email</td>
			<td class="account_menu_header" align="center">Status</td>
			<td class="account_menu_header" align="center" width="100px">Date/Time&nbsp;of&nbsp;referral</td>
		</tr>
		<tbody id="account/tellafriend/referrals">
			<tr>
				<td colspan="3" align="center">You currently have no referrals.</td>
			</tr>
		</tbody>
	</table>
</div>
