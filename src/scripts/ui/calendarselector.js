
function UI_CalendarSelector(sId, bAllowToggle, fOnSelect)
{
	var self = document.getElementById(sId);

	self.init = function()
	{
		self.innerHTML = "<table width='100%' height='26' cellpadding='0' cellspacing='0'><tr><td><img src='"+UI.image+"icon=empty.png'/></td><td width='100%' align='center'><span></span><span></span></td><td><img class='clickicon' src='"+UI.image+"image=ui/calendar/period.png'/></td></tr></table>";

		var oTable = self.firstChild;
		var oImages = self.getElementsByTagName("IMG");

		self.oImage = oImages[1];
		UI.setTooltip(self.oImage, '');
		if (bAllowToggle) self.oImage.onclick = self.toggleMode;
		else              self.oImage.style.display = "none";

		self.oMonthSelector = UI_MonthSelector(oTable.rows[0].cells[1].firstChild);
		self.oPeriodSelector = UI_PeriodSelector(oTable.rows[0].cells[1].lastChild);

		self.oMonthSelector.onSelect = function(sDate)
		{
			var oTo = sDate.toDate()
			var oFrom = sDate.toDate();

			oFrom.setDate(1);
			oTo.setDate(oTo.getDays());

			self.oPeriodSelector.set(oFrom.toText(), oTo.toText());
			self.onSelect(oFrom.toText(), null);

			return true;
		}

		self.oPeriodSelector.onSelect = function(sFrom, sTo)
		{
			self.oMonthSelector.set(sFrom);
			self.onSelect(sFrom, sTo);
		}

		self.setMode(true);
		self.oMonthSelector.onSelect(CDate.toString(null));

		if (Utility.isDefined(fOnSelect)) self.onSelect = fOnSelect;

		return self;
	}

	self.onSelect = function(sFrom, sTo){ }

	self.set = function(sFrom, sTo)
	{
		self.oMonthSelector.set(sFrom);
		self.oPeriodSelector.set(sFrom, sTo);
	}

	self.setMode = function(bMonthSelector)
	{
		self.oMonthSelector.isVisible = bMonthSelector;
		self.oMonthSelector.style.display = (bMonthSelector ? "" : "none");

		self.oPeriodSelector.isVisible = !bMonthSelector;
		self.oPeriodSelector.style.display = (bMonthSelector ? "none" : "");

		self.oImage.sTooltip = (bMonthSelector ? "Select a custom date period." : "Select a single month to view.");
	}

	self.toggleMode = function()
	{
		self.setMode(!self.oMonthSelector.isVisible);
	}

	return self.init();
}
