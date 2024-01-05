
function UI_Calendar()
{
	var self = UI_Shadow();
	
	var oTooltip = UI_Tooltip();

	var oHeader = self.shadowPane.appendChild(document.createElement("TABLE"));
	var oBody = self.shadowPane.appendChild(document.createElement("TABLE"));

	self.init = function()
	{
		self.onSelect = null;
		self.onSelectMonth = null;
		self.shadowPane.className = "ui_calendar";

		oBody.className = "ui_calendar_body";

		oHeader.cellPadding = 0;
		oHeader.cellSpacing = 0;

		var oRow = oHeader.insertRow(-1);
		oRow.insertCell(-1).innerHTML = "<img src='"+UI.image+"image=ui/calendar/previous.png' style='cursor:hand'/>";
		oRow.insertCell(-1).className = "ui_calendar_header"
		oRow.insertCell(-1).innerHTML = "<img src='"+UI.image+"image=ui/calendar/next.png' style='cursor:hand'/>";

		self.onscroll = function(event){ return UI.cancelBubble(event); };
		self.onresize = function(event){ return UI.cancelBubble(event); };
		self.onkeypress = function(event){ return UI.cancelBubble(event); };
		self.onmousedown = function(event){ return UI.cancelBubble(event); };

		return self;
	}

	self.paintDate = function(iYear, iMonth, iDate)
	{
		var oDate = new Date(iYear, iMonth-1, iDate, 12, 0, 0);
		
		var oDisplay = oHeader.rows[0].cells[1];

		oHeader.rows[0].cells[0].onclick = function(){ oDate.setMonth(oDate.getMonth()-2); self.set(oDate); };
		oHeader.rows[0].cells[2].onclick = function(){ oDate.setMonth(oDate.getMonth()); self.set(oDate); };

		oDisplay.innerHTML = "<u style='cursor:pointer'>" + CDate.months[iMonth] + ", " + iYear + "</u>";
		oDisplay.onclick = function(){ self.paintMonth(iYear, iMonth, iDate); };
		oDisplay.onmouseout = function(){ oTooltip.hide(); };
		oDisplay.onmousemove = function(oEvent){ oEvent=(oEvent?oEvent:event); oTooltip.show("Click to show year calendar.", oEvent.clientX + 10, oEvent.clientY + 18); };

		XML.clear(oBody);
		self.paintWeeks();
		self.paintDays(oDate);
	}

	self.paintWeeks = function()
	{
		var oRow = oBody.insertRow(-1);
		for (var iWeek = 0; iWeek < 7; iWeek++)
		{
			var oCell = oRow.insertCell(-1);
			oCell.innerHTML = CDate.weeksShort[iWeek+1];
		}
	}

	self.paintDays = function(oDate)
	{
		var iDay = oDate.getDate();
		var iMonth = oDate.getMonth();
		var iYear = oDate.getFullYear();

		oDate.setDate(1);
		var iCurrentWeekDay = oDate.getDay();
		for(var iWeek = 0; iWeek < 6; iWeek++)
		{
			var oRow = oBody.insertRow( -1 );
			for(var iWeekDay = 0; iWeekDay < 7; iWeekDay++)
			{
				var oCell = oRow.insertCell( -1 );
				if(iCurrentWeekDay == iWeekDay)
				{
					var iDate = oDate.getDate();
					oCell.date = iDate;
					oCell.innerHTML = iDate;
					oCell.onmouseout = function() { this.className = ""; };
					oCell.onmouseover = function() { this.className = "ui_calendar_body_over"; };
					oCell.onclick = function(){ if (self.onSelect != null) self.onSelect(iYear, iMonth+1, this.date) };

					if (iDate == iDay) oCell.className = "ui_calendar_body_over";

					oDate.setDate(iDate + 1);
					iCurrentWeekDay = (oDate.getMonth() == iMonth ? oDate.getDay() : -1);
				}
			}

			if(iCurrentWeekDay == -1) break;
		}
	}

	self.paintMonth = function(iYear, iMonth, iDay)
	{
		var oDisplay = oHeader.rows[0].cells[1];
		oHeader.rows[0].cells[0].onclick = function(){ self.paintMonth(iYear-1, iMonth, iDay); };
		oHeader.rows[0].cells[2].onclick = function(){ self.paintMonth(iYear+1, iMonth, iDay); };
		
		oTooltip.hide();
		oDisplay.innerHTML = iYear;
		oDisplay.onclick = null;
		oDisplay.onmouseout = null;
		oDisplay.onmousemove = null;

		XML.clear(oBody);

		var iIndex = 1;
		for (var r = 0; r < 3; r++)
		{
			var oRow = oBody.insertRow(-1);

			for (var c = 0; c < 4; c++)
			{
				var oCell = oRow.insertCell(-1);
				oCell.month = iIndex;
				oCell.innerHTML = CDate.monthsShort[iIndex];
				oCell.className = (iIndex == (iMonth) ? "ui_calendar_cell_month_over" : "ui_calendar_cell_month");
				oCell.onclick = function(){ if (self.onSelectMonth != null && self.onSelectMonth(iYear, this.month, 1) == false) return; self.paintDate(iYear, this.month, iDay); };
				oCell.onmouseout = function(){ this.className = "ui_calendar_cell_month"; };
				oCell.onmouseover = function(){ this.className = "ui_calendar_cell_month_over"; };

				iIndex++;
			}
		}
	}

	self.set = function(oDate)
	{
		if (typeof(oDate) == "string") oDate = oDate.toDate();
		self.paintDate(oDate.getFullYear(), oDate.getMonth()+1, oDate.getDate());
	}

	self.setMonth = function(oDate)
	{
		if (typeof(oDate) == "string") oDate = oDate.toDate();
		self.paintMonth(oDate.getFullYear(), oDate.getMonth()+1, oDate.getDate());
	}

	return self.init();
}