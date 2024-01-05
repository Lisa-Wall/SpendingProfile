
function Account_Password()
{
	var self = this;

	self.oOld = document.getElementById("account/password/old");
	self.oNew = document.getElementById("account/password/new");
	self.oRetype = document.getElementById("account/password/retype");
	self.oLoader = document.getElementById("account/password/loader");
	self.oSubmit = document.getElementById("account/password/submit");

	self.init = function()
	{
	}

	self.loader = function(bLoad)
	{
		self.oOld.disabled = bLoad;
		self.oNew.disabled = bLoad;
		self.oRetype.disabled = bLoad;
		self.oSubmit.disabled = bLoad;
		self.oLoader.style.visibility = (bLoad ? "visible" : "hidden");
	}

	self.set = function()
	{
		var sOld = self.oOld.value;
		var sNew = self.oNew.value;
		var sRetype = self.oRetype.value;

		if      (sOld.length == 0) return alert("Old password is empty.");
		else if (sNew.length == 0) return alert("Password can not be empty.");
		else if (sNew == sOld)     return alert("New password is the same as old password.");
		else if (sNew != sRetype)  return alert("New password does not match retyped password.");

		AJAX.call(XML.serialize(true, "User.setPassword", "OldPassword", sOld, "NewPassword", sNew), self.response, self.loader);
	}

	self.response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;

		if (oResponse.getAttribute("Type") == 'OK')
		{
			alert("Password changed successfully.");

			self.oOld.value = "";
			self.oNew.value = "";
			self.oRetype.value = "";
		}
		else
		{
			alert(oResponse.getAttribute("Message"));
		}
	}

	self.init();
}

var oAccountPassword = null;