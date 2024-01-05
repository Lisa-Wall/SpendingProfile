
function PieGraphControll(sId, sTag, sTitle, fOnShowExpenses) //showVendorExpenses showCategoryExpenses
{
	var self = document.getElementById(sId);

	self.init = function()
	{
		self.innerHTML = "<table width='100%' cellpadding='0' cellspacing='0'><tr><td class='piegraph_title'>" + sTitle + "</td><td><img src='"+UI.image+"icon=pie_flat.png' class='clickicon'/></td><td><img src='"+UI.image+"icon=expand.png' class='clickicon'/></td><td><img src='"+UI.image+"icon=table.png' class='clickicon'/></td><td><img src='"+UI.image+"icon=info.png' class='clickicon'/></td></tr><tr><td colspan='5'></td></tr></table>";

		var oTable = self.firstChild;
		var oImages = self.getElementsByTagName("IMG");

		self.oPieGraph = oTable.rows[1].cells[0].appendChild(new PieGraph(sTag, 250, 200, self.onUpdated));
		self.oPieGraph.oImage.className = '';

		oImages[0].onclick = self.toggleMode
		oImages[1].onclick = self.showExpanded;
		oImages[2].onclick = fOnShowExpenses;

		UI.setTooltip(oImages[0], "");
		UI.setTooltip(oImages[1], "View a larger version of the pie chart.");
		UI.setTooltip(oImages[2], "View pie chart data as a list.");
		UI.setHelptip(oImages[3], "Pie Chart", "- Move the mouse over the pie chart to see details of each slice.<br/>- Click on a slice to drill down into its subcategories.<br/>- To get back to the previous level, click the arrow at the top left (<img src='"+UI.image+"icon=pie_up.png' class='clickicon'/>).");

		self.oFlat = oImages[0];
		self.update = self.oPieGraph.update;

		self.oWindow = UI_Window(sTitle, false, false);

		self.oPieExpanded = PieGraph(sTag, 450, 450);
		self.oPieExpanded.oBottom.innerHTML = "<input type='button' class='button' value='Close' style='width:70px'/>";
		self.oPieExpanded.oBottom.firstChild.onclick = self.oWindow.onClose;

		self.oWindow.windowPane.className = "windowpane";
		self.oWindow.windowPane.appendChild(self.oPieExpanded);

		self.bFlat = false;

		return self;
	}

	self.toggleMode = function()
	{
		self.setMode(!self.bFlat);
		self.oPieGraph.setMode(self.bFlat);
	}

	self.setMode = function(bFlat)
	{
		self.bFlat = bFlat
		self.oFlat.src = UI.image + (bFlat ? "icon=pie_tree.png" : "icon=pie_flat.png");
		self.oFlat.sTooltip = (bFlat ? "View multi-level pie chart." : "View a flat version of the pie chart with all categories and subcategories at the same level.");
		return bFlat;
	}

	self.showExpanded = function()
	{
		self.oPieExpanded.update();
		UI.centerWindow(self.oWindow);
	}

	self.onUpdated = function(oMap)
	{
		self.setMode( oMap.getAttribute("Flat") == "true" );

		if (self.oWindow.isVisible()) self.oPieExpanded.setMode(self.bFlat);
	}

	return self.init();
}

