
function FiltersPopup(fOnSelect)
{
	var self = UI_Shadow();

	self.init = function()
	{
		var sContent = "<table class='label' style='background:white' width='100%' height='100%'></table>";

		//self.windowTitle.innerHTML = " <b> Saved Searches</b>";
		self.shadowPane.innerHTML = sContent;

		self.oTable = self.shadowPane.firstChild;

		self.onmousedown = function(event){ return UI.cancelBubble(event); };

		return self;
	}

	self.show = function()
	{
		AJAX.call("<Filter.getAll />", self.getAll_Response);
	}
	
	self.remove = function(sId)
	{
		for (var i = 0; i < self.oTable.rows.length; i++)
		{
			if (self.oTable.rows[i].sId == sId) self.oTable.deleteRow(i);
		}
	}
	
	self.onDelete = function()
	{
		if (confirm("Delete selected saved search?"))
		{
			AJAX.call(XML.serialize(true, "Filter.delete", "Id", this.sId), self.onDelete_Response);
		}
	}

	self.onSelect = function()
	{
		UI.hide(self);
		if (fOnSelect != null) fOnSelect(this.parentNode.sFilter);
	}

	self.getAll_Response = function(oFilters, sFilters, bSuccess)
	{
		XML.clear(self.oTable);
		for (var oFilter = oFilters.firstChild; oFilter != null; oFilter = oFilter.nextSibling)
		{
			var oRow = self.oTable.insertRow(-1);
			
			oRow.sId = oFilter.getAttribute("Id");
			oRow.sFilter = oFilter.getAttribute("Filter");

			oRow.insertCell(-1).innerHTML = "<img src='"+UI.image+"icon=delete_small.png' class='clickicon'/>";
			oRow.insertCell(-1).innerHTML = oRow.sFilter;

			oRow.cells[1].onclick = self.onSelect;
			oRow.cells[1].onmouseout = function() { this.className = "ui_smartitem"; };
			oRow.cells[1].onmouseover = function() { this.className = "ui_smartitem_over"; };
			
			oRow.cells[1].style.width = "100%";
			oRow.cells[1].style.paddingLeft = "5px";
			oRow.cells[1].style.whiteSpace = "nowrap";

			oRow.cells[0].firstChild.sId = oRow.sId;
			oRow.cells[0].firstChild.onclick = self.onDelete;
		}
	}

	self.onDelete_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess || oResponse.nodeName == "Error") return alert("An error occurd while deleting saved search.");

		self.remove(oResponse.getAttribute("Id"));
	}

	return self.init();
}
