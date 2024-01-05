
function Account_TellAFriend()
{
	var self = this;

	self.oEmail = document.getElementById("account/tellafriend/email");
	self.oMessage = document.getElementById("account/tellafriend/message");
	self.oLoader = document.getElementById("account/tellafriend/loader");
	self.oSubmit = document.getElementById("account/tellafriend/submit");

	self.oReferrals = document.getElementById("account/tellafriend/referrals");

	self.init = function()
	{
		self.show();
	}

	self.loader = function(bLoad)
	{
		self.oEmail.disabled = bLoad;
		self.oMessage.disabled = bLoad;
		self.oSubmit.disabled = bLoad;
		self.oLoader.style.visibility = (bLoad ? "visible" : "hidden");
	}

	self.set= function()
	{
		var sEmail = self.oEmail.value;
		var sMessage = self.oMessage.value;

		if (!Validate.email(sEmail)) return alert("Email address is invalid.");

		AJAX.call(XML.serialize(true, "User.tellAFriend", "Email", sEmail, "Message", sMessage), self.response, self.loader);
	}

	self.response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;

		if (oResponse.getAttribute("Type") == 'OK')
		{
				alert("Thank you! Your friend has been added to your referral list (see below).");
				window.location.reload();
		}
		else
		{
				alert(oResponse.getAttribute("Message"));
		}
	}

	self.show = function(bShow)
	{
		AJAX.call("<User.getReferrals />", self.referrals, self.loader);
	}

	self.referrals = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;

		if (oResponse.nodeName == "ERROR") return alert(oResponse.getAttribute("Message"));

		XML.clear(self.oReferrals);

		var aMessages = { "NOTCREATED": "Still waiting for friend to create an account.",
											"NOTLOGGEDIN": "Your friend has created an account but has not signed in yet. To sign in, they must retrieve the password that was sent to their email address.",
											"CREATED": "Your friend has become a member of Spending Profile!" };

		for (var oReferral = oResponse.firstChild; oReferral != null; oReferral = oReferral.nextSibling)
		{
			var oRow = self.oReferrals.insertRow(-1);

			oRow.insertCell(-1).innerHTML = oReferral.getAttribute("Email");
			oRow.insertCell(-1).innerHTML = aMessages[oReferral.getAttribute("Status")];
			oRow.insertCell(-1).innerHTML = oReferral.getAttribute("ReferredOn");
		}
	}

	self.init();
}

var oAccountTellAFriend = null;