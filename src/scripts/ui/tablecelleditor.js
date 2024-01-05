
var UI_TableCellEditor =
{
	oPopup: null,
	oCalendar: null,
	oTextArea: null,
	oDropdown: null,

	oCell: null,
	sValue: null,

	cancel:function(oCell, sValue)
	{
		if (UI_TableCellEditor.oCell != null)
		{
			UI_TableCellEditor.oCell.removeChild(UI_TableCellEditor.oCell.firstChild);
			UI_TableCellEditor.oCell.innerHTML = UI_TableCellEditor.sValue;
		}

		UI_TableCellEditor.oCell = oCell;
		UI_TableCellEditor.sValue = sValue;
	},

	apply:function()
	{
		//If something is being edited.
		if (UI_TableCellEditor.oCell != null && UI_TableCellEditor.oCell.firstChild != null && UI_TableCellEditor.oCell.firstChild.apply)
		{
			UI_TableCellEditor.oCell.firstChild.apply();
		}
	},

	textField:function(oCell, sValue, fOnChange)
	{
		UI_TableCellEditor.apply();
		UI_TableCellEditor.cancel(oCell, sValue);

		var fApply = function()
		{
			var sNewValue = oCell.firstChild.value;
			UI_TableCellEditor.cancel(null, null);
			fOnChange(oCell, sValue, sNewValue);
		}

		oCell.innerHTML = "<input type='text' class='form_input' style='width:100%'/>";
		oCell.firstChild.onclick = UI.cancelBubble;
		oCell.firstChild.focus();
		oCell.firstChild.apply = fApply
		oCell.firstChild.onblur = fApply; //function(){ UI_TableCellEditor.cancel(null, null); }; //editing cancellation
		oCell.firstChild.value = XML.toString(sValue);
		oCell.firstChild.onkeypress = function(oEvent)
		{
			oEvent = (oEvent?oEvent:event);

			//Editing cancellation
			if (oEvent.keyCode == KeyCode.ESCAPE) UI_TableCellEditor.cancel(null, null);
			//Pressing the ENTER key submitts the value.
			else if (oEvent.keyCode == KeyCode.ENTER) oCell.firstChild.apply();
		};
	},

	calendar:function(oCell, sValue, fOnChange)
	{
		UI_TableCellEditor.apply();
		UI_TableCellEditor.cancel(null, null);

		if (UI_TableCellEditor.oCalendar == null) UI_TableCellEditor.oCalendar = new UI_Calendar();

		//Set the value to the calendar.
		UI_TableCellEditor.oCalendar.set(sValue);

		//show the calendar
		UI.showPopupRelativeTo(UI_TableCellEditor.oCalendar, oCell, 0, 15);

		//when a date is picked, show it in the cell, again in the format yyyy-mm-dd (dashes inclusive)
		UI_TableCellEditor.oCalendar.onSelect = function(iYear, iMonth, iDay)
		{
			var sNewValue = iYear + "-" + (iMonth < 10 ? "0" + iMonth : iMonth) + "-" + (iDay < 10 ? "0" + iDay : iDay);

			UI.hide(UI_TableCellEditor.oCalendar);
			fOnChange(oCell, sValue, sNewValue);
		};
	},

	textArea:function(oCell, oValue, fOnChange)
	{
		UI_TableCellEditor.apply();
		UI_TableCellEditor.cancel(null, null);

		//Create text area popup if not already created.
		if (UI_TableCellEditor.oTextArea == null) UI_TableCellEditor.oTextArea = new UI_TextAreaPopup();

		//Show text area popup.
		UI.showPopupRelativeTo(UI_TableCellEditor.oTextArea, oCell, 0, 15, true);

		UI_TableCellEditor.oTextArea.set(XML.toString(oValue.value), oValue.title);

		//Listen to enter key to notify OnChange function.
		UI_TableCellEditor.oTextArea.onEnter = function(sNewValue)
		{
			fOnChange(oCell, oValue.value, sNewValue);
		};
	},

	dropdown:function(oCell, oValue, fOnChange)
	{
		UI_TableCellEditor.apply();
		UI_TableCellEditor.cancel(null, null);

		if (UI_TableCellEditor.oPopup == null) UI_TableCellEditor.oPopup = new UI_SmartPopup();

		UI_TableCellEditor.oPopup.onSelect = function(sNewValue)
		{
			UI_TableCellEditor.oPopup.hide();
			fOnChange(oCell, oValue.value, sNewValue);
		};

		UI_TableCellEditor.oPopup.show(oValue.items, "", oCell, {x: -3, y: 0}, {position: "BOTTOM", alignment: "LEFT"}, {minWidth: 20, minHeight: 20, maxWidth: 300, maxHeight: 300});
	},

	smartdropdown:function(oCell, oValue, fOnChange)
	{
		UI_TableCellEditor.apply();
		UI_TableCellEditor.cancel(oCell, oValue.value);

		if (UI_TableCellEditor.oDropdown == null) UI_TableCellEditor.oDropdown = new UI_SmartDropdown();

		var fApply = function()
		{
			var sId = UI_TableCellEditor.oDropdown.oInput.sId;
			var sNewValue = UI_TableCellEditor.oDropdown.oInput.value;
			UI_TableCellEditor.cancel(null, null);
			fOnChange(oCell, oValue.value, sNewValue, sId);
		}

		var oDropdown = UI_TableCellEditor.oDropdown;

		oDropdown.oInput.value = XML.toString(oValue.value);
		oDropdown.oInput.style.width = oCell.clientWidth-20;
		oDropdown.onclick = UI.cancelBubble;
		oDropdown.getValues = function(){ return oValue.items; };
		oDropdown.onkeyup = function(oEvent)
		{
			oEvent = (oEvent ? oEvent : event);
			if (oEvent.keyCode == KeyCode.ESCAPE) UI_TableCellEditor.cancel(null, null);
			else if (oEvent.keyCode == KeyCode.ENTER) fApply();
		}

		oCell.innerHTML = "";
		oCell.appendChild(oDropdown);
		oCell.firstChild.apply = fApply;

		oDropdown.oInput.focus();
	}
}