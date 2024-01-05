
function UI_SmartDropdown(fGetValues, fOnChange, bReadOnly, sName)
{
	var self = document.createElement("SPAN");

	self.getValues = UI.defaultValue(fGetValues, null);

	self.init = function()
	{
		var sNodeName = (Utility.isIE() && Utility.isDefined(sName) ? "<INPUT NAME='"+sName+"'/>" : "INPUT");

		self.oInput = self.appendChild( document.createElement(sNodeName) );
		self.oButton = self.appendChild( UI_PushButton(UI.image + "image=ui/dropdown/button.png", "ui_dropdown_button", self.onButtonClick) );
		self.oSmartMenu = new UI_SmartPopup(self.onSelect);

		self.oInput.type = "text";
		self.oInput.name = UI.defaultValue(sName, null);
		self.oInput.className = "textfield";
		self.oInput.onkeyup = self.onKeyPressed;
		self.oInput.onkeydown = self.onKeyDown;
		self.oInput.onclick = self.onClick;
		self.oInput.ondblclick = self.onButtonClick;
		self.oInput.readOnly = UI.defaultValue(bReadOnly, false);
		self.oInput.sId = null;
		self.onChange = UI.defaultValue(fOnChange, null);

		return self;
	}

	self.onClick = function()
	{
		if (self.getValues != null) self.oSmartMenu.show(self.getValues(), self.oInput.value, self.oButton);
	}

	self.onButtonClick = function()
	{
		self.oInput.focus();
		if (self.getValues != null) self.oSmartMenu.show(self.getValues(), "", self.oButton);
	}

	self.set = function(sValue, sId)
	{
		self.oInput.sId = sId;
		self.oInput.value = sValue;
	}

	self.onSelect = function(sValue, sId)
	{
		self.oSmartMenu.hide();
		self.oInput.sId = sId;
		self.oInput.value = sValue;
		self.oInput.focus();

		if (self.onChange != null) self.onChange(sValue, sId);
	}

	self.onKeyDown = function(oEvent)
	{
		oEvent = (oEvent ? oEvent : event);

		if (oEvent.keyCode == KeyCode.TAB)
		{
			self.oSmartMenu.hide();
		}
	}

	self.onKeyPressed = function(oEvent)
	{
		oEvent = (oEvent ? oEvent : event);

		self.oInput.sId = null;
		if (oEvent.keyCode == KeyCode.ESCAPE)
		{
			self.oSmartMenu.hide();
		}
		else if (oEvent.keyCode == KeyCode.ENTER)
		{
			self.oSmartMenu.hide();

			if (self.onChange != null) self.onChange(sValue, null);
		}
		else if (self.getValues != null)
		{
			self.oSmartMenu.show(self.getValues(), self.oInput.value, self.oButton);
		}
	}

	return self.init();
}
