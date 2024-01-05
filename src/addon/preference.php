<div id="account/preference" class="page_area" style="width:100%;display:none">
	<div class="page_title">Account Statements</div>
	<p>
		<input id="account/preference/statement" type="checkbox" /><span onclick="this.previousSibling.click()" style="cursor:pointer"><b>Yes, I would like to receive monthly statements from Spending Profile.</b></span>
		<br/><br/>
		<center>
			<img id="account/preference/loader" style="visibility:hidden;vertical-align:middle" src="<?=$sImage?>icon=loader.gif"/>
			<input id="account/preference/submit" class="form_button" type="button" value="Submit" onclick="oAccountPreference.set()"/>
		</center>
	</p>
	<p>Monthly statements show a summary of your financial activity over the past month. Click <a href="<?=$sImage?>image=sample/statement.png">here</a> to see a sample statement.</p>
	<p>Your statement will arrive once a month by email.</p>
</div>