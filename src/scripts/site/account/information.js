
function Account_Information()
{
	var self = this;

	self.oEmail = document.getElementById("account/info/email");
	self.oCurrency = document.getElementById("account/info/currency");
	self.oStatement = document.getElementById("account/info/statement");

	self.init = function()
	{
		self.oEmail.innerHTML = oSession.email;
		self.oCurrency.innerHTML = oSession.currency;
		self.oStatement.innerHTML = (oSession.preference.get("STATEMENT", "0") == "1" ? "Yes" : "No");
	}

	self.init();
}

var oAccountInfo = null;