
function Account_Email()
{
	var self = this;

	self.oOld = document.getElementById("account/email/old");
	self.oNew = document.getElementById("account/email/new");
	self.oRetype = document.getElementById("account/email/retype");
	self.oLoader = document.getElementById("account/email/loader");
	self.oSubmit = document.getElementById("account/email/submit");


	self.init = function()
	{
		self.oOld.innerHTML = oSession.email;
	}

	self.loader = function(bLoad)
	{
		self.oNew.disabled = bLoad;
		self.oRetyp.disabled = bLoad;
		self.oSubmit.disabled = bLoad;
		self.oLoader.style.visibility = (bLoad ? "visible" : "hidden");
	}

	self.set = function()
	{
		var sOld = self.oOld.innerHTML;
		var sNew = self.oNew.value;
		var sRetype = self.oRetype.value;

		if      (sNew.length == 0)      return alert("New email can not be empty.");
		else if (sNew == sOld)          return alert("New email is the same as current email address.");
		else if (sNew != sRetype)       return alert("New email does not match retyped email.");
		else if (!Validate.email(sNew)) return alert("New email address is invalid.");

		AJAX.call(XML.serialize(true, "User.setEmail", "Email", sNew), self.response, self.loader);
	}

	self.response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;

		if (oResponse.getAttribute("Type") == 'OK')
		{
			alert("Email updated successfully.");

			window.location.reload();
		}
		else
		{
			alert(oResponse.getAttribute("Message"));
		}
	}

	self.init();
}

var oAccountEmail = null;
