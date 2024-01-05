
var CDate =
{
	weeksShort: new Array("", "Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"),
	monthsShort: new Array("", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"),

	months: new Array("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"),

	now:function()
	{
		return (new Date()).toText();
	},

	diference:function(oDate1, oDate2)
	{
		if (typeof(oDate1) == "string") oDate1 = CDate.fromString(oDate1);
		if (typeof(oDate2) == "string") oDate2 = CDate.fromString(oDate2);

		return (oDate1.getTime()-oDate2.getTime());
	},

	fromString:function(sDate)
	{
		return new Date(sDate.replace(/^(....).(..).(..)/g, "$2/$3/$1"));
	},

	validate:function(sDate)
	{
		return sDate.match(/^([0-9]{4,4}).([0-9]{1,2}).([0-9]{1,2})$/);
	},

	toString:function(oDate)
	{
		if (oDate == null) oDate = new Date();
		return CDate.format(oDate.getFullYear(), oDate.getDate(), oDate.getMonth() + 1);
	},

	format:function(iYear, iMonth, iDay)
	{
		return iYear + "-" + (iMonth < 10 ? "0" : "") + iMonth + "-" + (iDay < 10 ? "0" : "") + iDay;
	},

	elapsedDays:function(oDate)
	{
		var oToday = new Date();
		return Math.round((oToday.getTime()-oDate.getTime())/(24*60*60*1000));
	},

	getTime:function(oDate)
	{
		if (!Utility.isDefined(oDate)) oDate = new Date();
		return Math.round(oDate.getTime()/1000);
	},

	addMonth:function(oDate, iDelta)
	{
		if (typeof(oDate) == "string") oDate = CDate.fromString(oDate);

		oDate.setMonth(oDate.getMonth()+iDelta);
		return oDate;
    },

    getDays:function(iYear, iMonth)
    {
    	var oDate = new Date(iYear, iMonth-1, 1);
    	return oDate.getDays();
    }
}

String.prototype.toDate = function()
{
	return CDate.fromString(this);
}

Date.prototype.parse = function(sDate)
{
	var aDate = sDate.split("-");
	this.setFullYear(parseInt(aDate[0], 10));
	this.setMonth(parseInt(aDate[1], 10)-1);
	this.setDate(parseInt(aDate[2], 10));
}

Date.prototype.toText = function()
{
	return CDate.format(this.getFullYear(), this.getMonth()+1, this.getDate());
}

Date.prototype.getDays = function()
{
	this.setDate(1);
	this.setHours(12);
	this.setMonth(this.getMonth()+1);
	this.setDate(0);
	return this.getDate();
}
