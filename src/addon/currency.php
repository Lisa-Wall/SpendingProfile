<div id="account/currency" class="page_area" style="width:100%;display:none">
	<div class="page_title">Change Currency Symbol</div>
	<p>
		Change the currency symbol I use from (<span id="account/currency/old"></span>) to <input id="account/currency/new" type="text" maxlength="4" style="width:50px"/>
		<br/><br/>
		<center>
			<img id="account/currency/loader" style="visibility:hidden;vertical-align:middle" src="<?=$sImage?>icon=loader.gif"/>
			<input id="account/currency/submit" type="submit" value="Change Currency Symbol" onclick="oAccountCurrency.set()"/>
		</center>
	</p>
</div>
