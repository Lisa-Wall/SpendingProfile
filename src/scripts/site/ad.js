
function Ad(sId)
{
	var self = this;

	self.oPanel = document.getElementById(sId);
	self.iTimer = 0;
	self.iCounter = 0;

	self.init = function()
	{
		self.oPanel.innerHTML = "<table class='ad'><tr><td width='100%' style='font-weight:bold;padding: 0 5 0 5'></td><td align='right'><span class='ad_button'>&lt;</span>&nbsp;<span class='ad_button'>&gt;</span></td></tr><tr><td colspan='2' style='padding: 0 5 0 5;height:100%' valign='top'></td></tr></table>";
		
		var oTable = self.oPanel.firstChild;
		
		oTable.rows[0].cells[1].firstChild.onclick = self.onPrevious;
		oTable.rows[0].cells[1].lastChild.onclick = self.onNext;
		
		self.oTitle = oTable.rows[0].cells[0];
		self.oContent = oTable.rows[1].cells[0];

		self.get();
	}

	self.get = function()
	{
		window.clearTimeout(self.iTimer);
		AJAX.call("<Ad.get Index='"+self.iCounter+"' />", self.get_Response);
	}

	self.get_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess || oResponse.nodeName == 'Error') return;
	
		self.oContent.innerHTML = sResponse;
		self.oTitle.innerHTML = self.oContent.firstChild.getAttribute("_Title");
		
		var iDuration = self.oContent.firstChild.getAttribute("_Duration");
		
		if (iDuration != "0")
		{
			self.iTimer = window.setTimeout(self.onNext, parseInt(iDuration)*1000);
		}
	}

	self.onNext = function()
	{
		self.iCounter++;
		self.get();
	}

	self.onPrevious = function()
	{
		self.iCounter--;
		self.get();
	}

	self.init();
}