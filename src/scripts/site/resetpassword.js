
function ResetPassword()
{
	var self = this;
	this.form = document.resetpassword;

	this.oLoader = document.getElementById("resetpassword/loader");
	this.oMessage = document.getElementById("resetpassword/message");
	this.oLoaderMessage = document.getElementById("resetpassword/waitmessage");

	this.oResetPanel = document.getElementById("resetpassword/reset");
	this.oResettedPanel = document.getElementById("resetpassword/resetted");

	this.loading = function(bLoading)
	{
		self.form.email.disabled = bLoading;
		self.form.submit.disabled = bLoading;

		self.oLoader.style.visibility = (bLoading ? "visible" : "hidden");
		self.oLoaderMessage.style.display = (bLoading ? "" : "none");
	}

	this.message = function(sMessage)
	{
		self.oMessage.innerHTML = sMessage;
	}

	this.submit = function()
	{
		self.message("");

		if (self.form.email.value.length == 0) return self.message("Please enter an email address.");
		if (!Validate.email(self.form.email.value)) return self.message("Invalid email address.");

		AJAX.call(XML.serialize(true, "User.resetPassword", "Email", self.form.email.value), self.submit_Response, self.loading);
	}

	this.submit_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;

		var sType = oResponse.getAttribute("Type");

		if (sType == "OK")
		{
			document.getElementById("resetpassword/email").innerHTML = self.form.email.value;

			self.form.email.value = "";
			self.oResetPanel.style.display = "none";
			self.oResettedPanel.style.display = "";
		}
		else self.message(sType);
	}

	this.loading(false);
}
