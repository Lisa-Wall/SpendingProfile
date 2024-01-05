
function CreateAccount()
{
	var self = this;

	this.oEmail1 = document.getElementById("createaccount/email1");
	this.oEmail2 = document.getElementById("createaccount/email2");
	this.oSubmit = document.getElementById("createaccount/submit");
	this.oLoader = document.getElementById("createaccount/loader");
	this.oMessage = document.getElementById("createaccount/message");
	this.oLoaderMessage = document.getElementById("createaccount/waitmessage");

	this.oCreatePanel = document.getElementById("createaccount/create");
	this.oCreatedPanel = document.getElementById("createaccount/created");

	this.loading = function(bLoading)
	{
		self.oEmail1.disabled = bLoading;
		self.oEmail2.disabled = bLoading;
		self.oSubmit.disabled = bLoading;
		self.oLoader.style.visibility = (bLoading ? "visible" : "hidden");
		self.oLoaderMessage.style.display = (bLoading ? "" : "none");
	}

	this.message = function(sMessage)
	{
		self.oMessage.innerHTML = sMessage;
	}

	this.onKeyPress = function(oEvent)
	{
		oEvent = (oEvent?oEvent:event);
		if (oEvent.keyCode == 13) self.submit();
	}

	this.submit = function()
	{
		self.message("");

		//Insure the emails are valid and equal
		if (self.oEmail1.value.length == 0) return self.message("Please enter an email address.");
		if (self.oEmail1.value != self.oEmail2.value) return self.message("The email addresses do not match.");
		if (!Validate.email(self.oEmail1.value)) return self.message("Invalid email address.");

		//do the ajax call to create account for the user.
		AJAX.call(XML.serialize(true, "User.create", "Email", self.oEmail1.value), self.submit_Response, self.loading);
	}

	this.submit_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;

		var sType = oResponse.getAttribute("Type");

		if (sType != "OK")
		{
			self.message(oResponse.getAttribute("Message"));
		}
		else
		{
			self.oEmail1.value = "";
			self.oEmail2.value = "";
			self.oCreatePanel.style.display = "none";
			self.oCreatedPanel.style.display = "";
		}
	}

	this.loading(false);
}