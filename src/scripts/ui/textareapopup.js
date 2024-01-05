
function UI_TextAreaPopup()
{
	var self = UI_WindowPopup();
	self.onEnter = null;

	self.build = function()
	{
		self.contentPane.innerHTML = "<textarea rows='5'></textarea><center><input type='button' class='button' value='Save' style='width:60px'/> <input type='button' class='button' value='Cancel' style='width:60px'/></center>";
		self.textArea = self.contentPane.firstChild;

		var oInputs = self.contentPane.getElementsByTagName("INPUT");
		oInputs[0].onclick = self.onApply;
		oInputs[1].onclick = self.onCancel;

		//Key event listener
		self.textArea.onkeypress = function(oEvent)
		{
			oEvent = (oEvent?oEvent:event);

			if(oEvent.keyCode == KeyCode.ESCAPE) UI.hide(self);
			else if(oEvent.keyCode == KeyCode.ENTER && self.onEnter != null) self.onApply();
		};

		return self;
	}

	self.onClose = function()
	{
		if (self.sValue != self.textArea.value)
		{
			if (confirm("Do you wish to save changes?")) self.onApply();
			else self.onCancel();
		}
		else self.onCancel();
	}

	self.onCancel = function()
	{
		UI.hide(self);
	}

	self.onApply = function()
	{
		UI.hide(self);
		self.onEnter(self.textArea.value);
	}

	self.set = function(sValue, sTitle)
	{
		self.sValue = sValue;
		self.textArea.focus();
		self.textArea.value = sValue;

		self.setTitle(sTitle);
	}

	return self.build();
}
