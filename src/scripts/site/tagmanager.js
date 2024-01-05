
function TagManager(sTag, oManager)
{
	var self = this;

	self.oTagEditor = null;

	self.setEditor = function(oEditor)
	{
		self.oTagEditor = oEditor;
	}

	self.get = function(fResponse)
	{
		AJAX.call(XML.serialize(true, sTag+".getAll"), self.get_Response);
	}

	self.add = function(sName)
	{
		AJAX.call(XML.serialize(true, sTag+".add", "Name", sName), self.add_Response);
	}

	self.remove = function(sId)
	{
		AJAX.call(XML.serialize(true, sTag+".delete", "Id", sId), self.remove_Response);
	}

	self.rename = function(sId, sName)
	{
		AJAX.call(XML.serialize(true, sTag+".rename", "Id", sId, "Name", sName), self.rename_Response);
	}

	self.onClose = function(iModifed)
	{
		oManager.refresh(sTag);
	}

	self.get_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;
		self.oTagEditor.update(oResponse);
	}

	self.add_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;
		if (oResponse.getAttribute("Type") == "INVALID_ARGUMENTS") return alert("Entered name is invalid.");
		if (oResponse.getAttribute("Type") == "SERVER_ERROR") return alert("A technical error occured. Please try again.");
		if (oResponse.getAttribute("Type") == "ALREADY_EXISTS") return alert("Entry already exists.");

		self.oTagEditor.add(oResponse.getAttribute("Id"), oResponse.getAttribute("Name"));
	}

	self.remove_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess || oResponse.nodeName == "Error") return alert("This entry cannot be deleted because it is being used in at least one transaction.");
		self.oTagEditor.remove(oResponse.getAttribute("Id"));
	}

	self.rename_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;
		else if (oResponse.getAttribute("Type") == "ALREADY_EXISTS")
		{
			if (confirm("Entry already exists. Would you like to merge this entry with the existing one?"))
			{
				var sId = oResponse.getAttribute("Id");
				var sName = oResponse.getAttribute("Name");
				AJAX.call(XML.serialize(true, sTag+".rename", "Id", sId, "Name", sName, "Replace", "true"), self.rename_Response);
			}
		}
		else
		{
			self.oTagEditor.modify(oResponse.getAttribute("Id"), oResponse.getAttribute("Name"));

			var iReplaced = oResponse.getAttribute("Replaced");
			if (iReplaced != null && iReplaced.length > 0) self.oTagEditor.remove(iReplaced);
		}
	}
}