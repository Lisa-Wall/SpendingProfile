
function UI_PeriodSelector(sId, bMonthPeriod)
{
	var self = (typeof(sId) == "string" ? document.getElementById(sId) : sId);

	self.bMonthPeriod = UI.defaultValue(bMonthPeriod, false);

	self.init = function()
	{
		self.innerHTML = "<table class='ui_periodselector'><tr><td><b>From:</b> <span style='cursor:pointer'></span>&nbsp;<img class='clickicon' src='"+UI.image+"icon=calendar.png'/></td><td><b>To:</b> <span style='cursor:pointer'></span>&nbsp;<img class='clickicon' src='"+UI.image+"icon=calendar.png'/></td><td></td><td><input class='button' type='button' value='Go' style='width:50px'/></td></tr></table>";
		var aSpans = self.getElementsByTagName("SPAN");
		var aInput = self.getElementsByTagName("INPUT");
		var aImages = self.getElementsByTagName("IMG");

		aInput[0].onclick = self.onApply;
		aImages[1].onclick = self.showToCalendar;
		aImages[0].onclick = self.showFromCalendar;

		self.oTo = aSpans[1];
		self.oFrom = aSpans[0];

		self.oTo.onclick = self.showToCalendar;
		self.oFrom.onclick = self.showFromCalendar;

		self.oToCalendar = new UI_Calendar();
		self.oFromCalendar = new UI_Calendar();

		self.oToCalendar.onSelect = function(iYear, iMonth, iDay)
		{
			if (self.bMonthPeriod) iDay = CDate.getDays(iYear, iMonth);

			UI.hide(self.oToCalendar);
			var sDate = CDate.format(iYear, iMonth, iDay);
			if (self.validate(self.oFrom.innerHTML, sDate)) self.oTo.innerHTML = sDate;

			return false;
		}

		self.oFromCalendar.onSelect = function(iYear, iMonth, iDay)
		{
			if (self.bMonthPeriod) iDay = 1;

			UI.hide(self.oFromCalendar);
			var sDate = CDate.format(iYear, iMonth, iDay);
			if (self.validate(sDate, self.oTo.innerHTML)) self.oFrom.innerHTML = sDate

			return false;
		}

		if (self.bMonthPeriod)
		{
			self.oToCalendar.onSelectMonth = self.oToCalendar.onSelect;
			self.oFromCalendar.onSelectMonth = self.oFromCalendar.onSelect;
		}

		var oDate = new Date();
		self.set(oDate.toText(), oDate.toText());

		return self;
	}

	self.onSelect = function(sFrom, sTo){}

	self.validate = function(sFrom, sTo)
	{
		var bValid = (CDate.diference(sFrom, sTo) <= 0);
		if (!bValid) alert("From date must be before To date.");
		return bValid;
	}

	self.onApply = function()
	{
		self.onSelect(self.oFrom.innerHTML, self.oTo.innerHTML);
	}

	self.set = function(sFrom, sTo)
	{
		if (typeof(sTo) == "date") sTo = sTo.toText();
		if (typeof(sFrom) == "date") sFrom = sFrom.toText();

		self.oTo.innerHTML = sTo;
		self.oFrom.innerHTML = sFrom;
	}

	self.showToCalendar = function()
	{
		if (self.bMonthPeriod) self.oToCalendar.setMonth(self.oTo.innerHTML);
		else                   self.oToCalendar.set(self.oTo.innerHTML);
		UI.showPopupRelativeTo(self.oToCalendar, self.oTo, -75, 20);
	}

	self.showFromCalendar = function()
	{
		if (self.bMonthPeriod) self.oFromCalendar.setMonth(self.oFrom.innerHTML);
		else                   self.oFromCalendar.set(self.oFrom.innerHTML);
		UI.showPopupRelativeTo(self.oFromCalendar, self.oFrom, -85, 20);
	}

	return self.init();
}