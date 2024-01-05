
function UI_Tooltip(sTooltip)
{
	var self = UI_Shadow(UI.image + "image=ui/popup/", "ui_popup");

	self.init = function()
	{
		self.shadowPane.className = "ui_popup";
		self.shadowPane.innerHTML = sTooltip;
		return self;
	}

	self.show = function(sTooltip, iX, iY, sHorizontal, sVertical)
	{
		if (sTooltip != null) self.shadowPane.innerHTML = sTooltip;

		sVertical = UI.defaultValue(sVertical, "BOTTOM");
		sHorizontal = UI.defaultValue(sHorizontal, "RIGHT");

		document.body.appendChild(self);

		var pWindowBounds = UI.dimension(document.body);
		var pDimension = UI.dimension(self);
		var pPosition = {x: iX, y: iY};

		if      (sVertical   == "TOP")    pPosition.y -= self.clientHeight;
		else if (sVertical   == "MIDDLE") pPosition.y -= self.clientHeight/2;
		if      (sHorizontal == "LEFT")   pPosition.x -= self.clientWidth;
		else if (sHorizontal == "CENTER") pPosition.x -= self.clientWidth/2;

		if (pPosition.x + pDimension.width > pWindowBounds.width) pPosition.x = pWindowBounds.width - pDimension.width;
		if (pPosition.y + pDimension.height > pWindowBounds.height) pPosition.y = pWindowBounds.height - pDimension.height;

		self.style.top = pPosition.y + document.body.scrollTop;
		self.style.left = pPosition.x + document.body.scrollLeft;
	}

	self.hide = function()
	{
		if (self.parentNode != null) self.parentNode.removeChild(self);
	}

	return self.init();
}