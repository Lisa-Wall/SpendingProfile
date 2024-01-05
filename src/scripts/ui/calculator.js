
function UI_Calculator(bWindowed)
{
	var self = document.createElement("TABLE");

	self.oWindow = null;
	self.onSelect = null;

	self.init = function()
	{
		//Setup the calculator table.
		self.className = "calculator";
		self.cellPadding = 0;
		self.cellSpacing = 1;

		//Setup the screen.
		self.oInput = self.insertRow(-1).insertCell(-1).appendChild(document.createElement("INPUT"));
		self.oInput.type = "text";
		self.oInput.className = "calculator_screen";
		self.oInput.onkeypress = self.onKeyPressed;

		var iIndex = 0;
		var aButton = new Array("<< Use", "C", "7", "8", "9", "/", "4", "5", "6", "x", "1", "2", "3", "-", "0", ".", "=", "+");
		var aValues = new Array("U", "C", "7", "8", "9", " / ", "4", "5", "6", " * ", "1", "2", "3", " - ", "0", ".", "=", " + ");

		for (var r = 0; r < 5; r++)
		{
			var pRow = self.insertRow(-1);
			for (var c = 0; (c < 4) && !(r == 0 && c == 2); c++)
			{
				var pCell = pRow.insertCell(-1);
				pCell.innerHTML = "<input type='button' class='calculator_button' value='" + aButton[iIndex] + "' />";
				pCell.firstChild.keyValue = aValues[iIndex++];
				pCell.firstChild.onclick = self.onButtonPressed;
			}
		}

		self.rows[0].cells[0].colSpan = 4;
		self.rows[1].cells[0].colSpan = 3;
		self.rows[0].cells[0].firstChild.className = "calculator_screen";
		self.rows[1].cells[0].firstChild.className = "calculator_use";
		self.rows[1].cells[1].firstChild.className = "calculator_clear";

		if (UI.defaultValue(bWindowed, true))
		{
			self.oWindow = UI_Window("<img src='"+UI.image+"icon=calculator.png' style='vertical-align: top' height='16'/>Calculator", false, false, false);
			self.oWindow.style.width = 145;
			self.oWindow.windowPane.margin = 4;
			self.oWindow.windowPane.appendChild(self);
			self.oWindow.component = self;
		}

		return self;
	}

	self.evaluate = function()
	{
		// Strip commas, so numbers like 6,762 are not taken as 762
		self.oInput.value = self.oInput.value.replace(/,/g, "");
		self.oInput.focus();

		try{
			self.oInput.value = eval(self.oInput.value);
		}
		catch (exception){}

		return false;
	}

	self.onButtonPressed = function()
	{
		if      (this.keyValue == '=') self.evaluate();
		else if (this.keyValue == 'C') self.oInput.value = "";
		else if (this.keyValue == 'U')
		{
			if (self.onSelect != null) self.onSelect(self.oInput.value);
		}
		else self.oInput.value += this.keyValue;

		self.oInput.focus();
	}

	self.onKeyPressed = function(pEvent)
	{
		var pEvent = (pEvent ? pEvent : event);
		var iCharCode =  (pEvent.charCode ? pEvent.charCode : (pEvent.which ? pEvent.which : pEvent.keyCode));

		// Evaluate expression when enter is pressed
		if(iCharCode == KeyCode.ENTER || iCharCode == KeyCode.EQUAL)  return self.evaluate();

		// Otherwise, block all input except for numeric.
		else  return self.isKeyValide(iCharCode);
	}

	self.isKeyValide= function(iCharCode)
	{
		return ((iCharCode >= KeyCode.NUM_0 && iCharCode <= KeyCode.NUM_9) || iCharCode == KeyCode.BACKSPACE || iCharCode == KeyCode.DELETE || iCharCode == KeyCode.SPACE || iCharCode == KeyCode.PLUS || iCharCode == KeyCode.MINUS || iCharCode == KeyCode.ASTERISK || iCharCode == KeyCode.SLASH_FORWARD || iCharCode == KeyCode.BRACKET_LEFT || iCharCode == KeyCode.BRACKET_RIGHT || iCharCode == KeyCode.PERIOD);
	}

	return self.init();
}