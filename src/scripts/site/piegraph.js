
var iPieGraphCount = 0;

//onUpdated (when the image updates it is called)
function PieGraph(sTag, iWidth, iHeight, fOnUpdated)
{
	var self = document.createElement("DIV");

	self.sTag = sTag;
	self.oTooltip = UI_Tooltip();

	self.init = function()
	{
		iPieGraphCount++;
		var sTable = "<table class='piegraph'><tr><td><img src='"+UI.image+"icon=pie_up.png' class='clickicon'/> <span></span></td></tr><tr><td><img class='piegraph_border' width='"+iWidth+"' height='"+iHeight+"' usemap='#image/map/"+iPieGraphCount+"'/><map name='image/map/"+iPieGraphCount+"'></map></td></tr><tr><td>Total Expenses: <span></span></td></tr><tr><td align='center'></td></tr></table>";

		self.innerHTML = sTable;
		var oTable = self.firstChild;

		self.oMap    = oTable.rows[1].cells[0].lastChild;
		self.oName   = oTable.rows[0].cells[0].lastChild;
		self.oImage  = oTable.rows[1].cells[0].firstChild;
		self.oTotal  = oTable.rows[2].cells[0].lastChild;
		self.oBottom = oTable.rows[3].cells[0];

		var oUpImage = oTable.rows[0].cells[0].firstChild;
		oUpImage.onclick = self.up;
		UI.setTooltip(oUpImage, "Up one level");

		self.oImage.onload = self.updateMap;
		self.onUpdated = UI.defaultValue(fOnUpdated, null);

		return self;
	}

	self.up = function()
	{
		if (self.sName.length != 0)
		{
			var iIndex = self.sName.lastIndexOf(oSession.delimiter);
			self.update( (iIndex != -1 ? self.sName.substring(0, iIndex) : ""));
		}
	}

	self.setMode = function(bFlat)
	{
		var sRequest = XML.serialize(true, "Graphs.getPie", "Tag", self.sTag, "Width", iWidth, "Height", iHeight, "Name", self.sName, "Flat", (bFlat?'true':'false'));
		self.oImage.src = AJAX.url + "?request=" + encodeURIComponent(sRequest) + "&SID=" + Math.random();
	}

	self.update = function(sName)
	{
		self.sName = UI.defaultValue(sName, '');
		var sRequest = XML.serialize(true, "Graphs.getPie", "Tag", self.sTag, "Width", iWidth, "Height", iHeight, "Name", self.sName);
		self.oImage.src = AJAX.url + "?request=" + encodeURIComponent(sRequest) + "&SID=" + Math.random();
	}

	self.updateMap = function()
	{
		AJAX.call(XML.serialize(true, "Graphs.getMap", "Tag", self.sTag), self.getPieMap_Response);
	}

	self.getPieMap_Response = function(oMap, sTagMap, bSuccess)
	{
		if (!bSuccess) return;

		if (self.onUpdated != null) self.onUpdated(oMap);

		var iTotal = parseFloat(oMap.getAttribute("Total"));
		self.oTotal.innerHTML = Utility.formatCurrency(iTotal, oSession.currency);

		var sName = oMap.getAttribute("Name");
		self.oName.innerHTML = (sName.length == 0 ? "[All]" : sName);

		XML.clear(self.oMap);
		for (var oMap = oMap.firstChild; oMap != null; oMap = oMap.nextSibling)
		{
			var iAmount = parseFloat(oMap.getAttribute("Total"));
			var iPercent = Math.round(iAmount/iTotal*1000)/10;

			var oArea     = document.createElement("AREA");
			oArea.name    = oMap.getAttribute("Name");
			oArea.hasSub  = (oMap.getAttribute("hasChildren") == "true");
			oArea.details = oArea.name + " " + Utility.formatCurrency(iAmount, oSession.currency) + " (" + iPercent + "%)";
			oArea.shape   = "polygon";
			self.oMap.appendChild(oArea);

			oArea.onclick     = function(){ if (this.hasSub) {self.oTooltip.hide(); self.update(this.name); } };
			oArea.onmouseout  = function(){ self.oTooltip.hide(); };
			oArea.onmousemove = function(pEvent){ self.oImage.style.cursor = (this.hasSub ? "pointer" : "default"); pEvent=(pEvent?pEvent:event); self.oTooltip.show(this.details, pEvent.clientX+15, pEvent.clientY-10); };

			oArea.coords = oMap.getAttribute("Map").toString();
		}
	}

	return self.init();
}