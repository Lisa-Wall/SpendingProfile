
function UI_MonthSelector(sId)
{
	var self = (typeof(sId) == "string" ? document.getElementById(sId) : sId);

	self.init = function()
	{
		self.innerHTML = "<table class='ui_monthselector'><tr><td><img src='"+UI.image+"image=ui/calendar/previous_blue.png' style='cursor:hand' onload='UI.setTooltip(this, sPreviousMonthTooltip)'/></td><td width='100%' align='center' style='cursor:pointer'>No Selection</td><td><img src='"+UI.image+"image=ui/calendar/next_blue.png' style='cursor:hand' onload='UI.setTooltip(this, sNextMonthTooltip)'/></td></tr></table>";

		var oTable = self.firstChild;
		oTable.rows[0].cells[2].firstChild.onclick = self.onNext;
		oTable.rows[0].cells[0].firstChild.onclick = self.onPrevious;

		self.oMonth = oTable.rows[0].cells[1];
		self.oMonth.onclick = self.showCalendar;
		self.oMonth.onmouseout = UI.tooltipHide;
		self.oMonth.onmouseover = UI.tooltipShow;
		self.oMonth.setAttribute("tooltip", sMonthSelectorTooltip);

		self.oCalendar = new UI_Calendar();
		self.oCalendar.onSelectMonth = function(iYear, iMonth, iDay)
		{
			var oDate = new Date(iYear, iMonth-1, iDay)
			if (self.onSelect(oDate.toText())) self.set(oDate);
			UI.hide(self.oCalendar);
			return false;
		}

		self.set(new Date());

		return self;
	}

	self.onSelect = function(sDate){ return true; }
	self.onNext = function() { self.onChange(+1); }
	self.onPrevious = function() { self.onChange(-1); }

	self.onChange = function(iDelta)
	{
		var oDate = self.sDate.toDate();
		oDate.setMonth(oDate.getMonth()+iDelta);
		if (self.onSelect(oDate.toText())) self.set(oDate);
	}

	self.showCalendar = function()
	{
		self.oCalendar.setMonth(self.sDate);
		UI.showPopupRelativeTo(self.oCalendar, self.oMonth, -20, 20);
	}

	self.set = function(oDate)
	{
		if (typeof(oDate) == "string") oDate = oDate.toDate();
		self.sDate = oDate.toText();
		self.oMonth.innerHTML = CDate.months[oDate.getMonth()+1] + ", " + oDate.getFullYear();
	}

	return self.init();
}

sNextMonthTooltip = "Next Month";
sPreviousMonthTooltip = "Previous Month";
sMonthSelectorTooltip = "Select a month";
