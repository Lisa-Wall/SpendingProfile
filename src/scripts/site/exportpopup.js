
function ExportPopup()
{
	var self = UI_WindowPopup();

	self.init = function()
	{
		var sContent =
"<div style='width:400px'>"+
"Save transaction to your computer as a backup or to use in other accounting software. Choose format:<br/><br/>" +
"<input type='radio' name='export/type' value='csv' checked='1'/><span onclick='this.previousSibling.click()' class='radiotext'>CSV (Excel)</span><br/>"+
"<input type='radio' name='export/type' value='ofx1'/><span onclick='this.previousSibling.click()' class='radiotext'>OFX v1.1 (Microsoft Money or Quicken)</span><br/>"+
"<input type='radio' name='export/type' value='ofx2'/><span onclick='this.previousSibling.click()' class='radiotext'>OFX v2.0</span><br/><br/>"+
"The transations that will be saved are those currently displayed in the transaction list. If this is not the set you wish to save, adjust the time period and/or the search filter until the desired set is displayed." +
"</div><br/><center><input type='button' class='button' value='Save' style='width:70px'/> <input type='button' class='button' value='Cancel' style='width:70px'/></center>";

		self.windowTitle.innerHTML = " <b> Save Transactions</b>";
		self.contentPane.innerHTML = sContent;
		self.contentPane.style.padding = "5px";

		var aTypes = self.contentPane.getElementsByTagName("INPUT");
		aTypes[3].onclick = self.onExport;
		aTypes[4].onclick = self.onClose;

		self.aTypes = new Array();
		self.aTypes[0] = aTypes[0];
		self.aTypes[1] = aTypes[1];
		self.aTypes[2] = aTypes[2];

		return self;
	}

	self.onExport = function()
	{
		UI.hide(self);

		var sType = self.getType();

		if (sType == "csv") sRequest = "<Export.export Format='csv' FileName='spendingprofile' />";
		else if (sType == "ofx1") sRequest = "<Export.export Format='ofx' FileName='spendingprofile' Version='1'/>";
		else if (sType == "ofx2") sRequest = "<Export.export Format='ofx' FileName='spendingprofile' Version='2'/>";

		var oWindow = window.open(AJAX.url + "?request=" + encodeURIComponent(sRequest) + "&SID=" + Math.random(), "Export", "width=200,height=100,scrollbars=no,resizable=no,toolbar=no,menubar=no,location=no,directories=no,status=no", true);
	}

	self.getType = function()
	{
		for (var i = 0; i < self.aTypes.length-1; i++) if (self.aTypes[i].checked) return self.aTypes[i].value;
	}

	return self.init();
}