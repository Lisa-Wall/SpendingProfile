
function UI_Window(sTitle, bMaximize, bResize, bKeepInWindow)
{
	var self = UI_Shadow();

	var oEvent = null;

	bResize = UI.defaultValue(bResize, true);
	bMaximize = UI.defaultValue(bMaximize, true);
	bKeepInWindow = UI.defaultValue(bKeepInWindow, false);

	self.init = function()
	{
		//Set basic window class.
		self.shadowPane.className = "ui_window";

		//Build the window structure.
		var sTitleBar = "<table class='ui_window_titlebar' cellpadding='0' cellspacing='0'><tr><td width='100%'>"+sTitle+"</td><td align='right'></td></tr></table>"
		var sStatusBar = "<img src='"+UI.image+"image=ui/window/resizer.png' ondrag='return false;' style='cursor:nw-resize;display:"+(bResize?'show':'none')+"'/>";
		self.shadowPane.innerHTML = "<table cellpadding='0' cellspacing='0' width='100%' height='100%'><tr><td>"+sTitleBar+"</td></tr><tr><td height='100%' width='100%'></td></tr><tr><td align='right'>"+sStatusBar+"</td></tr></table>";

		var oWindow = self.shadowPane.firstChild;

		//Get titlebar, window Pane and resizer.
		self.titleBar   = oWindow.rows[0].cells[0].firstChild;
		self.windowPane = oWindow.rows[1].cells[0];
		self.resizer    = oWindow.rows[2].cells[0].firstChild;

		//Get the titlbe bar cells.
		self.controls    = self.titleBar.rows[0].cells[1];
		self.windowTitle = self.titleBar.rows[0].cells[0];

		//Add the controlls if required.
		if (bMaximize) self.controls.appendChild(UI_PushButton(UI.image+"image=ui/window/button_maximize.png", "ui_window_button", self.onMaximize));
		self.controls.appendChild(UI_PushButton(UI.image+"image=ui/window/button_close.png", "ui_window_button", function(){ self.onClose() }));

		//Add events to the window.
		self.resizer.onmousedown = createEvent(self.onResize, self.clearEvent);
		self.titleBar.onmousedown = createEvent(self.onMove, self.clearEvent);
		self.controls.onmousedown = function(oEvent){ return UI.cancelBubble(oEvent); };

		return self;
	}

	self.onClose = function()
	{
		if (self.parentNode != null) self.parentNode.removeChild(self);
	}

	self.onMaximize = function()
	{
		this.bounds = UI.bounds(self);
		this.onclick = self.onRestore;

		self.style.top = 0;
		self.style.left = 0;
		self.style.width = document.body.clientWidth;
		self.style.height = document.body.clientHeight;
	}

	self.onRestore = function()
	{
		this.onclick = self.onMaximize;
		UI.setBounds(self, this.bounds);
	}

	self.onResize = function(event)
	{
		var oBounds = UI.bounds(self);

		if (oEvent != null)
		{
			self.style.width  = UI.inBounds(oEvent.width + (event.clientX - oEvent.mouseX), 1, 9999);
			self.style.height = UI.inBounds(oEvent.height + (event.clientY - oEvent.mouseY), 1, 9999);
		}
		else oEvent = { x: (self.offsetLeft-document.body.scrollLeft), y: (self.offsetTop-document.body.scrollTop), width: oBounds.width, height: oBounds.height, mouseX: event.clientX, mouseY: event.clientY };
	}

	self.onMove = function(event)
	{
		var oBounds = UI.bounds(self);

		if(oEvent != null)
		{
			var iTop = oEvent.y + (event.clientY - oEvent.mouseY);
			var iLeft = oEvent.x + (event.clientX - oEvent.mouseX);

			//We want to make sure that the window bar is always accessable.
			self.style.top  = (iTop < 1 ? 1 : iTop);
			self.style.left = (iLeft < 100 - oBounds.width ? 100 - oBounds.width : iLeft);
		}
		else oEvent = { x: (self.offsetLeft), y: (self.offsetTop), width: oBounds.width, height: oBounds.height, mouseX: event.clientX, mouseY: event.clientY };
	}

	self.clearEvent = function()
	{
		oEvent = null;
	}

	self.isVisible = function()
	{
		return (Utility.isIE() ? self.parentNode.parentNode != null : self.parentNode != null);
	}

	return self.init();
}

//TODO: remove this and move to the event it self to initialize.
function createEvent(fEvent, fClear)
{
	return function()
	{
		document.body.onmouseup   = function(oEvent){ document.body.onmouseup = null; document.body.onmousemove = null; fClear()}
		document.body.onmousemove = function(oEvent){ fEvent((oEvent?oEvent:event)); }
	}
}