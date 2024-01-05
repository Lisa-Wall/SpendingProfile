
function Account_Currency()
{
	var self = this;

	self.oOld = document.getElementById("account/currency/old");
	self.oNew = document.getElementById("account/currency/new");
	self.oLoader = document.getElementById("account/currency/loader");
	self.oSubmit = document.getElementById("account/currency/submit");

	self.oSymbols = XML.arrayToXML(new Array("$", "£", "€", "¥", "¤", "¢"), "Type", "Name");

	self.init = function()
	{
		self.oOld.innerHTML = oSession.currency;

		self.oPopup = new UI_SmartPopup();
		self.oPopup.onSelect = function(sNewValue)
		{
			self.oPopup.hide();
			self.oNew.value = sNewValue;
		}

		self.oNew.onclick = function()
		{
			self.oPopup.show(self.oSymbols, "", self.oNew, {x: -10, y: 0}, {position: "BOTTOM", alignment: "LEFT"}, {minWidth: 50, minHeight: 20, maxWidth: 300, maxHeight: 300});
		}
		self.oNew.onkeypress = function(oEvent)
		{
			self.oPopup.hide();
		}
	}

	self.loader = function(bLoad)
	{
		self.oNew.disabled = bLoad;
		self.oSubmit.disabled = bLoad;
		self.oLoader.style.visibility = (bLoad ? "visible" : "hidden");
	}

	self.set = function()
	{
		var sOld = self.oOld.innerHTML;
		var sNew = self.oNew.value;

		if      (sNew.length == 0) return alert("New currency symbol can not be empty.");
		else if (sNew == sOld)     return alert("New currency symbol is the same as the old one.");

		AJAX.call(XML.serialize(true, "User.setCurrency", "Currency", sNew), self.response, self.loader);
	}

	self.response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;

		if (oResponse.getAttribute("Type") == 'OK')
		{
			alert("Currency symbol updated successfully.");
			window.location.reload();
		}
		else
		{
			alert(oResponse.getAttribute("Message"));
		}
	}

	self.init();
}

var oAccountCurrency = null;
