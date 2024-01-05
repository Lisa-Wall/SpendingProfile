
function UI_Table(sId)
{
	var self = document.getElementById(sId);

	self.header = self.appendChild(document.createElement("TABLE"));
	self.content = self.appendChild(document.createElement("DIV"));
	self.body = self.content.appendChild(document.createElement("TABLE"));

	self.orderedBy = null;

	self.init = function()
	{
		self.body.className = "table_content";
		self.body.cellPadding = 0;
		self.body.cellSpacing = 1;

		self.header.className = "table_header";
		self.header.insertRow(-1);
		self.header.cellPadding = 0;
		self.header.cellSpacing = 1;
	}

	self.addHeader = function(sId, sName, bOrderable, bAscending, iWidth, fOnOrdered)
	{
		var pColumn = self.header.rows[0].insertCell(-1);

		pColumn.innerHTML = sName;
		pColumn.className = "table_header_cell";

		pColumn.columnId = sId;
		pColumn.onOrdered = fOnOrdered;
		pColumn.columnName = sName;
		pColumn.ascending = bAscending;
		pColumn.onclick = (bOrderable ? self.onOrderBy : null);

		if (iWidth != null) pColumn.style.width = iWidth;

		return pColumn;
	}

	self.clear = function()
	{
		XML.clear(self.body);

		var pBodyRow = self.body.insertRow(-1);
		var pHeaderRow = self.header.rows[0];
		for (var i = 0; i < pHeaderRow.cells.length; i++) pBodyRow.insertCell(-1).style.width = pHeaderRow.cells[i].clientWidth;

		return self.body;
	}

	self.resizeColumns = function()
	{
		var pBodyRow = self.body.insertRow(0);
		var pHeaderRow = self.header.rows[0];
		for (var i = 0; i < pHeaderRow.cells.length; i++) pBodyRow.insertCell(-1).style.width = pHeaderRow.cells[i].clientWidth;
	}

	self.onOrderBy = function()
	{
		var pColumn = this;
		self.orderBy(pColumn, (pColumn == self.orderedBy ? !pColumn.ascending : pColumn.ascending));
		if (pColumn.onOrdered != null) pColumn.onOrdered(pColumn.columnId, pColumn.ascending);
	}

	self.orderBy = function(oColumn, bAscending)
	{
		//If column is a string thing it is the column id.
		if (typeof(oColumn) == "string") oColumn = self.getColumn(oColumn);

		//If there is already a column that is ordered then repaint it.
		if (self.orderedBy != null) self.orderedBy.innerHTML = self.orderedBy.columnName;

		self.orderedBy = oColumn;
		oColumn.ascending = bAscending;
		oColumn.innerHTML = oColumn.columnName + "<img src='" + UI.image + "image=ui/table/sort_"+(bAscending?"asc":"desc")+".png' style='vertical-align:middle'/>";
	}

	self.getColumn = function(sId)
	{
		var oColumns = self.header.rows[0].cells;
		for (var i = 0; i < oColumns.length; i++) if (oColumns[i].columnId == sId) return oColumns[i];
		return null;
	}

	self.init();

	return self;
}