
function UI_Uploader(sId, fUpload, fRemove, fCancel)
{
	var self = (typeof(sId) == "string" ? document.getElementById(sId) : sId);

	self.sMode = null;

	self.init = function()
	{
		self.innerHTML = "<table class='ui_uploader'><tr><td><input name='receipt' type='file' style='width:200'/></td><td><img src='"+UI.image+"icon=loader.gif' onload='UI.setTooltip(this, sUploadPleaseWait)'/></td><td><img src='"+UI.image+"icon=error.png'/></td><td><a href='javascript:;'>Remove</a></td><td><a href='javascript:;'>Cancel</a></td><td><img src='"+UI.image+"icon=info.png' class='clickicon' onload='UI.setHelptip(this, \"Upload Receipt\", sUploadReceiptHelptip)'/></td></tr></table>";

		var oTable = self.firstChild;

		self.oInput = oTable.rows[0].cells[0].firstChild;
		self.oLoader = oTable.rows[0].cells[1];
		self.oError = oTable.rows[0].cells[2];
		self.oRemove = oTable.rows[0].cells[3];
		self.oCancel = oTable.rows[0].cells[4];

		self.oInput.onchange = self.onUpload;
		self.oCancel.firstChild.onclick = self.onCancel;
		self.oRemove.firstChild.onclick = self.onRemove;

		UI.setTooltip(self.oError, "");
		UI.setTooltip(self.oRemove, sUploadRemoveTooltip);
		UI.setTooltip(self.oCancel, sUploadCancelTooltip);

		self.setMode("NORMAL");

		return self;
	}
	
	self.reset = function()
	{
		if (self.bIsUploading && fCancel) fCancel();
		self.setMode("NORMAL");
	}

	self.setMode = function(sMode, sError)
	{
		self.sMode = sMode;
		self.bIsUploading = false;

		if (sMode == "NORMAL")
		{
			self.oLoader.style.display = "none";
			self.oError.style.display = "none";
			self.oRemove.style.display = "none";
			self.oCancel.style.display = "none";
			self.oInput.disabled = false;
		}
		else if (sMode == "UPLOADING")
		{
			self.bIsUploading = true;
			self.oLoader.style.display = "";
			self.oError.style.display = "none";
			self.oRemove.style.display = "none";
			self.oCancel.style.display = "";
			self.oInput.disabled = true;
		}
		else if (sMode == "UPLOADED")
		{
			self.oLoader.style.display = "none";
			self.oError.style.display = "none";
			self.oRemove.style.display = "";
			self.oCancel.style.display = "none";
			self.oInput.disabled = true;
		}
		else if (sMode == "REMOVING")
		{
			self.oLoader.style.display = "";
			self.oError.style.display = "none";
			self.oRemove.style.display = "";
			self.oCancel.style.display = "none";
			self.oInput.disabled = true;
		}
		else if (sMode == "CANCELING")
		{
			self.oLoader.style.display = "";
			self.oError.style.display = "none";
			self.oRemove.style.display = "none";
			self.oCancel.style.display = "";
			self.oInput.disabled = true;
		}
		else if (sMode == "ERROR")
		{
			self.oLoader.style.display = "none";
			self.oError.style.display = "";
			self.oRemove.style.display = "none";
			self.oCancel.style.display = "none";
			self.oInput.disabled = false;
			
			self.oError.sTooltip = sError;
		}
	}
	
	self.error = function(sMessage)
	{
		self.setMode("ERROR", sMessage);
	}
	
	self.uploaded = function()
	{
		self.setMode("UPLOADED");
	}

	self.onUpload = function()
	{
		if (fUpload) fUpload();
		self.setMode("UPLOADING");
	}
	
	self.cancel = function()
	{
		self.clearInput();
		self.setMode("NORMAL");
	}
	
	self.onCancel = function()
	{
		self.setMode("CANCELING");
		if (fCancel) fCancel();
	}

	self.remove = function()
	{
		self.clearInput();
		self.setMode("NORMAL");
	}

	self.onRemove = function()
	{
		if (fRemove && !fRemove()) return;
		self.setMode("REMOVING");
	}


	self.clearInput = function()
	{
		var oCell = self.firstChild.rows[0].cells[0];
		oCell.innerHTML = oCell.innerHTML;
		self.oInput = oCell.firstChild;
		self.oInput.onchange = self.onUpload;
	}

	return self.init();
}


var sUploadRemoveTooltip = "Remove the uploaded file.";
var sUploadCancelTooltip = "Cancel the current upload";
var sUploadPleaseWait = "Uploading file. Please Wait...";
var sUploadReceiptHelptip =
"Add a receipt to a transaction:<ol>" +
"<li>Scan or otherwise obtain an image of the receipt. <br/>Supported image types are: png, jpeg, and gif.</li>" +
"<li>Use the <b>Browse</b> button to select the receipt image.</li>" +
"<li>Wait for the file to upload.</li></ol>" + 
"<p>Note: Receipt image files cannot be greater than 256KB in size.</p>";