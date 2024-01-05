
function ImportOptions()
{
	var self = UI_WindowPopup("Import Options");

	self.init = function()
	{
		var oContent = document.getElementById("import/options");
		var oInputs = oContent.getElementsByTagName("input");

		self.oGuessVendor  = oInputs[0];
		self.oFormatVendor = oInputs[1];
		self.oFormatNotes  = oInputs[2];
		oInputs[3].onclick = self.onSave;
		oInputs[4].onclick = self.onClose;

		self.contentPane.appendChild(oContent);
		oContent.style.display = "";

		AJAX.call("<Import.getPreference />", self.get_Response);

		return self;
	}

	self.set = function(oSettings)
	{
		self.oGuessVendor.checked = (oSettings.getAttribute("GuessVendor") == "true");
		self.oFormatNotes.checked = (oSettings.getAttribute("FormatNotes") == "true");
		self.oFormatVendor.checked = (oSettings.getAttribute("FormatVendor") == "true");
	}

	self.onSave = function()
	{
		var bGuessVendor = (self.oGuessVendor.checked ? "true" : "false");
		var bFormatNotes = (self.oFormatNotes.checked ? "true" : "false");
		var bFormatVendor = (self.oFormatVendor.checked ? "true" : "false");

		AJAX.call(XML.serialize(true, "Import.setPreference", "GuessVendor", bGuessVendor, "FormatVendor", bFormatVendor, "FormatNotes", bFormatNotes), self.save_Response);
	}

	self.get_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess || oResponse.nodeName == "Error") return alert("An error occured while getting options: " + oResponse.getAttribute("Message"));
		self.set(oResponse);
	}

	self.save_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess || oResponse.nodeName == "Error") return alert("An error occured while saving options: " + oResponse.getAttribute("Message"));
		self.set(oResponse);
		UI.hide(self);
	}

	return self.init();
}