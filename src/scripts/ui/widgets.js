
var KeyCode =
{
	SPACE : 32,
	NUM_0 : 48,
	NUM_1 : 49,
	NUM_2 : 50,
	NUM_3 : 51,
	NUM_4 : 52,
	NUM_5 : 53,
	NUM_6 : 54,
	NUM_7 : 55,
	NUM_8 : 56,
	NUM_9 : 57,

	BACKSPACE       : 8,
	DELETE          : 127,
	SPACE           : 32,
	PLUS            : 43,
	MINUS           : 45,
	ASTERISK        : 42,
	SLASH_FORWARD   : 47,
	BRACKET_LEFT    : 40,
	BRACKET_RIGHT   : 41,
	PERIOD          : 46,
	EQUALS          : 61,
	ENTER           : 13,
	ESCAPE          : 27,
	TAB             : 9
}

var UI =
{
	image: "",
	oTooltip:null,
	oHelptip:null,

	tooltipShow:function(oEvent, oComponent)
	{
		if (UI.oTooltip == null) UI.oTooltip = UI_Tooltip();

		oEvent = (oEvent ? oEvent : event);
		oComponent = UI.defaultValue(oComponent, this);

		var sTooltip = (typeof(oComponent) == "string" ? oComponent : oComponent.getAttribute("tooltip"));

		if (sTooltip.length > 0) UI.oTooltip.show(sTooltip, oEvent.clientX + 10, oEvent.clientY + 18);
	},

	tooltipHide:function()
	{
		UI.oTooltip.hide();
	},

	setTooltip:function(oComponent, sTooltip)
	{
		if (UI.oTooltip == null) UI.oTooltip = UI_Tooltip();

		oComponent.sTooltip = sTooltip;

		var fShowTooltip = function(oEvent){ oEvent = (oEvent?oEvent:event); if (oComponent.sTooltip.length > 0) UI.oTooltip.show(oComponent.sTooltip, oEvent.clientX + 10, oEvent.clientY + 18); };

		UI.eventAttach(oComponent, "mouseout", UI.oTooltip.hide);
		UI.eventAttach(oComponent, "mousemove", fShowTooltip);
	},

	setHelptip:function(oComponent, sTitle, sMessage)
	{
		if (UI.oHelptip == null) UI.oHelptip = UI_Helptip();

		oComponent.sTitle = sTitle;
		oComponent.sMessage = sMessage;
		oComponent.onclick = function()
		{
			UI.oHelptip.set(oComponent.sTitle, oComponent.sMessage);
			UI.showPopupRelativeTo(UI.oHelptip, oComponent, 0, 15, true);
		}
	},

	showHelptip:function(oComponent, sTitle, sMessage)
	{
		if (UI.oHelptip == null) UI.oHelptip = UI_Helptip();

		UI.oHelptip.set(sTitle, sMessage);
		UI.showPopupRelativeTo(UI.oHelptip, oComponent, 0, 15, true);
	},


	defaultValue:function(pParam, pDefault)
	{
		return (typeof(pParam) == "undefined" ? pDefault : pParam);
	},


	/* Events */

	cancelBubble:function(oEvent)
	{
		oEvent=(oEvent?oEvent:event);
		oEvent.cancelBubble = true;
		return false;
	},

	eventFire:function(oParent, sEvent, oEvent)
	{
		var oCurrent = oParent;
		while ((oCurrent = XML.next(oParent, oCurrent)) != null) if (eval("oCurrent."+sEvent) != null) eval("oCurrent."+sEvent+"(oEvent)");
	},


	eventAttach:function(oObject, sEvent, fFunction)
	{
	if (oObject.addEventListener)
	{
		if (sEvent == "mousewheel") sEvent = "DOMMouseScroll";
		oObject.addEventListener(sEvent, fFunction, true);
	}
	else if (oObject.attachEvent) oObject.attachEvent("on" + sEvent, fFunction);
	else return false;

	return true;
	},

	eventDetach:function(oObject, sEvent, fFunction, bUseCapture)
	{
		if (oObject.removeEventListener)
		{
			if (sEvent == "mousewheel") sEvent = "DOMMouseScroll";
			oObject.removeEventListener(sEvent, fFunction, bUseCapture);
		}
		else if (oObject.detachEvent) oObject.detachEvent("on" + sEvent, fFunction);
		else return false;

		return true;
	},

	/* Positions, sizes, bounds and dimensions */

	position:function(oComponent)
	{
		var pPosition = {x: 0, y: 0};

		while (oComponent != null)
		{
			pPosition.x += oComponent.offsetLeft - (oComponent.scrollLeft ? oComponent.scrollLeft : 0);
			pPosition.y += oComponent.offsetTop - (oComponent.scrollTop ? oComponent.scrollTop : 0);

			if (Utility.isIE()) oComponent = oComponent.offsetParent;
			else                oComponent = (oComponent.parentNode != null && oComponent.parentNode.nodeName == "DIV" && (oComponent.parentNode.scrollTop != 0 || oComponent.parentNode.scrollLeft != 0) ? oComponent.parentNode : oComponent.offsetParent);
		}

		return pPosition;
	},

	dimension:function(pComponent)
	{
		return { width: pComponent.clientWidth, height: pComponent.clientHeight};
	},

	bounds:function(pComponent)
	{
		var pPosition = UI.position(pComponent);

		return {x: pPosition.x, y: pPosition.y, width: pComponent.clientWidth, height: pComponent.clientHeight};
	},

	setBounds:function(pComponent, oBounds)
	{
		pComponent.style.top = oBounds.y;
		pComponent.style.left = oBounds.x;
		pComponent.style.width = oBounds.width;
		pComponent.style.height = oBounds.height;
	},

	inBounds:function(iValue, iMin, iMax)
	{
		if      (iValue < iMin) iValue = iMin;
		else if (iValue > iMax) iValue = iMax;
		return iValue;
	},


	/* Windows, popups */

	centerWindow:function(oWindow, iOffsetX, iOffsetY)
	{
		iOffsetX = UI.defaultValue(iOffsetX, 0);
		iOffsetY = UI.defaultValue(iOffsetY, 0);

		document.body.appendChild(oWindow);

		var oWindowSize = UI.dimension(oWindow);
		var oBrowserSize = UI.dimension(document.body);

		var iTop = (oBrowserSize.height - oWindowSize.height)/2;
		var iLeft = (oBrowserSize.width - oWindowSize.width)/2;

		oWindow.style.top = (iTop < 1 ? 1 : iTop) + iOffsetY;
		oWindow.style.left = (iLeft < 1 ? 1 : iLeft) + iOffsetX;
	},

	showLoader:function(oParent, bShow)
	{
		if (!bShow && oParent.loader != null)
		{
			oParent.loader.parentNode.removeChild(oParent.loader);
			oParent.loader = null;
		}
		else if (bShow)
		{
			var oBounds = UI.bounds(oParent);
			var oPanel = (oParent.loader != null ? oParent.loader : document.createElement("DIV"));

			oPanel.className = "ui_loader";
			oPanel.style.top = oBounds.y + document.body.scrollTop;
			oPanel.style.left = oBounds.x + document.body.scrollLeft;
			oPanel.style.width = oBounds.width;
			oPanel.style.height = oBounds.height;

			oParent.loader = oPanel;
			document.body.appendChild(oPanel);
		}
	},

	showRelativeTo:function(oComponent, oRelativeTo, iOffsetX, iOffsetY, bEnsureWithinWindow, oMaxSize)
	{
		bEnsureWithinWindow = UI.defaultValue(bEnsureWithinWindow, false);

		document.body.appendChild(oComponent);

		var pPosition = UI.position(oRelativeTo);

		var iX = pPosition.x + iOffsetX;
		var iY = pPosition.y + iOffsetY;

		if (bEnsureWithinWindow)
		{
			if (iX + oComponent.clientWidth > document.body.clientWidth) iX = document.body.clientWidth - oComponent.clientWidth;
			if (iY + oComponent.clientHeight > document.body.clientHeight) iY = document.body.clientHeight - oComponent.clientHeight;
			if (iY < 0) iY = 0;
			if (iX < 0) iX = 0;
		}
/*
		if (Utility.isDefined(oMaxSize))
		{
			if (oComponent.clientHeight >= oMaxSize.height)
			{
				oComponent.panel.style.overflow = "auto";
				oComponent.panel.style.height = oMaxSize.height-40;
			}
			else
			{
				oComponent.panel.style.height = "auto";
				oComponent.panel.style.overflow = "visible";
			}
		}
*/
		oComponent.style.top = iY + document.body.scrollTop;
		oComponent.style.left = iX + document.body.scrollLeft;
	},

	showPopupRelativeTo:function(oComponent, oRelativeTo, iOffsetX, iOffsetY, bEnsureWithinWindow, oMaxSize)
	{
		oComponent.hideFunction = function(oEvent)
		{
			oEvent = (oEvent?oEvent:event);
			if      (oEvent.type == "scroll") UI.hide(oComponent);
			else if (oEvent.type == "resize") UI.hide(oComponent);
			else if (oEvent.type == "mousedown") UI.hide(oComponent);
			else if (oEvent.type == "keypress" && oEvent.keyCode == KeyCode.ESCAPE) UI.hide(oComponent);
		};

		window.onscroll = oComponent.hideFunction;
		window.onresize = oComponent.hideFunction;
		document.body.onkeypress = oComponent.hideFunction;
		document.body.onmousedown = oComponent.hideFunction;

		//Show the component.
		UI.showRelativeTo(oComponent, oRelativeTo, iOffsetX, iOffsetY, bEnsureWithinWindow, oMaxSize);
	},

	hide:function(oComponent)
	{
		if (oComponent.parentNode != null) oComponent.parentNode.removeChild(oComponent);
		if (oComponent.hideFunction != null)
		{
			window.onscroll = null;
			window.onresize = null;
			document.body.onkeypress = null;
			document.body.onmousedown = null;

			oComponent.hideFunction = null;

			if (oComponent.onHide) oComponent.onHide();
		}
	},

	show:function(oObject, oRelative, oPosition, oOffset, oBounds, bAutoHide)
	{
		//Objec tis a popup.
		if (UI.defaultValue(bAutoHide, false))
		{
			oObject.hideFunction = function(oEvent)
			{
				oEvent = (oEvent?oEvent:event);
				if      (oEvent.type == "scroll") UI.hide(oObject);
				else if (oEvent.type == "resize") UI.hide(oObject);
				else if (oEvent.type == "mousedown") UI.hide(oObject);
				else if (oEvent.type == "keypress" && oEvent.keyCode == KeyCode.ESCAPE) UI.hide(oObject);
			};

			window.onscroll = oObject.hideFunction;
			window.onresize = oObject.hideFunction;
			document.body.onkeypress = oObject.hideFunction;
			document.body.onmousedown = oObject.hideFunction;
		}

		//If the max is greater than the window then reset the max.
		if (oBounds.maxWidth  > document.body.clientWidth ) oBounds.maxWidth  = document.body.clientWidth;
		if (oBounds.maxHeight > document.body.clientHeight) oBounds.maxHeight = document.body.clientHeight;

		//Make sure width and hight is not exceeded.
		if (oObject.clientWidth >= oBounds.maxWidth)
		{
			oObject.panel.style.overflow = "auto";
			oObject.panel.style.width = oBounds.maxWidth-40;
		}
		else if (oObject.clientWidth <= oBounds.minWidth)
		{
			oObject.panel.style.overflow = "visible";
			oObject.panel.style.width = oBounds.minWidth;
		}
		else
		{
			oObject.panel.style.width = "auto";
		}

		if (oObject.clientHeight >= oBounds.maxHeight)
		{
			oObject.panel.style.overflow = "auto";
			oObject.panel.style.height = oBounds.maxHeight-40;
		}
		else
		{
			oObject.panel.style.height = "auto";
			oObject.panel.style.overflow = "visible";
		}

		//Position the menu.
		var oBounds = {x: 0, y: 0, width: oObject.clientWidth, height: oObject.clientHeight};
		if (oPosition.position == "TOP" || oPosition.position == "BOTTOM")
		{
			oBounds.y = (oPosition.position == "TOP" ? oRelative.y - oBounds.height : oRelative.y + oRelative.height);
			oBounds.x = (oPosition.alignment == "RIGHT" ? (oRelative.x + oRelative.width - oBounds.width) : oRelative.x);
		}
		else if (oPosition.position == "LEFT" || oPosition.position == "RIGHT")
		{
			oBounds.x = (oPosition.position == "LEFT" ? oRelative.x - oBounds.width : oRelative.x + oRelative.width);
			oBounds.y = (oPosition.alignment == "TOP" ? (oRelative.y + oRelative.height - oBounds.height) : oRelative.y);
		}

		//If positioned outisde the visiable area then move to the visible area.
		var oViewport = {x: 0, y: 0, width: (document.body.clientWidth), height: (document.body.clientHeight)}
		if (oBounds.x < oViewport.x) oBounds.x = oViewport.x
		if (oBounds.y < oViewport.y) oBounds.y = oViewport.y;
		if (oBounds.x + oBounds.width  > oViewport.width ) oBounds.x = oViewport.width  - oBounds.width - 16;
		if (oBounds.y + oBounds.height > oViewport.height) oBounds.y = oViewport.height - oBounds.height - 16;

		//Finally set the location of the popup.
		oObject.style.top  = oOffset.y + oBounds.y + document.body.scrollTop;
		oObject.style.left = oOffset.x + oBounds.x + document.body.scrollLeft;
	},

	/* General UI functions */

	expandCollapse:function(oImage, sBody)
	{
		var oBody = document.getElementById(sBody);

		var bExpanded = (oBody.style.display == "");

		oImage.expanded = bExpanded;
		oImage.src = UI.image + "icon=" + (bExpanded ? "open.png" : "close.png");
		oBody.style.display = (bExpanded ? "none" : "");
	},

	expand:function(oImage, bExpand)
	{
		if (oImage.pane == null) oImage.pane = document.getElementById(oImage.getAttribute("panel"));

		var bExpand = (typeof(bExpand) == "undefined" ? (oImage.pane.style.display != "") : bExpand);

		oImage.expanded = bExpand;
		oImage.src = UI.image + "icon=" + (bExpand ? "close.png" : "open.png");
		oImage.pane.style.display = (bExpand ? "" : "none");
	},


	display:function(sId, bShow)
	{
		var oComponent = document.getElementById(sId);
		if (oComponent != null) oComponent.style.display = (bShow ? "" : "none");
	},


	disable:function(sId, bDisabled)
	{
		var oElement = document.getElementById(sId);
		if(oElement != null) oElement.disabled = bDisabled;
	},


	/* Form */

	setValue:function(oInput, sValue, bForce)
	{
		if (typeof(oInput) == "string") oInput = document.getElementById(oInput);
		if (oInput == null) return;

		var sSet = oInput.getAttribute("set");
		var sName = oInput.nodeName.toUpperCase();

		if      (oInput.set)          oInput.set(sValue);
		else if (sSet != null)        eval(sSet+"(oInput, sValue)");
		else if (sName == "TEXTAREA") oInput.value = sValue;
		else if (sName == "SELECT")   UI.setDropDown(oInput, sValue, sValue, bForce);
		else if (sName == "INPUT")
		{
			var sType = oInput.type.toUpperCase();
			if (sType == "CHECKBOX" || sType == "RADIO") UI.setCheckbox(oInput, sValue);
			else                                         oInput.value = sValue;
		}
		else oInput.innerHTML = sValue;

		if(oInput.onchange != null) oInput.onchange();
	},

	setCheckbox:function(oInput, sValue)
	{
		if      (typeof(sValue) == "string") sValue = sValue.toLowerCase();
		else if (typeof(sValue) == "boolean") sValue = (sValue ? "true" : "false");
		else if (typeof(sValue) == "number") sValue = (sValue > 0 ? "true" : "false");

		oInput.checked = (sValue == "1" || sValue == "true");
	},

	setDropDown:function(oSelect, sValue, sText, bForce)
	{
		bForce = UI.defaultValue(bForce, false);

		if (!bForce) oSelect.value = sValue;
		else
		{
			for (var i = 0; i < oSelect.options.length; i++)
			{
				if (oSelect.options[i].value == sValue)
				{
					oSelect.options[i].selected = true;
					return;
				}
			}

			var oOption = oSelect.appendChild(document.createElement("OPTION"));
			oOption.value = sValue;
			oOption.innerHTML = sText;
			oOption.selected = true;
		}
	},

	getValue:function(oInput)
	{
		if (typeof(oInput) == "string") oInput = document.getElementById(oInput);
		if (oInput == null) return null;

		var sValue = null;
		var sGet = oInput.getAttribute("get");
		var sName = oInput.nodeName.toUpperCase();

		if      (oInput.get)          sValue = oInput.get();
		else if (sGet != null)        sValue = eval(sGet+"(oInput)");
		else if (sName == "SELECT")   sValue = oInput.value;
		else if (sName == "TEXTAREA") sValue = oInput.value;
		else if (sName == "INPUT")
		{
			var sType = oInput.type.toUpperCase();
			if (sType == "CHECKBOX" || sType == "RADIO") sValue = (oInput.checked ? "1" : "0");
			else                                         sValue = oInput.value;
		}
		else sValue = oInput.innerHTML

		return sValue;
	},

	populateDropDown:function(oDropDown, oOptions, sSelect, sValueAttr, sTextAttr)
	{
		if (typeof(oDropDown) == "string") oDropDown = document.getElementById(oDropDown);

		XML.clear(oDropDown);

		oOption = (oOptions != null ? oOptions.firstChild : null);
		while(oOption != null)
		{
			if(oOption.nodeType == 1)
			{
				var oValue = oDropDown.appendChild(document.createElement("OPTION"));
				var sValue = oOption.getAttribute(sValueAttr);
				var sText  = oOption.getAttribute(sTextAttr);

				if (sValue == sSelect) oValue.selected = true;

				oValue.object     = oOption;
				oValue.value      = sValue;
				oValue.innerHTML  = sText;
			}

			oOption = oOption.nextSibling;
		}

		if(oDropDown.onchange) oDropDown.onchange();

		return oDropDown;
	},

	selectTab:function(sId, bSelect)
	{
		var pTab = document.getElementById(sId);
		var sClass = (bSelect ? "tab_selected" : "tab");

		pTab.className = sClass;
		pTab.rows[0].cells[0].className = sClass + "_left";
		pTab.rows[0].cells[1].className = sClass + "_middle";
		pTab.rows[0].cells[2].className = sClass + "_right";
	},

	nextRow:function(oTable, oRow, iCells)
	{
		if (oRow == null || oRow.nextSibling == null)
		{
			oRow = oTable.insertRow(-1);
			for (var i = 0; i < iCells; i++) oRow.insertCell(-1);
		}
		else
		{
			oRow = oRow.nextSibling;
			oRow.style.display = "";
		}

		return oRow;
	},

	clearTable:function(oTable, iCells)
	{
		for (var i = 0; i < oTable.rows.length; i++) oTable.rows[i].style.display = "none";
		return (oTable.rows.length > 0 ? oTable.rows[0] : UI.nextRow(oTable, null, iCells));
	}
};