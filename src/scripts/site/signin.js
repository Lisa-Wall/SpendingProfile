
function SignIn()
{
	var self = this;
	self.form = document.signin;

	self.oLoader = document.getElementById("signin/loader");
	self.oMessage = document.getElementById("signin/message");
	self.oLoaderMessage = document.getElementById("signin/waitmessage");

	self.init = function()
	{
		self.loading(false);
	}

	self.loading = function(bLoading)
	{
		self.form.email.disabled = bLoading;
		self.form.password.disabled = bLoading;
		self.form.apply.disabled = bLoading;

		if (!bLoading) self.form.email.focus();

		self.oLoader.style.visibility = (bLoading ? "visible" : "hidden");
		self.oLoaderMessage.style.display = (bLoading ? "" : "none");
	}

	self.message = function(sMessage)
	{
		self.oMessage.innerHTML = sMessage;
	}

	self.submit = function()
	{
		self.message("");

		if (self.form.email.value.length == 0) return self.message("Please specify an email address and password.");
		if (self.form.password.value.length == 0) return self.message("Please specify an email address and password.");
		if (!Validate.email(self.form.email.value)) return self.message("Email address is invalid.");

		AJAX.call(XML.serialize(true, "User.login", "Email", self.form.email.value, "Password", self.form.password.value), self.submit_Response, self.loading);

		return false;
	}

	self.submit_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;

		var sType = oResponse.getAttribute("Type");

		if (sType == "OK")
		{
			self.form.onsubmit = null;
			self.form.submit();
		}
		else if (sType == "TOO_MANY_ATTEMPTS")
		{
			self.message("Too many attempts! Your account has been locked. To unlock it please reset your password <a href='resetpassword.php'>here</a>.");
		}
		else if (sType == "MANY_ATTEMPTS_WARNING")
		{
			self.message("Invalid email or password. Note: too may attempts will lock the account. If you need a new password please click <a href='resetpassword.php'>here</a>.");
		}
		else self.message(oResponse.getAttribute("Message"));
	}

	self.init();
}

