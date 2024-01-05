
function Analysis()
{
	var self = this;

	self.oTooltip = UI_Tooltip();

	self.init = function()
	{
		var oTable = document.getElementById("analysis/categories/table");

		self.oMap = document.getElementById("analysis/map");
		self.oImage = document.getElementById("analysis/image");
		self.oTitle = document.getElementById("analysis/title");
		self.aViewBy = new Array(document.getElementById("analysis/viewby/totals"), document.getElementById("analysis/viewby/category"));
		self.aGraphType = new Array(document.getElementById("analysis/graphtype/bar"), document.getElementById("analysis/graphtype/line"));
		self.oTotalIncome = document.getElementById("analysis/totals/income");
		self.oTotalExpenses = document.getElementById("analysis/totals/expenses");

		self.oLegendTotals = document.getElementById("analysis/totals");
		self.oLegendCategories = document.getElementById("analysis/categories");

		self.oPeriodSelector = new UI_PeriodSelector("analysis/periodselector", true);
		self.oPeriodSelector.onSelect = self.setPeriod;

		self.aCategoryList = new Array();
		self.aCategoryList[0] = oTable.rows[0].cells[1].appendChild( UI_SmartDropdown(self.getCategories, self.onCategorySelected, true) );
		self.aCategoryList[1] = oTable.rows[1].cells[1].appendChild( UI_SmartDropdown(self.getCategories, self.onCategorySelected, true) );
		self.aCategoryList[2] = oTable.rows[2].cells[1].appendChild( UI_SmartDropdown(self.getCategories, self.onCategorySelected, true) );
		self.aCategoryList[3] = oTable.rows[3].cells[1].appendChild( UI_SmartDropdown(self.getCategories, self.onCategorySelected, true) );

		AJAX.call("<Category.getTree />", self.getCategories_Response);
		AJAX.call("<Analysis.setGraph />", self.getAnalysis_Response);
	}

	self.getCategories = function()
	{
		return self.oCategories;
	}

	self.setPeriod = function(sFrom, sTo)
	{
		AJAX.call(XML.serialize(true, "Analysis.setGraph", "From", sFrom, "To", sTo), self.getAnalysis_Response);
	}

	self.setViewBy = function(sType)
	{
		var bTotals = (sType == "TOTALS");
		self.oLegendTotals.style.display = (bTotals ? "" : "none");
		self.oLegendCategories.style.display = (bTotals ? "none" : "");

		AJAX.call(XML.serialize(true, "Analysis.setGraph", "ViewBy", sType), self.getAnalysis_Response);
	}

	self.setGraphType = function(sType)
	{
		AJAX.call(XML.serialize(true, "Analysis.setGraph", "GraphType", sType), self.getAnalysis_Response);
	}

	self.setTotalIncome = function(bChecked)
	{
		AJAX.call(XML.serialize(true, "Analysis.setGraph", "TotalIncome", (bChecked ? "1":"0")), self.getAnalysis_Response);
	}

	self.setTotalExpenses = function(bChecked)
	{
		AJAX.call(XML.serialize(true, "Analysis.setGraph", "TotalExpenses", (bChecked ? "1":"0")), self.getAnalysis_Response);
	}

	self.onCategorySelected = function(sValue, sId)
	{
		var sIds = "";
		for (var i = 0; i < self.aCategoryList.length; i++)
		{
			var sId = self.aCategoryList[i].oInput.sId;
			sIds += (sIds.length == 0 ? "" : ",") + (sId == null ? -i : sId);
		}

		AJAX.call(XML.serialize(true, "Analysis.setGraph", "Categories", sIds), self.getAnalysis_Response);
	}

	self.updateMap = function()
	{
		AJAX.call("<Analysis.getMap />", self.getMap_Response);
	}

	self.getMap_Response = function(oMaps, sMaps, bSuccess)
	{
		if (!bSuccess) return;

		var bTotals = (oMaps.getAttribute("ViewBy") == "TOTALS");

		XML.clear(self.oMap);
		for (var oMap = oMaps.firstChild; oMap != null; oMap = oMap.nextSibling)
		{
			var sId = oMap.getAttribute("Name");
			var sValue = oMap.getAttribute("Value");

			var oArea = document.createElement("AREA");
			oArea.shape = "rect";
			oArea.details = (bTotals ? sId : self.getCategoryFromId(sId)) + ": " + (sValue < 0 ? "<b>+"+(Utility.formatCurrency(Math.abs(sValue), oSession.currency))+"</b>" : Utility.formatCurrency(sValue, oSession.currency));
			oArea.coords = oMap.getAttribute("Map").toString();

			oArea.onmouseout = function(){ self.oTooltip.hide(); };
			oArea.onmousemove = function(pEvent){ pEvent=(pEvent?pEvent:event); self.oTooltip.show(this.details, pEvent.clientX+15, pEvent.clientY-10); };

			self.oMap.appendChild(oArea);
		}
	}

	self.getCategoryFromId = function(sId)
	{
		var oElement = XML.getElementByAttribute(self.oCatReference, "Id", sId, false);
		return (oElement == null ? '' : oElement.getAttribute("Name"))
	}

	self.getAnalysis_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess || oResponse.nodeName == "Error") return;

		self.oCatReference = oResponse;
		var aCategories = oResponse.getAttribute("Categories").split(",");

		self.aViewBy[0].checked = (oResponse.getAttribute("ViewBy") == "TOTALS");
		self.aViewBy[1].checked = (oResponse.getAttribute("ViewBy") == "CATEGORY");
		self.aGraphType[0].checked = (oResponse.getAttribute("GraphType") == "BAR");
		self.aGraphType[1].checked = (oResponse.getAttribute("GraphType") == "LINE");
		self.oTotalIncome.checked = (oResponse.getAttribute("TotalIncome") == "1");
		self.oTotalExpenses.checked = (oResponse.getAttribute("TotalExpenses") == "1");
		self.oPeriodSelector.set(oResponse.getAttribute("From"), oResponse.getAttribute("To"));

		self.oTitle.innerHTML = (oResponse.getAttribute("ViewBy") == "TOTALS" ? "Overall Totals" : "Totals by Category")

		var bTotals = self.aViewBy[0].checked;
		self.oLegendTotals.style.display = (bTotals ? "" : "none");
		self.oLegendCategories.style.display = (bTotals ? "none" : "");

		for (var i = 0; i < aCategories.length; i++) self.aCategoryList[i].set(self.getCategoryFromId(aCategories[i]), aCategories[i]);

		self.oImage.src = AJAX.url + "?request=" + encodeURIComponent("<Analysis.getGraph Width='550' Height='300' />") + "&SID=" + Math.random();
	}

	self.getCategories_Response = function(oCategories, sCategories, bSuccess)
	{
		if (!bSuccess) return;

		self.oCategories = oCategories;
		var oCategory = oCategories.insertBefore( oCategories.ownerDocument.createElement("Category"), oCategories.firstChild );
		oCategory.setAttribute("Name", "[NONE]");
	}

	return self.init();
}