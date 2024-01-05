<? $PAGE_INNER = true; $bHttps = true; $bSecured = true; include_once("inc_header.php"); ?>

<script>

function loadPage()
{
	oAccount = new Account();
	oAccountInfo = new Account_Information();
	oAccountEmail = new Account_Email();
	oAccountPassword = new Account_Password();
	oAccountCurrency = new Account_Currency();
	oAccountPreference = new Account_Preference();
	oAccountTellAFriend = new Account_TellAFriend();

	oAccount.show(Utility.getParam("page", "info"));
}

function Account()
{
	var self = this;

	self.oTabs = new Array(document.getElementById("account/info"), document.getElementById("account/email"), document.getElementById("account/currency"), document.getElementById("account/password"), document.getElementById("account/preference"), document.getElementById("account/tellafriend"));
	self.show = function(sTab)
	{
		var sId = "account/" + sTab;
		for (var i = 0; i < self.oTabs.length; i++)
		{
			var oTab = self.oTabs[i];
			oTab.style.display = (oTab.id == sId ? "" : "none");
		}
	}
}

var oAccount = null;

</script>

<table width="100%" height="400px">
	<tr>
		<td valign="top" width="250px">
			<br/><br/>
			<table class="account_menu" cellspacing="4px" width="220px">
				<tr><td class="account_menu_header">I Would Like To...</td></tr>
				<tr><td><a href="javascript:oAccount.show('info')">View Account Information</a></td></tr>
				<tr><td><a href="javascript:oAccount.show('password')">Change Password</a></td></tr>
				<tr><td><a href="javascript:oAccount.show('email')">Change Email</a></td></tr>
				<tr><td><a href="javascript:oAccount.show('currency')">Change Currency Symbol</a></td></tr>
				<tr><td><a href="javascript:oAccount.show('preference')">Change Statements Preference</a></td></tr>
				<tr><td>&nbsp;</td></tr>
				<tr><td><a href="javascript:oAccount.show('tellafriend')">Tell-A-Friend!</a></td></tr>
			</table>
		</td>
		<td valign="top">
		<? include_once('addon/information.php'); ?>
		<? include_once('addon/password.php'); ?>
		<? include_once('addon/currency.php'); ?>
		<? include_once('addon/email.php'); ?>
		<? include_once('addon/preference.php'); ?>
		<? include_once('addon/tellafriend.php'); ?>
		</td>
	</tr>
</table>

<? include_once("inc_footer.php"); ?>