
function Account_Preference()
{
	var self = this;

	self.oStatement = document.getElementById("account/preference/statement");
	self.oLoader = document.getElementById("account/preference/loader");
	self.oSubmit = document.getElementById("account/preference/submit");

	self.init = function()
	{
		self.oStatement.checked = (oSession.preference.get("STATEMENT") == "1");
	}

	self.set = function()
	{
		oSession.preference.set("STATEMENT", (self.oStatement.checked ? "1" : "0"));

		AJAX.call(XML.serialize(true, "User.setPreference", "Preference", oSession.preference.toString()), self.response, self.loader);
	}

	self.response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;

		if (oResponse.getAttribute("Type") == 'OK')
		{
			alert("Preference updated successfully.");
			window.location.reload();
		}
		else
		{
			alert(oResponse.getAttribute("Message"));
		}
	}

	self.init();
}

var oAccountPreference = null;