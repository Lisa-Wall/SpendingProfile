
function ReceiptWindow()
{
	var self = new UI_Window("Receipt", false, true);

	self.sId = 0;

	self.init = function()
	{
		var sForm = "<form action='<?=SERVER?>' target='receiptwindow/frame' method='post' style='padding:0; margin:0' enctype='multipart/form-data'><input type='hidden' name='request' value=''/>"
		var sFrame = "<iframe name='receiptwindow/frame' id='receiptwindow/frame' style='display:none'></iframe>";
	
		self.windowPane.className = "windowpane";
		self.windowPane.innerHTML = "<table width='100%' height='100%'><tr><td>"+sForm+"<span></span></form></td></tr><tr><td height='100%'><div class='scrollpane' style='width:100%;height:100%'><img/></div></td></tr><tr><td align='center' style='padding-top:10px'><button class='button' style='width:70px'>Close</button></td></tr></table>" + sFrame;

		self.oImage = self.windowPane.getElementsByTagName('IMG')[0];
		self.oForm = self.getElementsByTagName('FORM')[0];
		self.oForm.action = oSession.server;
		self.oUploader = UI_Uploader(self.windowPane.getElementsByTagName('SPAN')[0], self.upload, self.removeUpload, self.cancelUpload);
		
		self.getElementsByTagName('BUTTON')[0].onclick = self.onClose;
		
		return self;
	}

	self.open = function(sId, bReceipt, oCaller)
	{
		self.sId = sId;
		self.oCaller = oCaller;
		self.bReceipt = bReceipt;
		self.windowTitle.innerHTML = "Receipt (" + sId + ")";
		self.oUploader.clearInput();

		if (self.bReceipt)
		{
			self.oImage.src = AJAX.url + "?request=" + encodeURIComponent("<Receipt.get Id='"+sId+"'/>") + "&SID=" + Math.random();
			self.oImage.style.display = "";
			self.oUploader.setMode("UPLOADED");
		}
		else
		{
			self.oImage.src = "";
			self.oUploader.setMode("NORMAL");
			self.oImage.style.display = "none";
		}

		self.showReceipt();
	}

	self.showReceipt = function()
	{
		self.style.width = "350";
		self.style.height = "500";

		if (!self.isVisible()) UI.centerWindow(self);
	}

	self.upload = function()
	{
		self.oForm.request.value = XML.serialize(true, "Receipt.add", "Id", self.sId, "Field", "receipt", "Callback", "oReceiptWindow.upload_Response");
		self.oForm.submit();
	}

	self.removeUpload = function()
	{
		if (confirm("Delete the receipt from transaction " + self.sId + "?"))
		{
			AJAX.call(XML.serialize(true, "Receipt.remove", "Id", self.sId), self.remove_Response);
			return true;
		}
		else return false;
	}
	
	self.cancelUpload = function()
	{
		var oFrame = document.getElementById("receiptwindow/frame");
		oFrame.document.close();
		oFrame.document.innerHTML = "";

		self.oUploader.cancel();
	}

	self.upload_Response = function(sResponse, sFile, sSize)
	{
		if (sResponse == "1") self.open(self.sId, true, self.oCaller)
		else self.oUploader.error(sResponse);

		if (self.oCaller) self.oCaller = oTransactionTable.paintReceipt(self.oCaller, true);
	}
	
	self.remove_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess || oResponse.nodeName == "Error")
		{			
			alert(sRC_ErrorRemovingReceipt);
			
			self.oUploader.uploaded();
		}
		else
		{
			self.open(self.sId, false, self.oCaller)

			if (self.oCaller) self.oCaller = oTransactionTable.paintReceipt(self.oCaller, false);
		}
	}

	self.onClose = function()
	{
		if (!self.oUploader.bIsUploading || confirm(sRC_UploadingInProgress)) UI.hide(self);
	}

	return self.init();
}

var sRC_UploadingInProgress = "A receipt is being uploaded. Would you like to cancel the upload and close the window?";
var sRC_ErrorRemovingReceipt = "An error occured while removing the receipt.";
